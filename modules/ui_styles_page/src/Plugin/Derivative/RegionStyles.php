<?php

declare(strict_types=1);

namespace Drupal\ui_styles_page\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\ui_styles\StylePluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derivative class for the local tasks and menu links for regions styles.
 */
class RegionStyles extends DeriverBase implements ContainerDeriverInterface {

  public function __construct(
    protected ThemeHandlerInterface $themeHandler,
    protected StylePluginManagerInterface $stylesManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id): static {
    return new static(
      $container->get('theme_handler'),
      $container->get('plugin.manager.ui_styles')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    foreach ($this->themeHandler->listInfo() as $theme_name => $theme) {
      $definitions = $this->stylesManager->getDefinitionsForTheme($theme_name);
      if (empty($definitions)) {
        continue;
      }

      $this->derivatives[$theme_name] = $base_plugin_definition;
      $this->derivatives[$theme_name]['title'] = $theme->info['name'];
      $this->derivatives[$theme_name]['route_parameters'] = [
        'theme' => $theme_name,
      ];
    }

    return $this->derivatives;
  }

}
