<?php

declare(strict_types = 1);

namespace Drupal\ui_styles\Render;

use Drupal\Core\Render\Element as CoreElement;
use Drupal\Core\Render\Element\RenderCallbackInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Template\AttributeHelper;

/**
 * Provides helper methods for Drupal render elements.
 */
class Element extends CoreElement {

  /**
   * The string searched for callback.
   */
  public const CALLBACK_NEEDLE = '::';

  /**
   * The length of the callback separator needle.
   */
  public const CALLBACK_NEEDLE_LENGTH = 2;

  /**
   * List of #type to consider without attributes.
   *
   * @var array
   */
  public static $typeWithoutAttributes = [
    'inline_template',
    'processed_text',
    'link',
  ];

  /**
   * List of #type to consider with attributes.
   *
   * @var array
   */
  public static $typeWithAttributes = [
    'view',
    'pattern',
    'html_tag',
  ];

  /**
   * List of #theme to consider with attributes.
   *
   * @var array
   */
  public static $themeWithAttributes = [
    'layout',
    'block',
  ];

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
  public static function addClasses(array $element, array $classes, $attr_property = '#attributes'): array {
    $element[$attr_property] = $element[$attr_property] ?? [];
    $element[$attr_property] = AttributeHelper::mergeCollections(
      $element[$attr_property],
      ['class' => $classes]
    );
    return $element;
  }

  /**
   * Wrap in a div container if not accepting attributes.
   *
   * @param array $element
   *   A render array.
   */
  public static function wrapElementIfNotAcceptingAttributes(array &$element): void {
    if (!Element::isAcceptingAttributes($element)) {
      $element = Element::wrapElement($element);
    }
  }

  /**
   * Wrap in a div container to be able to receive classes.
   *
   * @param array $element
   *   A render array.
   *
   * @return array
   *   A render array.
   */
  public static function wrapElement(array $element): array {
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      'element' => $element,
    ];
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
    if (\array_key_exists('#attributes', $element) || \array_key_exists('#item_attributes', $element)) {
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
    if (isset($element['#type'])) {
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
  protected static function isThemeHookAcceptingAttributes(array $element) {
    $theme = self::getTheme($element);
    $registry = \Drupal::service('theme.registry')->get();
    if (\array_key_exists($theme, $registry)) {
      $theme_hook = $registry[$theme];
      if (!\array_key_exists('variables', $theme_hook) && \array_key_exists('base hook', $theme_hook)) {
        $theme_hook = $registry[$theme_hook['base hook']];
      }
      // Some templates are special. They have no theme variables, but they
      // accept attributes anyway.
      if (\array_key_exists('template', $theme_hook) && \in_array($theme_hook['template'], self::$themeWithAttributes, TRUE)) {
        return TRUE;
      }
      if (\array_key_exists('variables', $theme_hook)) {
        return \array_key_exists('attributes', $theme_hook['variables'])
          || \array_key_exists('item_attributes', $theme_hook['variables']);
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
  protected static function isRenderElementAcceptingAttributes(array $element) {
    // For performance reasons, check first with lists of known render
    // elements.
    if (\in_array($element['#type'], self::$typeWithoutAttributes, TRUE)) {
      return FALSE;
    }
    if (\in_array($element['#type'], self::$typeWithAttributes, TRUE)) {
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
    if (\is_array($element) && isset($element['#theme'])) {
      return self::isThemeHookAcceptingAttributes($element);
    }

    return FALSE;
  }

  /**
   * Get theme of render array.
   *
   * @param array $element
   *   A render array.
   *
   * @return string
   *   The theme's machine name.
   */
  protected static function getTheme(array $element): string {
    if (!isset($element['#theme'])) {
      return '';
    }

    if (!\is_array($element['#theme'])) {
      return $element['#theme'];
    }

    // Some #theme values are an array of suggestions.
    // Most of the time, the last item is the original theme hook.
    $theme = \end($element['#theme']);
    // Anyway, lets be sure it is not a suggestion.
    return \explode('__', $theme)[0];
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
    if (\is_string($callback)) {
      $double_colon = \strpos($callback, self::CALLBACK_NEEDLE);
      if ($double_colon === FALSE) {
        // We don't deal with this situation.
        // @todo Do we need to deal with it? Check Drupal\Core\Render\Renderer.
      }
      elseif ($double_colon > 0) {
        $callback = \explode(self::CALLBACK_NEEDLE, $callback, self::CALLBACK_NEEDLE_LENGTH);
      }
    }

    $message = \sprintf('Render %s callbacks must be methods of a class that implements \Drupal\Core\Security\TrustedCallbackInterface or be an anonymous function. The callback was %s. Support for this callback implementation is deprecated in 8.8.0 and will be removed in Drupal 9.0.0. See https://www.drupal.org/node/2966725', $callback_type, '%s');

    // Add \Drupal\Core\Render\Element\RenderCallbackInterface as an extra
    // trusted interface so that:
    // - All public methods on Render elements are considered trusted.
    // - Helper classes that contain only callback methods can implement this
    //   instead of TrustedCallbackInterface.
    $callbackWrapper = new TrustedCallbackWrapper();
    // @phpstan-ignore-next-line
    return $callbackWrapper->doTrustedCallback($callback, $args, $message, TrustedCallbackInterface::TRIGGER_SILENCED_DEPRECATION, RenderCallbackInterface::class);
  }

}
