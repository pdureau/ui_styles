<?php

namespace Drupal\ui_styles\Render;

use Drupal\Core\Render\Element as CoreElement;
use Drupal\Core\Template\AttributeHelper;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Render\Element\RenderCallbackInterface;

/**
 * Provides helper methods for Drupal render elements.
 */
class Element extends CoreElement {

  /**
   * Add HTML classes to render array.
   *
   * @param array $element
   *   A render array.
   * @param array $classes
   *   An array of HTML classes.
   * @param string $attr_property
   *   Attributes property when different from #attributes.
   *
   * @return array
   *   A render array.
   */
  public static function addClasses(array $element, array $classes, $attr_property = '#attributes') {
    $element[$attr_property] = isset($element[$attr_property]) ? $element[$attr_property] : [];
    $element[$attr_property] = AttributeHelper::mergeCollections(
      $element[$attr_property],
      ['class' => $classes]
    );
    return $element;
  }

  /**
   * Check if render array accept #attributes property.
   *
   * @param array $element
   *   A render array.
   *
   * @return bool
   *   Attributes acceptance.
   */
  public static function isAcceptingAttributes(array $element) {

    // If already existing, we just go for it.
    if (array_key_exists('#attributes', $element) || array_key_exists('#item_attributes', $element)) {
      return TRUE;
    }

    // Theme hooks.
    // See also: https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!theme.api.php/group/themeable/.
    if (isset($element['#theme'])) {
      return self::isThemeHookAcceptingAttributes($element);
    }

    // Render element plugins.
    // @see \Drupal\Core\Render\Element\ElementInterface.
    // See also: https://api.drupal.org/api/drupal/elements.
    elseif (isset($element['#type'])) {
      return self::isRenderElementAcceptingAttributes($element);
    }

    // Other render arrays (#markup, #plain_text...)
    return FALSE;
  }

  /**
   * Check if theme hook render array accept #attributes property.
   *
   * @param array $element
   *   A theme hook render array.
   *
   * @return bool
   *   Attributes acceptance.
   */
  private static function isThemeHookAcceptingAttributes(array $element) {
    $registry = \Drupal::service('theme.registry')->get();
    if (array_key_exists($element['#theme'], $registry)) {
      $theme_hook = $registry[$element['#theme']];
      if (!array_key_exists('variables', $theme_hook) && array_key_exists('base hook', $theme_hook)) {
        $theme_hook = $registry[$theme_hook['base hook']];
      }
      // Some templates are specials. They have no theme variables, but they 
      // accept attributes anyway.
      $with_attributes = [
        'layout',
        'block',
      ];
      if (array_key_exists('template', $theme_hook) && in_array($theme_hook['template'], $with_attributes)) {
        return TRUE;
      }
      if (array_key_exists('variables', $theme_hook)) {
        return array_key_exists('attributes', $theme_hook['variables'])
          || array_key_exists('item_attributes', $theme_hook['variables']);
      }
    }
    return FALSE;
  }

  /**
   * Check if render element accept #attributes property.
   *
   * @param array $element
   *   A render element.
   *
   * @return bool
   *   Attributes acceptance.
   */
  private static function isRenderElementAcceptingAttributes(array $element) {
    // For performance reasons, check first with lists of known render
    // elements.
    $without_attributes = [
      'inline_template',
      'processed_text',
      'link',
    ];
    if (in_array($element['#type'], $without_attributes)) {
      return FALSE;
    }
    $with_attributes = [
      'view',
      'pattern',
      'html_tag',
    ];
    if (in_array($element['#type'], $with_attributes)) {
      return TRUE;
    }

    // If not in lists, do a resource hungry check, processing the render
    // element.
    $info = \Drupal::service('plugin.manager.element_info')->getInfo($element['#type']);
    if (isset($info['#pre_render'])) {
      foreach ($info['#pre_render'] as $callable) {
        $element = self::doCallback('#pre_render', $callable, [$element]);
      }
    }
    // Check again as theme hooks instead of render elements plugins.
    if (isset($element['#theme'])) {
      return self::isThemeHookAcceptingAttributes($element);
    }

    return FALSE;
  }

  /**
   * Performs a callback.
   *
   * @param string $callback_type
   *   The type of the callback. For example, '#post_render'.
   * @param string|callable $callback
   *   The callback to perform.
   * @param array $args
   *   The arguments to pass to the callback.
   *
   * @return mixed
   *   The callback's return value.
   *
   * @see \Drupal\Core\Security\TrustedCallbackInterface
   */
  protected static function doCallback($callback_type, $callback, array $args) {
    if (is_string($callback)) {
      $double_colon = strpos($callback, '::');
      if ($double_colon === FALSE) {
        // We don't deal with this situation.
        // TODO: Do we need to deal with it? Check Drupal\Core\Render\Renderer.
      }
      elseif ($double_colon > 0) {
        $callback = explode('::', $callback, 2);
      }
    }

    $message = sprintf('Render %s callbacks must be methods of a class that implements \Drupal\Core\Security\TrustedCallbackInterface or be an anonymous function. The callback was %s. Support for this callback implementation is deprecated in 8.8.0 and will be removed in Drupal 9.0.0. See https://www.drupal.org/node/2966725', $callback_type, '%s');

    // Add \Drupal\Core\Render\Element\RenderCallbackInterface as an extra
    // trusted interface so that:
    // - All public methods on Render elements are considered trusted.
    // - Helper classes that contain only callback methods can implement this
    //   instead of TrustedCallbackInterface.
    $callbackWrapper = new TrustedCallbackWrapper();
    return $callbackWrapper->doTrustedCallback($callback, $args, $message, TrustedCallbackInterface::TRIGGER_SILENCED_DEPRECATION, RenderCallbackInterface::class);
  }

}
