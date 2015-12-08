<?php
/**
 * Content Review main class
 *
 * @package   dxw Content Review
 * @author    Adam Onishi <adam@dxw.com>
 * @license   GPL2
 * @copyright   2015 dxw
 */

class Dxw_Content_Review {
  /**
   * An instance of the class
   *
   * @var null
   */
  protected static $instance = null;

  /**
   * The slug of the plugin
   *
   * @var string
   */
  protected static $plugin_slug = 'dxw-content-review';

  private function __construct() {
    // Nothing to see here
  }

  public static function get_instance() {
    if( null === self::$instance ) {
      self::$instance = new self;
    }

    return self::$instance;
  }

  public function initialise() {

    // settings
    $this->settings = array(
      // basic
      'name'               => __('dxw Content Review', 'dxwsubs'),
      'version'            => '0.0.1',
      'slug'               => self::$plugin_slug,

      // urls
      'basename'           => plugin_basename( __FILE__ ),
      'path'               => plugin_dir_path( __FILE__ ),
      'dir'                => plugin_dir_url( __FILE__ ),

      'dxw_review_length'  => array(
        "0"  => "No review date",
        "3"  => "3 months time",
        "6"  => "6 months time",
        "12" => "12 months time",
      ),

      'dxw_review_action'  => array(
        "email" => "Email reviewer",
        "draft" => "Email reviewer and set to draft",
        "trash" => "Email reviewer and send to trash",
      ),
    );

    require_once('lib/helpers.php');

    if( is_admin() ) {
      // Add content review meta box
      add_action( 'add_meta_boxes', array( $this, 'setup_meta_boxes' ) );
      add_action( 'save_post', array( $this, 'save_meta_data' ) );
    }

    // Add content review action
    add_action( 'dxw_content_review_hook', array($this, 'content_review') );
  }

  public static function plugin_activation() {
    wp_schedule_event( time(), 'daily', 'dxw_content_review_hook' );
  }

  public static function plugin_deactivation() {
    wp_clear_scheduled_hook( 'dxw_content_review_hook' );
    wp_clear_scheduled_hook( 'dxw_content_review' );
  }

  public static function content_review() {
    $to_review = get_option( 'dxw_content_review' );

    $now = time();

    if( ! is_array($to_review) ) {
      return;
    }

    foreach( $to_review as $post_id => $review_date ) {
      if( $now > $review_date ) {
        $action = get_post_meta( $post_id, '_dxw_review_action', true );

        // If action isn't just to email user set new status
        if( 'email' !== $action ) {
          self::set_post_status($post_id, $action);
        }

        // Send review alert
        self::alert_reviewers($post_id, $action);

        // Remove post from review schedule to prevent multiple alerts
        unset($to_review[$post_id]);
        update_option( 'dxw_content_review', $to_review );
      }
    }

  }

  public static function set_post_status($post_id, $action) {
    $post = array(
      'ID'          => $post_id,
      'post_status' => $action,
    );

    wp_update_post( $post );
  }

  public static function alert_reviewers($post_id, $action) {
    $reviewers = get_post_meta( $post_id, '_dxw_review_email', true );

    // if there are no subscribers we don't need to send an email
    if( empty($reviewers) ) {
      return;
    }

    $subject = 'Please review post: ' . get_the_title( $post_id ) . ', on DCLG Intranet';

    $email_args = array(
      'post_title'  => get_the_title( $post_id ),
      'action'      => $action,
    );

    ob_start();
    dxw_get_view('email-template', $email_args);
    $message = ob_get_contents();
    ob_end_clean();

    wp_mail( $reviewers, $subject, $message );
  }

  public static function setup_meta_boxes( $post_type ) {

    if( $post_type === 'post' || $post_type === 'page' ) {
      add_meta_box(
        'dxw-content',
        'Content Review',
        array( 'Dxw_Content_Review', 'render_meta_box' ),
        $post_type,
        'side',
        'core'
      );
    }

  }

  public static function render_meta_box( $post ) {
    // Get meta box content
    // Use nonce for verification
    wp_nonce_field( plugin_basename( __FILE__ ), 'dxw_content_review_nonce' );

    $args = array();

    // Get settings data
    $args['dxw_review_email'] = get_post_meta( $post->ID, '_dxw_review_email', true );
    $args['dxw_review_length'] = get_post_meta( $post->ID, '_dxw_review_length', true );
    $args['dxw_review_action'] = get_post_meta( $post->ID, '_dxw_review_action', true );

    // Include fields meta view
    dxw_get_view('content-review-meta', $args);
  }

  public static function save_meta_data( $post_id ) {
    // verify if this is an auto save routine.
    // If it is the post has not been updated, so we don’t want to do anything
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
      return $post_id;
    }

    // verify this came from the screen and with proper authorization,
    // because save_post can be triggered at other times
    if ( !isset( $_POST['dxw_content_review_nonce'] ) || !wp_verify_nonce( $_POST['dxw_content_review_nonce'], plugin_basename( __FILE__ ) ) ) {
      return $post_id;
    }

    // Get the post type object.
    global $post;
    $post_type = get_post_type_object( $post->post_type );

    // Check if the current user has permission to edit the post.
    if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
      return $post_id;
    }

    if ( wp_is_post_revision( $post_id ) ) {
      return $post_id;
    }

    $data = array();

    $data['_dxw_review_email'] = self::get_emails( $_POST['dxw_review_email'] );
    $data['_dxw_review_length'] = self::get_value_from_setting( $_POST['dxw_review_length'], 'dxw_review_length' );
    $data['_dxw_review_action'] = self::get_value_from_setting( $_POST['dxw_review_action'], 'dxw_review_action' );

    foreach( $data as $key => $value ) {
      if( ! is_wp_error( $value ) ) {
        $current = get_post_meta( $post_id, $key, true );

        // add/update record (both are taken care of by update_post_meta)
        if ( $value && '' == $current ) {
          add_post_meta( $post_id, $key, $value, true );
        } elseif ( $value && $value != $current ) {
          update_post_meta( $post_id, $key, $value );
        } elseif ( '' == $value && $current ) {
          delete_post_meta( $post_id, $key, $current );
        }
      }
    }

    if( ! is_wp_error( $data['_dxw_review_length'] ) ) {
      self::save_review_date( $post_id, $data['_dxw_review_length'] );
    }
  }

  public static function get_emails( $email ) {

    if( strpos($email, ',') ) {
      $emails = explode(',', $email);

      foreach($emails as $address) {
        if( ! is_email( trim($address) ) ) {
          $error = new WP_Error('content-review', 'Invalid email entered');
        }
      }
    } else {
      if( ! is_email( trim($email) ) ) {
        $error = new WP_Error('content-review', 'Invalid email entered');
      }
    }

    if( isset($error) ) {
      return $error;
    } else {
      return $email;
    }

  }

  public static function get_value_from_setting($value, $setting_name) {
    $settings = dxw_get_setting($setting_name);

    if( array_key_exists($value, $settings) ) {
      return $value;
    } else {
      return new WP_Error('content-review', 'Invalid setting chosen');
    }
  }

  public static function save_review_date( $post_id, $length ) {
    if( $length === '0' ) {
      return;
    }

    $reviews = get_option('dxw_content_review');

    if( ! is_array($reviews) ) {
      $reviews = array();
    }

    $review_period = '+' . $length . ' month';

    $published = get_the_time( 'U', $post_id );
    $review_date = strtotime($review_period, $published);

    $reviews[$post_id] = $review_date;

    update_option( 'dxw_content_review', $reviews );
  }
}
