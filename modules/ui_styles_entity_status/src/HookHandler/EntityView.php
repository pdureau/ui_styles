<?php

declare(strict_types = 1);

namespace Drupal\ui_styles_entity_status\HookHandler;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Template\AttributeHelper;
use Drupal\layout_builder\Entity\LayoutEntityDisplayInterface;
use Drupal\layout_builder\Plugin\SectionStorage\OverridesSectionStorage;
use Drupal\ui_styles\StylePluginManagerInterface;
use Drupal\ui_styles_entity_status\UiStylesEntityStatusInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add classes to entity view build array.
 */
class EntityView implements ContainerInjectionInterface {

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
    StylePluginManagerInterface $stylesManager
  ) {
    $this->stylesManager = $stylesManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    // @phpstan-ignore-next-line
    return new static(
      $container->get('plugin.manager.ui_styles')
    );
  }

  /**
   * Add classes to entity view build array.
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
   * @param string $view_mode
   *   The view mode the entity is rendered in.
   */
  public function alter(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, string $view_mode): void {
    if (!$entity instanceof EntityPublishedInterface) {
      return;
    }

    if ($entity->isPublished()) {
      return;
    }

    /** @var array $settings */
    $settings = \theme_get_setting(UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY) ?? [];
    if (empty($settings)) {
      return;
    }

    $selected = $settings['selected'];
    $extra = $settings['extra'];
    $extra_array = \explode(' ', $extra);
    $styles = \array_merge($selected, $extra_array);
    $styles = \array_unique(\array_filter($styles));

    $build['#attributes'] = $build['#attributes'] ?? [];
    $build['#attributes'] = AttributeHelper::mergeCollections(
      $build['#attributes'],
      [
        'class' => $styles,
      ]
    );

    // Layout Builder display.
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    if ($display instanceof LayoutEntityDisplayInterface && $display->isLayoutBuilderEnabled()) {
      $layout_builder = &$build['_layout_builder'];
      $layout_field_name = OverridesSectionStorage::FIELD_NAME;
      // Layout override: we are dealing with a content entity.
      if ($entity->hasField($layout_field_name) && !$entity->get($layout_field_name)->isEmpty()) {
        foreach ($entity->get($layout_field_name) as $delta => $section_item) {
          /** @var \Drupal\layout_builder\Plugin\Field\FieldType\LayoutSectionItem $section_item */
          if (!$layout_builder[$delta]) {
            // We may encounter some issue when manipulating the last section.
            continue;
          }
          $layout_builder[$delta] = $this->stylesManager->addClasses($layout_builder[$delta], $selected, $extra);
        }
      }
      // Default layout: we are dealing with a config entity.
      else {
        foreach ($display->getSections() as $delta => $section) {
          $layout_builder[$delta] = $this->stylesManager->addClasses($layout_builder[$delta], $selected, $extra);
        }
      }
    }
  }

}
