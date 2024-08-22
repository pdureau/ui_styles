<?php

declare(strict_types=1);

namespace Drupal\ui_styles\Source;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ui_styles\Definition\StyleDefinition;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Source plugins.
 */
abstract class SourcePluginBase extends PluginBase implements SourceInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable(StyleDefinition $definition): bool {
    return TRUE;
  }

}
