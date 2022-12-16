<?php

declare(strict_types = 1);

namespace Drupal\ui_styles_test;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\ui_styles\StylePluginManager;

/**
 * Plugin manager used for tests.
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
   */
  public function __construct(
    ModuleHandlerInterface $module_handler,
    ThemeHandlerInterface $theme_handler,
    TranslationInterface $translation,
    CacheBackendInterface $cache_backend,
    MessengerInterface $messenger,
    array $styles
  ) {
    parent::__construct($module_handler, $theme_handler, $translation, $cache_backend, $messenger);
    $this->styles = $styles;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions(): array {
    return $this->styles;
  }

}
