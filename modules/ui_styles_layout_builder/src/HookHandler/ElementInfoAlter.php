<?php

declare(strict_types=1);

namespace Drupal\ui_styles_layout_builder\HookHandler;

/**
 * Element Info Alter.
 */
class ElementInfoAlter {

  /**
   * Alter element info.
   *
   * Because hook_preprocess_layout() deals only with layouts rendered by
   * \Drupal::service('plugin.manager.core.layout')->getThemeImplementations()
   * (for example, this is not the case for layouts managed from
   * ui_patterns_layout_builder module), we need to move up to the layout
   * builder's sections level:
   * - using hook_entity_view_alter() while rendering an entity
   * - using hook_element_info_alter() while previewing.
   *
   * @param array $info
   *   An associative array with structure identical to that of the return value
   *   of \Drupal\Core\Render\ElementInfoManagerInterface::getInfo().
   *
   * @see https://www.drupal.org/project/drupal/issues/3080684
   */
  public function alter(array &$info): void {
    $info['layout_builder']['#pre_render'][] = 'Drupal\ui_styles_layout_builder\LayoutBuilderTrustedCallbacks::preRender';
  }

}
