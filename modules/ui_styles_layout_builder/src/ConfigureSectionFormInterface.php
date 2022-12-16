<?php

declare(strict_types = 1);

namespace Drupal\ui_styles_layout_builder;

use Drupal\Core\Form\FormInterface;
use Drupal\layout_builder\Section;

/**
 * Defines an interface for ConfigureSectionForm.
 */
interface ConfigureSectionFormInterface extends FormInterface {

  /**
   * Get the layout section being modified.
   *
   * @return \Drupal\layout_builder\Section
   *   The layout section.
   */
  public function getCurrentSection(): Section;

  /**
   * Indicates whether the section is being added (false) or updated (true).
   *
   * @return bool
   *   The section status: added (false) or updated (true).
   */
  public function isUpdate(): bool;

  /**
   * Get the layout plugin being modified.
   *
   * @return \Drupal\Core\Layout\LayoutInterface|\Drupal\Core\Plugin\PluginFormInterface
   *   The layout plugin object.
   */
  public function getLayout();

}
