<?php

declare(strict_types=1);

namespace Drupal\ui_styles_library\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ui_styles\StylePluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generates styles library.
 */
class StylesLibraryController extends ControllerBase {

  public function __construct(
    protected StylePluginManagerInterface $stylesManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static($container->get('plugin.manager.ui_styles'));
  }

  /**
   * Render styles library page.
   *
   * @return array
   *   Style overview page render array.
   */
  public function overview() {
    $styles = [];
    foreach ($this->stylesManager->getGroupedDefinitions() as $groupName => $groupedDefinitions) {
      foreach ($groupedDefinitions as $definition) {
        // Provide the same structure as in UI Patterns Library.
        $styles[$groupName][$definition->id()] = $definition->toArray() + [
          'definition' => $definition->toArray(),
        ];
      }
    }

    return [
      '#theme' => 'ui_styles_overview_page',
      '#styles' => $styles,
    ];
  }

}
