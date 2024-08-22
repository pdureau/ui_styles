<?php

declare(strict_types=1);

namespace Drupal\ui_styles\Source;

use Drupal\ui_styles\Definition\StyleDefinition;

/**
 * Interface for Source plugins.
 */
interface SourceInterface {

  /**
   * Return if the source is applicable or not.
   *
   * @param \Drupal\ui_styles\Definition\StyleDefinition $definition
   *   The style plugin definition.
   *
   * @return bool
   *   The plugin applicability.
   */
  public function isApplicable(StyleDefinition $definition): bool;

  /**
   * Return the form render array.
   *
   * @param \Drupal\ui_styles\Definition\StyleDefinition $definition
   *   The style plugin definition.
   * @param string $selected
   *   The selected option.
   *
   * @return array
   *   The render form array.
   */
  public function getWidgetForm(StyleDefinition $definition, string $selected = ''): array;

}
