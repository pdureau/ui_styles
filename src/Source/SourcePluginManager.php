<?php

declare(strict_types=1);

namespace Drupal\ui_styles\Source;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\ui_styles\Attribute\Source;
use Drupal\ui_styles\Definition\StyleDefinition;

/**
 * Source plugin manager.
 */
class SourcePluginManager extends DefaultPluginManager implements SourcePluginManagerInterface {

  /**
   * Constructor.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/UiStyles/Source',
      $namespaces,
      $module_handler,
      'Drupal\ui_styles\Source\SourceInterface',
      Source::class
    );
    $this->alterInfo('ui_styles_source_info');
    $this->setCacheBackend($cache_backend, 'ui_styles_source_plugins');

    $this->defaults = [
      'id' => '',
      'label' => '',
      'weight' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  public function getSortedDefinitions(?array $definitions = NULL): array {
    $definitions = $definitions ?? $this->getDefinitions();

    \uasort($definitions, static function (array $item1, array $item2) {
      // Sort by weight.
      $weight = $item1['weight'] <=> $item2['weight'];
      if ($weight != 0) {
        return $weight;
      }

      // Sort by plugin ID.
      // In case the plugin ID starts with an underscore.
      $id1 = \str_replace('_', '', $item1['id']);
      $id2 = \str_replace('_', '', $item2['id']);
      return \strnatcasecmp($id1, $id2);
    });

    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getApplicableSourcePlugin(StyleDefinition $styleDefinition): ?SourceInterface {
    $definitions = $this->getSortedDefinitions();
    foreach ($definitions as $definition) {
      $instance = $this->createInstance($definition['id']);
      if ($instance instanceof SourceInterface && $instance->isApplicable($styleDefinition)) {
        return $instance;
      }
    }

    return NULL;
  }

}
