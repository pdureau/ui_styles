<?php

declare(strict_types = 1);

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
    $layout_builder = $element['layout_builder'];
    /** @var \Drupal\layout_builder\SectionStorageInterface $section_storage */
    $section_storage = $element['#section_storage'];
    $delta = 0;
    $max = \count($section_storage->getSections());
    foreach (Element::children($layout_builder) as $index) {
      if (isset($layout_builder[$index]['layout-builder__section']) && $delta < $max) {
        $section = $section_storage->getSection($delta);
        /** @var array $selected */
        $selected = $section->getThirdPartySetting('ui_styles', 'selected') ?: [];
        /** @var string $extra */
        $extra = $section->getThirdPartySetting('ui_styles', 'extra') ?: '';
        $element['layout_builder'][$index]['layout-builder__section'] =
          $styles_manager->addClasses($element['layout_builder'][$index]['layout-builder__section'], $selected, $extra);
        ++$delta;
      }
    }
    return $element;
  }

}
