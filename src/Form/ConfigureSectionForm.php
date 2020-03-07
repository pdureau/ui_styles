<?php

namespace Drupal\ui_styles\Form;

use Drupal\layout_builder\Form\ConfigureSectionForm as OriginalConfigureSectionForm;
use Drupal\ui_styles\ConfigureSectionFormInterface;

/**
 * Class ConfigureSectionForm.
 *
 * Extend the original form to expose the current section object.
 * May be related to https://www.drupal.org/i/3044117
 */
class ConfigureSectionForm extends OriginalConfigureSectionForm implements ConfigureSectionFormInterface {

  /**
   * {@inheritdoc}
   */
  public function getCurrentSection() {
    // While adding a new section, we have this strange situation where delta is
    // already incremeted, but section not yet added to storage.
    $max = count($this->sectionStorage->getSections());
    if ($this->delta < $max) {
      return $this->sectionStorage->getSection($this->delta);
    }
    return NULL;
  }

}
