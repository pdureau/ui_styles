<?php

namespace Drupal\ui_styles_library\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ui_styles\StylePluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StylesLibraryController.
 *
 * @package Drupal\ui_styles\Controller
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
  public static function create(ContainerInterface $container) {
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
