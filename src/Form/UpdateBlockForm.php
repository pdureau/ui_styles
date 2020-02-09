<?php

namespace Drupal\layout_builder_classes\Form;

use Drupal\layout_builder\Form\UpdateBlockForm as OriginalUpdateBlockForm;

/**
 * Class UpdateBlockForm.
 */
class UpdateBlockForm extends OriginalUpdateBlockForm {

  /**
   * Return the UUID of the component.
   *
   * @return string
   *   The UUID of the component.
   */
  public function getUuid() {
    return $this->uuid;
  }

  /**
   * Return the section storage object.
   *
   * @return \Drupal\layout_builder\SectionStorageInterface
   *   The section storage.
   */
  public function getSectionStorage() {
    return $this->sectionStorage;
  }

  /**
   * Return the field delta.
   *
   * @return int
   *   The field delta.
   */
  public function getDelta() {
    return $this->delta;
  }

  /**
   * Return the region.
   *
   * @return string
   *   The region.
   */
  public function getRegion() {
    return $this->region;
  }

  /**
   * Return the block plugin.
   *
   * @return \Drupal\Core\Block\BlockPluginInterface
   *   The block plugin object.
   */
  public function getBlock() {
    return $this->block;
  }

}
