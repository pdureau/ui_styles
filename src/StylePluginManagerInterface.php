<?php

declare(strict_types = 1);

namespace Drupal\ui_styles;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines an interface for style_plugin managers.
 */
interface StylePluginManagerInterface extends PluginManagerInterface {

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\ui_styles\Definition\StyleDefinition|null
   *   The plugin definition. NULL if not found.
   *
   * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
   */
  public function getDefinition($plugin_id, $exception_on_invalid = TRUE);

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\ui_styles\Definition\StyleDefinition[]
   *   The plugins definitions.
   */
  public function getDefinitions();

  /**
   * Get the sorted list of styles.
   *
   * @return \Drupal\ui_styles\Definition\StyleDefinition[]
   *   The sorted list of styles.
   */
  public function getSortedDefinitions(): array;

  /**
   * Add style selection form elements to an existing form.
   *
   * @param array $form
   *   The form array to add to.
   * @param array $selected
   *   The selected class(es).
   * @param string $extra
   *   The optional free extra class(es).
   *
   * @return array
   *   The modified form element.
   */
  public function alterForm(array $form, array $selected = [], string $extra = ''): array;

  /**
   * Add classes to target element.
   *
   * @param array $target_element
   *   The render element to add to.
   * @param array $selected
   *   The selected class(es), as an array.
   * @param string $extra
   *   The free extra class(es), as a string.
   *
   * @return array
   *   The modified render element.
   */
  public function addClasses(array $target_element, array $selected = [], string $extra = ''): array;

}
