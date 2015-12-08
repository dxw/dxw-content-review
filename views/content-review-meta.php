<p>
<label for="dxw-review-length">Set content review period</label>
<select name="dxw_review_length" id="dxw-review-length" style="width:100%">
  <?php foreach( dxw_get_setting('dxw_review_length') as $value => $label ): ?>
    <option value="<?php echo $value; ?>" <?php selected( $args['dxw_review_length'], $value ); ?>><?php echo $label; ?></option>
  <?php endforeach; ?>
</select>
</p>

<p>
<label for="dxw-review-email">Set reviewer email address(es)</label>
<input type="text" name="dxw_review_email" id="dxw-review-email" value="<?php echo esc_attr( $args['dxw_review_email'] ); ?>" style="width:100%">
<span class="description">Comma (,) separate multiple emails</span>
</p>

<p>
<label for="dxw_review_action">Action on review date</label>
<select name="dxw_review_action" id="dxw-review-action" style="width:100%">
  <?php foreach( dxw_get_setting('dxw_review_action') as $value => $label ): ?>
    <option value="<?php echo $value; ?>" <?php selected( $args['dxw_review_action'], $value ); ?>><?php echo $label; ?></option>
  <?php endforeach; ?>
</select>
</p>
