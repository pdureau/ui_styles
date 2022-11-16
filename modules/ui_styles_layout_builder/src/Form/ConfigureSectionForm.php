<?php

declare(strict_types = 1);

namespace Drupal\ui_styles_layout_builder\Form;

use Drupal\layout_builder\Form\ConfigureSectionForm as OriginalConfigureSectionForm;
use Drupal\ui_styles_layout_builder\ConfigureSectionFormInterface;

/**
 * Class ConfigureSectionForm.
 *
 * Extend the original form to expose the current section object.
 * May be related to https://www.drupal.org/i/3044117
 *
 * @phpstan-ignore-next-line
 */
class ConfigureSectionForm extends OriginalConfigureSectionForm implements ConfigureSectionFormInterface {

  /**
   * {@inheritdoc}
   */
  public function getCurrentSection() {
    // While adding a new section, we have this strange situation where delta is
    // already incremented, but section not yet added to storage.
    $max = \count($this->sectionStorage->getSections());
    if ($this->delta < $max) {
      return $this->sectionStorage->getSection($this->delta);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function isUpdate(): bool {
    return $this->isUpdate;
  }

  /**
   * {@inheritdoc}
   */
  public function getLayout() {
    return $this->layout;
  }

}
