<?php

declare(strict_types=1);

namespace Drupal\ui_styles;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\layout_builder\SectionStorageInterface;

/**
 * Helper trait to get Layout Builder section storage overrides.
 *
 * Workaround until Core API improvements is done.
 */
trait SectionStorageTrait {

  /**
   * Get section storage.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The entity view display holding the display options configured for the
   *   entity components.
   * @param string $viewMode
   *   The view mode the entity is rendered in.
   *
   * @return \Drupal\layout_builder\SectionStorageInterface|null
   *   The section storage if one matched all contexts, or NULL otherwise.
   *
   * @see \Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay::buildSections()
   */
  protected function getDisplaySectionStorage(ContentEntityInterface $entity, EntityViewDisplayInterface $display, string $viewMode): ?SectionStorageInterface {
    /** @var \Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface $section_storage_manager */
    $section_storage_manager = \Drupal::service('plugin.manager.layout_builder.section_storage');

    $contexts = $this->getContextsForEntity($entity, $display, $viewMode);
    $label = new TranslatableMarkup('@entity being viewed', [
      '@entity' => $entity->getEntityType()->getSingularLabel(),
    ]);
    $contexts['layout_builder.entity'] = EntityContext::fromEntity($entity, $label);
    $cacheability = new CacheableMetadata();
    return $section_storage_manager->findByContext($contexts, $cacheability);
  }

  /**
   * Gets the available contexts for a given entity.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The entity view display holding the display options configured for the
   *   entity components.
   * @param string $viewMode
   *   The view mode the entity is rendered in.
   *
   * @return \Drupal\Core\Plugin\Context\ContextInterface[]
   *   An array of context objects for a given entity.
   *
   * @see \Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay::getContextsForEntity()
   */
  protected function getContextsForEntity(FieldableEntityInterface $entity, EntityViewDisplayInterface $display, string $viewMode): array {
    /** @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface $contextRepository */
    $contextRepository = \Drupal::service('context.repository');
    $available_context_ids = \array_keys($contextRepository->getAvailableContexts());
    return [
      'view_mode' => new Context(ContextDefinition::create('string'), $viewMode),
      'entity' => EntityContext::fromEntity($entity),
      'display' => EntityContext::fromEntity($display),
    ] + $contextRepository->getRuntimeContexts($available_context_ids);
  }

}
