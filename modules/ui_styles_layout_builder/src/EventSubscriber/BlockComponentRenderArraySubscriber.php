<?php

declare(strict_types=1);

namespace Drupal\ui_styles_layout_builder\EventSubscriber;

use Drupal\Core\Template\AttributeHelper;
use Drupal\layout_builder\Event\SectionComponentBuildRenderArrayEvent;
use Drupal\layout_builder\LayoutBuilderEvents;
use Drupal\ui_styles\StylePluginManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Add each component's block styles to the render array.
 */
class BlockComponentRenderArraySubscriber implements EventSubscriberInterface {

  public function __construct(
    protected StylePluginManagerInterface $styleManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = [];
    // Layout Builder also subscribes to this event to build the initial render
    // array. We use a higher weight so that we execute after it.
    $events[LayoutBuilderEvents::SECTION_COMPONENT_BUILD_RENDER_ARRAY] = [
      'onBuildRender',
      (int) 50,
    ];
    return $events;
  }

  /**
   * Add each component's block styles to the render array.
   *
   * @param \Drupal\layout_builder\Event\SectionComponentBuildRenderArrayEvent $event
   *   The section component render event.
   */
  public function onBuildRender(SectionComponentBuildRenderArrayEvent $event): void {
    $build = $event->getBuild();
    // This shouldn't happen - Layout Builder should have already created the
    // initial build data.
    if (empty($build)) {
      return;
    }
    $component = $event->getComponent();

    // Block wrapper.
    $dummy = [
      '#type' => 'html_tag',
      '#tag' => 'span',
    ];
    /** @var array $selected */
    $selected = $component->get('ui_styles_wrapper') ?: [];
    /** @var string $extra */
    $extra = $component->get('ui_styles_wrapper_extra') ?: '';

    $dummy = $this->styleManager->addClasses($dummy, $selected, $extra);
    /** @var \Drupal\Core\Template\Attribute|array $dummy_attributes */
    $dummy_attributes = $dummy['#attributes'] ?? [];
    /** @var \Drupal\Core\Template\Attribute|array $block_attributes */
    $block_attributes = $build['#attributes'] ?? [];
    $build['#attributes'] = AttributeHelper::mergeCollections(
      $block_attributes,
      $dummy_attributes
    );

    // Block title.
    $dummy = [
      '#type' => 'html_tag',
      '#tag' => 'span',
    ];
    /** @var array $selected */
    $selected = $component->get('ui_styles_title') ?: [];
    /** @var string $extra */
    $extra = $component->get('ui_styles_title_extra') ?: '';

    $dummy = $this->styleManager->addClasses($dummy, $selected, $extra);
    $build['#configuration']['ui_style_title_attributes'] = $dummy['#attributes'] ?? [];

    // Block content.
    /** @var array $selected */
    $selected = $component->get('ui_styles') ?: [];
    /** @var string $extra */
    $extra = $component->get('ui_styles_extra') ?: '';
    $build = $this->styleManager->addClasses($build, $selected, $extra);

    $event->setBuild($build);
  }

}
