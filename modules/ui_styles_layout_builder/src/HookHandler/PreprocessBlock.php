<?php

declare(strict_types=1);

namespace Drupal\ui_styles_layout_builder\HookHandler;

use Drupal\Core\Template\AttributeHelper;

/**
 * Handle title attributes.
 */
class PreprocessBlock {

  /**
   * Handle title attributes.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    if (!empty($variables['configuration']['label_display'])) {
      $ui_style_title_attributes = $variables['configuration']['ui_style_title_attributes'] ?? [];
      $variables['title_attributes'] = AttributeHelper::mergeCollections(
        $variables['title_attributes'],
        $ui_style_title_attributes
      );
    }
  }

}
