<?php

namespace Drupal\layout_builder_classes_library\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\layout_builder_classes\StylePluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StylesLibraryController.
 *
 * @package Drupal\layout_builder_classes\Controller
 */
class StylesLibraryController extends ControllerBase {

  /**
   * Styles manager service.
   *
   * @var \Drupal\layout_builder_classes\StylePluginManagerInterface
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
    return new static($container->get('plugin.manager.style_plugin'));
  }

  /**
   * Render styles library page.
   *
   * @return array
   *   Style overview page render array.
   */
  public function overview() {
    return [
      '#theme' => 'styles_overview_page',
      '#styles' => $this->stylesManager->getDefinitions(),
    ];
  }

}
