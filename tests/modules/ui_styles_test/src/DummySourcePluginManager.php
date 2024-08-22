<?php

declare(strict_types=1);

namespace Drupal\ui_styles_test;

use Drupal\ui_styles\Source\SourcePluginManager;

/**
 * Plugin manager used for tests.
 *
 * @phpstan-ignore-next-line
 */
class DummySourcePluginManager extends SourcePluginManager {

  /**
   * The list of sources.
   *
   * @var array
   */
  protected array $sources = [];

  /**
   * {@inheritdoc}
   */
  public function getDefinitions(): array {
    $definitions = $this->sources;
    foreach ($definitions as $plugin_id => &$definition) {
      $this->processDefinition($definition, $plugin_id);
    }
    return $definitions;
  }

  /**
   * Getter.
   *
   * @return array
   *   Property value.
   */
  public function getSources(): array {
    return $this->sources;
  }

  /**
   * Setter.
   *
   * @param array $sources
   *   Property value.
   *
   * @return $this
   */
  public function setSources(array $sources) {
    $this->sources = $sources;
    return $this;
  }

}
