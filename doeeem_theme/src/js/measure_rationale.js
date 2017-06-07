/**
 * Rationale accordion.
 */
(function($, Drupal, window, document, undefined) {

  Drupal.behaviors.doeeem_theme_rationale = {
    attach: function(context, settings) {
      $('.page-node.node-type-measure')
        .find('.node.node-measure>.content>.field-name-body')
        .accordion({
          header: "h2",
          collapsible: true,
          active: false
        });
    }
  };

})(jQuery, Drupal, this, this.document);
