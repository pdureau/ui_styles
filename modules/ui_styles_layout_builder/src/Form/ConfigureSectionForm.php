<?php

declare(strict_types = 1);

namespace Drupal\ui_styles_layout_builder\Form;

use Drupal\layout_builder\Form\ConfigureSectionForm as OriginalConfigureSectionForm;
use Drupal\layout_builder\Section;
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
   * The section being configured.
   *
   * @var \Drupal\layout_builder\Section
   */
  protected $section;

  /**
   * {@inheritdoc}
   */
  public function getCurrentSection(): Section {
    if (\method_exists(\get_parent_class($this), 'getCurrentSection')) {
      return parent::getCurrentSection();
    }

    // Copy getCurrentSection method from Core 9.5 and adapt to previous core
    // versions available methods and attributes.
    // @phpstan-ignore-next-line
    if (!isset($this->section)) {
      if ($this->isUpdate) {
        $this->section = $this->sectionStorage->getSection($this->delta);
      }
      else {
        // @phpstan-ignore-next-line
        $this->section = new Section($this->layout->getPluginId());
      }
    }

    return $this->section;
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
