<?php

declare(strict_types = 1);

namespace Drupal\ui_styles_test;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\ui_styles\StylePluginManager;

/**
 * Plugin manager used for tests.
 *
 * @phpstan-ignore-next-line
 */
class DummyStylePluginManager extends StylePluginManager {

  /**
   * The list of styles.
   *
   * @var array
   */
  protected array $styles;

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  public function __construct(
    ModuleHandlerInterface $module_handler,
    ThemeHandlerInterface $theme_handler,
    TranslationInterface $translation,
    CacheBackendInterface $cache_backend,
    array $styles
  ) {
    parent::__construct($module_handler, $theme_handler, $translation, $cache_backend);
    $this->styles = $styles;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions(): array {
    $definitions = $this->styles;
    foreach ($definitions as $plugin_id => &$definition) {
      $this->processDefinition($definition, $plugin_id);
    }
    return $definitions;
  }

}
