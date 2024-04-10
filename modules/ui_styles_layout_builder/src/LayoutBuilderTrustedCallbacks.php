<?php

declare(strict_types=1);

namespace Drupal\ui_styles_layout_builder;

use Drupal\Core\Render\Element;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Limit what can be called in render arrays to reduce the risk of RCE.
 *
 * See also: https://www.drupal.org/node/2966725.
 */
class LayoutBuilderTrustedCallbacks implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks(): array {
    return ['preRender'];
  }

  /**
   * Pre-render callback: Sets color preset logo.
   */
  public static function preRender(array $element): array {
    $styles_manager = \Drupal::service('plugin.manager.ui_styles');
    /** @var \Drupal\layout_builder\SectionStorageInterface $section_storage */
    $section_storage = $element['#section_storage'];
    $delta = 0;
    $max = \count($section_storage->getSections());
    foreach (Element::children($element['layout_builder']) as $index) {
      if ($delta >= $max) {
        break;
      }

      // Dealing with "add section link" sections.
      if (!isset($element['layout_builder'][$index]['layout-builder__section'])) {
        continue;
      }
      $layout = &$element['layout_builder'][$index]['layout-builder__section'];

      // Section styles.
      $section = $section_storage->getSection($delta);
      /** @var array $selected */
      $selected = $section->getThirdPartySetting('ui_styles', 'selected') ?: [];
      /** @var string $extra */
      $extra = $section->getThirdPartySetting('ui_styles', 'extra') ?: '';
      $layout = $styles_manager->addClasses($layout, $selected, $extra);

      // Regions styles.
      /** @var array $regions_configuration */
      $regions_configuration = $section->getThirdPartySetting('ui_styles', 'regions', []);
      foreach ($regions_configuration as $region_name => $region_styles) {
        // Skip if the region is not available anymore due to changed layout.
        if (!isset($layout[$region_name])) {
          continue;
        }
        /** @var array $selected */
        $selected = $region_styles['selected'] ?? [];
        /** @var string $extra */
        $extra = $region_styles['extra'] ?? '';
        $layout[$region_name] = $styles_manager->addClasses($layout[$region_name], $selected, $extra);
      }

      ++$delta;
    }
    return $element;
  }

}
