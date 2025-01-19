<?php

declare(strict_types=1);

namespace Drupal\ui_styles;

use Drupal\Component\Plugin\CategorizingPluginManagerInterface;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines an interface for style plugin managers.
 */
interface StylePluginManagerInterface extends PluginManagerInterface, CategorizingPluginManagerInterface {

  /**
   * List of theme hook using item_attributes instead of attributes.
   */
  public const THEME_WITH_ITEM_ATTRIBUTES = [
    'image_formatter',
    'responsive_image_formatter',
  ];

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
   * {@inheritdoc}
   *
   * @param \Drupal\ui_styles\Definition\StyleDefinition[]|null $definitions
   *   (optional) The plugin definitions to sort. If omitted, all plugin
   *   definitions are used.
   *
   * @return \Drupal\ui_styles\Definition\StyleDefinition[]
   *   The sorted definitions.
   *
   * @phpstan-ignore-next-line
   */
  public function getSortedDefinitions(?array $definitions = NULL): array;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\ui_styles\Definition\StyleDefinition[]|null $definitions
   *   (optional) The plugin definitions to group. If omitted, all plugin
   *   definitions are used.
   *
   * @return \Drupal\ui_styles\Definition\StyleDefinition[][]
   *   The sorted definitions grouped by category.
   */
  public function getGroupedDefinitions(?array $definitions = NULL): array;

  /**
   * Add style selection form elements to an existing form.
   *
   * @param array $form
   *   The form array to add to.
   * @param array $selected
   *   The selected class(es).
   * @param string $extra
   *   The optional free extra class(es).
   * @param string $theme
   *   The optional theme.
   *
   * @return array
   *   The modified form element.
   *
   * @deprecated in ui_styles:8.x-1.14 and is removed from ui_styles:2.0.0. Use
   *    the ui_styles_styles form element instead.
   * @see https://www.drupal.org/node/3500750
   */
  public function alterForm(array $form, array $selected = [], string $extra = '', string $theme = ''): array;

  /**
   * Add classes to target element.
   *
   * @param array $element
   *   The render element to add to.
   * @param array $selected
   *   The selected class(es), as an array.
   * @param string $extra
   *   The free extra class(es), as a string.
   *
   * @return array
   *   The modified render element.
   */
  public function addClasses(array $element, array $selected = [], string $extra = ''): array;

  /**
   * Filter plugins by the modules and the selected theme and its parents.
   *
   * @param string $theme
   *   The theme to filter the plugins against.
   *
   * @return \Drupal\ui_styles\Definition\StyleDefinition[][]
   *   The list of filtered, grouped and sorted definitions.
   */
  public function getDefinitionsForTheme(string $theme): array;

}
