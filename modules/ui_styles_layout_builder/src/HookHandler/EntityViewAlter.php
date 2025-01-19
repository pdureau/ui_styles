<?php

declare(strict_types=1);

namespace Drupal\ui_styles_layout_builder\HookHandler;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Template\AttributeHelper;
use Drupal\layout_builder\Entity\LayoutEntityDisplayInterface;
use Drupal\layout_builder\Section;
use Drupal\ui_styles\SectionStorageTrait;
use Drupal\ui_styles\StylePluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add classes to Layout Builder sections.
 */
class EntityViewAlter implements ContainerInjectionInterface {

  use SectionStorageTrait;

  /**
   * The styles plugin manager.
   *
   * @var \Drupal\ui_styles\StylePluginManagerInterface
   */
  protected StylePluginManagerInterface $stylesManager;

  /**
   * Constructor.
   *
   * @param \Drupal\ui_styles\StylePluginManagerInterface $stylesManager
   *   The styles plugin manager.
   */
  public function __construct(
    StylePluginManagerInterface $stylesManager,
  ) {
    $this->stylesManager = $stylesManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('plugin.manager.ui_styles')
    );
  }

  /**
   * Add classes to Layout Builder sections.
   *
   * Because hook_preprocess_layout() deals only with layouts rendered by
   * \Drupal::service('plugin.manager.core.layout')->getThemeImplementations()
   * (for example, this is not the case for layouts managed from
   * ui_patterns_layout_builder module), we need to move up to the layout
   * builder's sections level:
   * - using hook_entity_view_alter() while rendering an entity
   * - using hook_element_info_alter() while previewing.
   *
   * @param array &$build
   *   A renderable array representing the entity content. The module may add
   *   elements to $build prior to rendering. The structure of $build is a
   *   renderable array as expected by
   *   \Drupal\Core\Render\RendererInterface::render().
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The entity view display holding the display options configured for the
   *   entity components.
   */
  public function alter(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display): void {
    if (!$entity instanceof ContentEntityInterface) {
      return;
    }

    if (!$display instanceof LayoutEntityDisplayInterface) {
      return;
    }

    if (!$display->isLayoutBuilderEnabled()) {
      return;
    }

    $storage = $this->getDisplaySectionStorage($entity, $display, $build['#view_mode']);
    if ($storage == NULL) {
      return;
    }

    $layout_builder = &$build['_layout_builder'];
    foreach ($storage->getSections() as $delta => $section) {
      $this->addStylesToSection($layout_builder, $section, $delta);
    }
  }

  /**
   * Add styles to a Layout Builder section.
   *
   * @param array $layoutBuilder
   *   The layout builder renderable array.
   * @param \Drupal\layout_builder\Section $section
   *   The section being processed.
   * @param int $delta
   *   The section delta.
   */
  protected function addStylesToSection(array &$layoutBuilder, Section $section, int $delta): void {
    /** @var array $selected */
    $selected = $section->getThirdPartySetting('ui_styles', 'selected') ?: [];
    /** @var string $extra */
    $extra = $section->getThirdPartySetting('ui_styles', 'extra') ?: '';
    $layoutBuilder[$delta] = $this->stylesManager->addClasses($layoutBuilder[$delta], $selected, $extra);

    // Regions.
    /** @var array $regions_configuration */
    $regions_configuration = $section->getThirdPartySetting('ui_styles', 'regions', []);
    foreach ($regions_configuration as $region_name => $region_styles) {
      /** @var array $selected */
      $selected = $region_styles['selected'] ?? [];
      /** @var string $extra */
      $extra = $region_styles['extra'] ?? '';
      // Set styles classes.
      $extra = \explode(' ', $extra);
      $styles = \array_merge($selected, $extra);
      $styles = \array_unique(\array_filter($styles));

      // Do not use the service to add the classes to avoid the div wrapper.
      // Otherwise, classes would be added twice.
      $layoutBuilder[$delta][$region_name]['#attributes'] = $layoutBuilder[$delta][$region_name]['#attributes'] ?? [];
      $layoutBuilder[$delta][$region_name]['#attributes'] = AttributeHelper::mergeCollections(
        $layoutBuilder[$delta][$region_name]['#attributes'],
        [
          'class' => $styles,
        ]
      );
    }
  }

}
