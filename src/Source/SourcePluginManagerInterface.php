<?php

declare(strict_types=1);

namespace Drupal\ui_styles\Source;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\ui_styles\Definition\StyleDefinition;

/**
 * Defines an interface for style source plugin managers.
 */
interface SourcePluginManagerInterface extends PluginManagerInterface {

  /**
   * {@inheritdoc}
   *
   * @param array|null $definitions
   *   (optional) The plugin definitions to sort. If omitted, all plugin
   *   definitions are used.
   *
   * @return array
   *   The sorted definitions.
   *
   * @phpstan-ignore-next-line
   */
  public function getSortedDefinitions(?array $definitions = NULL): array;

  /**
   * Returns the first found applicable source.
   *
   * @param \Drupal\ui_styles\Definition\StyleDefinition $styleDefinition
   *   A style definition.
   *
   * @return \Drupal\ui_styles\Source\SourceInterface|null
   *   A plugin instance or null if none found.
   */
  public function getApplicableSourcePlugin(StyleDefinition $styleDefinition): ?SourceInterface;

}
