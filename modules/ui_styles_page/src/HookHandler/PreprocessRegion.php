<?php

declare(strict_types=1);

namespace Drupal\ui_styles_page\HookHandler;

use Drupal\Core\Template\AttributeHelper;
use Drupal\ui_styles_page\UiStylesPageInterface;

/**
 * Add classes to region.
 */
class PreprocessRegion {

  /**
   * Inject classes.
   */
  public function preprocess(array &$variables): void {
    $settings = \theme_get_setting(UiStylesPageInterface::REGION_STYLES_KEY_THEME_SETTINGS);
    if (!\is_array($settings)) {
      return;
    }

    $region = $variables['region'];
    $selected = $settings[$region]['selected'] ?? [];
    $extra = $settings[$region]['extra'] ?? '';

    $extra = \explode(' ', $extra);
    $classes = \array_merge($selected, $extra);
    $classes = \array_unique(\array_filter($classes));

    if (empty($classes)) {
      return;
    }

    $variables['attributes'] = $variables['attributes'] ?? [];
    $variables['attributes'] = AttributeHelper::mergeCollections(
      $variables['attributes'],
      [
        'class' => $classes,
      ]
    );
  }

}
