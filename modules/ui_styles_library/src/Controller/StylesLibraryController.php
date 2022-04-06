<?php

declare(strict_types = 1);

namespace Drupal\ui_styles_library\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ui_styles\StylePluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generates styles library.
 */
class StylesLibraryController extends ControllerBase {

  /**
   * Styles manager service.
   *
   * @var \Drupal\ui_styles\StylePluginManagerInterface
   */
  protected $stylesManager;

  /**
   * {@inheritdoc}
   */
  final public function __construct(StylePluginManagerInterface $styles_manager) {
    $this->stylesManager = $styles_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static($container->get('plugin.manager.ui_styles'));
  }

  /**
   * Render styles library page.
   *
   * @return array
   *   Style overview page render array.
   */
  public function overview() {
    return [
      '#theme' => 'ui_styles_overview_page',
      '#styles' => $this->stylesManager->getDefinitions(),
    ];
  }

}
