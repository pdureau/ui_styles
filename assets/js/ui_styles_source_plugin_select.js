((Drupal, once) => {
  Drupal.ui_styles_source_plugin_select =
    Drupal.ui_styles_source_plugin_select || {};

  function closeOnChange(element) {
    element.addEventListener('change', (event) => {
      // Remove the focus so CSS will hide the options.
      event.target.blur();
    });
  }

  Drupal.behaviors.ui_styles_source_plugin_select = {
    attach(context) {
      const radioElements = once(
        'ui-styles-source-plugin-select',
        '.js-ui-styles-source-select-plugin input',
        context,
      );
      radioElements.forEach(closeOnChange);
    },
  };
})(Drupal, once);
