<?php

namespace Drupal\ui_styles_layout_builder;

use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Render\Element;

/**
 * Limit what can be called in render arrays to reduce the risk of RCE.
 *
 * See also: https://www.drupal.org/node/2966725.
 */
class LayoutBuilderTrustedCallbacks implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRender'];
  }

  /**
   * Pre-render callback: Sets color preset logo.
   */
  public static function preRender($element) {
    $styles_manager = \Drupal::service('plugin.manager.ui_styles');
    $layout_builder = $element['layout_builder'];
    /** @var \Drupal\layout_builder\SectionStorageInterface $section_storage */
    $section_storage = $element['#section_storage'];
    $delta = 0;
    $max = count($section_storage->getSections());
    foreach (Element::children($layout_builder) as $index) {
      if (isset($layout_builder[$index]['layout-builder__section']) && $delta < $max) {
        $section = $section_storage->getSection($delta);
        $selected = $section->getThirdPartySetting('ui_styles', 'selected') ?: [];
        $extra = $section->getThirdPartySetting('ui_styles', 'extra') ?: '';
        $element['layout_builder'][$index]['layout-builder__section'] =
          $styles_manager->addClasses($element['layout_builder'][$index]['layout-builder__section'], $selected, $extra);
        $delta++;
      }
    }
    return $element;
  }

}
