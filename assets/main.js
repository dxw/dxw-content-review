// Modal window for blog subscription

jQuery(function ($) {
  'use strict';

  var $widget = $('.widget_dclg_subscribe_widget'),
      $modal_content = $widget.find('.modal-subscribe');

  // If the location string is aimed at the
  if( '#blog-subscription' === location.hash ) {
    openModal(false);
  }

  $modal_content.hide();
  $widget.find('.btn-subscribe').show();

  $widget.on('click', '.btn-subscribe', openModal);

  function openModal(e) {
    if( e ) {
      e.preventDefault();
    }

    var $mask = $('<div />', { 'class': 'modal-backdrop fade'}),
        $modal = $modal_content.clone();

    $modal.addClass('modal fade').show();
    $('body').append($mask, $modal);

    setTimeout(function() {
      $mask.addClass('in');
      $modal.addClass('in');
    }, 200);

    $('body').on('click', '.modal-backdrop', function() {
      $modal.removeClass('in');
      $mask.removeClass('in');

      setTimeout(function() {
        $modal.remove();
        $mask.remove();
      }, 400);
    });

  };

});
