<?php

declare(strict_types=1);

namespace Drupal\ui_styles\Render;

use Drupal\Core\Render\Element\RenderCallbackInterface;
use Drupal\Core\Render\Element as CoreElement;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Template\AttributeHelper;

/**
 * Provides helper methods for Drupal render elements.
 */
class Element extends CoreElement {

  /**
   * List of #type to consider without attributes.
   *
   * @var array
   */
  public static $typeWithoutAttributes = [
    'inline_template',
    'link',
    'processed_text',
  ];

  /**
   * List of #type to consider with attributes.
   *
   * @var array
   */
  public static $typeWithAttributes = [
    'component',
    'html_tag',
    'pattern',
    'view',
  ];

  /**
   * List of #theme to consider with attributes.
   *
   * @var array
   */
  public static $themeWithAttributes = [
    'block',
    'layout',
  ];

  /**
   * Wrappers that hold content for styling.
   *
   * Meaningless wrappers must not receive styling until the very last moment
   * when we can't find an element to style.
   *
   * @var array
   */
  public static $meaninglessThemeWrappers = [
    'block',
    'layout',
    'view',
  ];

  /**
   * An element that only has those properties set is considered empty.
   *
   * @var string[]
   */
  public static $emptyProperties = [
    '#access',
    '#access_callback',
    '#attached',
    '#cache',
    '#weight',
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
  public static function addClasses(array $element, array $classes, string $attr_property = '#attributes'): array {
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
   * Traverse the render tree and find all first elements accepting attributes.
   *
   * @param array $element
   *   The render element. Must be reference to update in place later.
   *
   * @return array
   *   Plain array of nested elements references to directly manipulate.
   */
  public static function findFirstAcceptingAttributes(array &$element): array {
    $candidates = [];
    // For field items we likely reached the real content, no need to go deeper.
    // We return even non-accepting elements because it's the content.
    if (\in_array($element['#theme'] ?? '', ['field'], TRUE)) {
      foreach (static::children($element) as $key) {
        $candidates[] = &$element[$key];
      }
      return $candidates;
    }
    // If an element is accepting attributes and not just a wrapper,
    // it's the desired content to style.
    if (!static::isMeaninglessWrapper($element) && Element::isAcceptingAttributes($element)) {
      $candidates[] = &$element;
      return $candidates;
    }
    // Go deeper in the tree.
    $siblings_marked_keys = [];
    foreach (static::children($element) as $key) {
      $new_candidates = static::findFirstAcceptingAttributes($element[$key]);
      $candidates = \array_merge($candidates, $new_candidates);
      if (!empty($new_candidates)) {
        $siblings_marked_keys[] = $key;
      }
    }
    // If some siblings were marked for styles and unmarked elements are leafs,
    // wrap them as they possibly represent some content as other siblings.
    if (!empty($siblings_marked_keys)) {
      $unmarked = \array_diff(static::children($element), $siblings_marked_keys);
      foreach ($unmarked as $key) {
        if (static::hasAllEmptyChildren($element[$key])) {
          // Exclude empty elements.
          if (static::isEmpty($element[$key])) {
            continue;
          }

          $candidates[] = &$element[$key];
        }
      }
    }
    return $candidates;
  }

  /**
   * Checks whether the element is only a wrapper.
   */
  public static function isMeaninglessWrapper(array $element): bool {
    // Check it is a meaningless wrapper.
    if (!\in_array($element['#theme'] ?? '', static::$meaninglessThemeWrappers, TRUE)) {
      return FALSE;
    }
    // Check that wrapper has some content otherwise it's content itself.
    return !static::hasAllEmptyChildren($element);
  }

  /**
   * Checks whether the element doesn't have children or they are empty.
   */
  public static function hasAllEmptyChildren(array $element): bool {
    // Check that wrapper has some content otherwise it's content itself.
    $children = static::children($element);
    if (empty($children)) {
      return TRUE;
    }
    foreach ($children as $key) {
      if (!empty($element[$key])) {
        return FALSE;
      }
    }
    return TRUE;
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
   * {@inheritdoc}
   */
  public static function isEmpty(array $elements): bool {
    return \array_diff(\array_keys($elements), static::$emptyProperties) === [];
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
   * Cannot use the renderer service as the doCallback method is protected.
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
   * @see \Drupal\Core\Render\Renderer::doCallback()
   */
  protected static function doCallback($callback_type, $callback, array $args) {
    $callable = \Drupal::service('callable_resolver')->getCallableFromDefinition($callback);
    $message = \sprintf('Render %s callbacks must be methods of a class that implements \Drupal\Core\Security\TrustedCallbackInterface or be an anonymous function. The callback was %s. See https://www.drupal.org/node/2966725', $callback_type, '%s');
    // Add \Drupal\Core\Render\Element\RenderCallbackInterface as an extra
    // trusted interface so that:
    // - All public methods on Render elements are considered trusted.
    // - Helper classes that contain only callback methods can implement this
    //   instead of TrustedCallbackInterface.
    $callbackWrapper = new TrustedCallbackWrapper();
    return $callbackWrapper->doTrustedCallback($callable, $args, $message, TrustedCallbackInterface::THROW_EXCEPTION, RenderCallbackInterface::class);
  }

}
