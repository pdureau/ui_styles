<?php

declare(strict_types=1);

namespace Drupal\ui_styles\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\ui_styles\Service\StylesheetGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allow to provide generated CSS files.
 */
class StylesheetController extends ControllerBase {

  public function __construct(
    protected StylesheetGeneratorInterface $stylesheetGenerator,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ui_styles.stylesheet_generator'),
    );
  }

  /**
   * Return the generated CSS.
   */
  public function generateStylesheet(Request $request): CacheableResponse {
    $prefix = (string) $request->query->get('prefix', '');
    $generatedCss = $this->stylesheetGenerator->generateStylesheet($prefix);

    $response = new CacheableResponse(
      $generatedCss,
      Response::HTTP_OK,
      [
        'content-type' => 'text/css',
        'Cache-Control' => 'public, max-age=' . StylesheetGeneratorInterface::MAX_AGE,
      ]
    );

    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => ['max-age' => StylesheetGeneratorInterface::MAX_AGE],
    ]));

    return $response;
  }

}
