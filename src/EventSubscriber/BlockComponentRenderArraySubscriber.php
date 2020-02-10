<?php

namespace Drupal\layout_builder_classes\EventSubscriber;

use Drupal\layout_builder\Event\SectionComponentBuildRenderArrayEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\layout_builder\LayoutBuilderEvents;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Render\Element;

/**
 * Class BlockComponentRenderArraySubscriber.
 */
class BlockComponentRenderArraySubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Layout Builder also subscribes to this event to build the initial render
    // array. We use a higher weight so that we execute after it.
    $events[LayoutBuilderEvents::SECTION_COMPONENT_BUILD_RENDER_ARRAY] = ['onBuildRender', 50];
    return $events;
  }

  /**
   * Add each component's block styles to the render array.
   *
   * @param \Drupal\layout_builder\Event\SectionComponentBuildRenderArrayEvent $event
   *   The section component render event.
   */
  public function onBuildRender(SectionComponentBuildRenderArrayEvent $event) {
    $build = $event->getBuild();
    // This shouldn't happen - Layout Builder should have already created the
    // initial build data.
    if (empty($build)) {
      return;
    }

    $selected = $event->getComponent()->get('layout_builder_classes');
    if ($selected) {
      // Convert single selection to an array for consistent processing.
      if (!is_array($selected)) {
        $selected = [$selected];
      }
      $selected = array_values($selected);
      $build['#attributes'] = isset($build['#attributes']) ? $build['#attributes'] : [];
      $build['#attributes']['class'] = isset($build['#attributes']['class']) ? $build['#attributes']['class'] : [];
      $build = $this->addStyleToBlockContent($build, $selected);
      // TODO: $build['#cache']['tags']?
      $event->setBuild($build);
    }

  }

  /**
   * Add style to block content instead of block wrapper.
   */
  private function addStyleToBlockContent($build, $styles) {
    if (isset($build['content']['#type']) &&
      in_array($build['content']['#type'], ['pattern', 'html_tag', 'view'])) {
      if (!isset($build['content']['#attributes']['class'])) {
        $build['content']['#attributes'] = [
          'class' => [],
        ];
      }
      $classes = $build['content']['#attributes']['class'] ?: [];
      $build['content']['#attributes']['class'] = array_merge($classes, $styles);
      $build['#attributes']['class'] = array_diff($build['#attributes']['class'], $styles);
    }
    elseif (isset($build['content']['#theme']) &&
      $build['content']['#theme'] === 'field') {
      foreach (Element::children($build['content']) as $delta) {
        if ($build['content']['#formatter'] === 'media_thumbnail') {
          $build = $this->addStyleToFieldFormatterItem($build, $delta, $styles, '#item_attributes');
        }
        else {
          // For everything else but #type=processed_text.
          $build = $this->addStyleToFieldFormatterItem($build, $delta, $styles, '#attributes');
        }
      }
      $build['#attributes']['class'] = array_diff($build['#attributes']['class'], $styles);
    }
    return $build;
  }

  /**
   * Add style to field formatter item.
   */
  private function addStyleToFieldFormatterItem($build, $delta, $styles, $attr_property) {
    if (!isset($build['content'][$delta][$attr_property])) {
      $build['content'][$delta][$attr_property] = [];
    }
    if (is_array($build['content'][$delta][$attr_property])) {
      if (!isset($build['content'][$delta][$attr_property]['class'])) {
        $build['content'][$delta][$attr_property] = [
          'class' => [],
        ];
      }
      $classes = $build['content'][$delta][$attr_property]['class'] ?: [];
      $build['content'][$delta][$attr_property]['class'] = array_merge($classes, $styles);
    }
    elseif ($build['content'][$delta][$attr_property] instanceof Attribute) {
      $build['content'][$delta][$attr_property]->addClass($styles);
    }
    return $build;
  }

}
