<?php

namespace Drupal\ui_styles;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines an interface for style_plugin managers.
 */
interface StylePluginManagerInterface extends PluginManagerInterface {

  /**
   * Add style selection form elements to an existing form.
   *
   * @param array $form
   *   The form array to add to.
   * @param mixed $selected
   *   The selected class(es).
   * @param string $extra
   *   The optional free extra class(es).
   *
   * @return array
   *   The modified form element.
   */
  public function alterForm(array $form, $selected = [], $extra = '');

  /**
   * Add classes to target element.
   *
   * @param array $target_element
   *   The render element to add to.
   * @param mixed $selected
   *   The selected class(es), as an array.
   * @param string $extra
   *   The free extra class(es), as a string.
   *
   * @return array
   *   The modified render element.
   */
  public function addClasses(array $target_element, $selected = [], $extra = '');

}
