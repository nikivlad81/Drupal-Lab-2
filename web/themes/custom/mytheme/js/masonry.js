(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.mytheme_masonry = {
    attach: function (context, settings) {
      $('.grid', context).masonry({
        // options
        itemSelector: '.grid-item',
        columnWidth: 200
      });

    }
  };

})(jQuery, Drupal, drupalSettings);
