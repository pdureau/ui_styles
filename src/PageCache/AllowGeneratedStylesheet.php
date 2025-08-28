<?php

declare(strict_types=1);

namespace Drupal\ui_styles\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Cache policy.
 *
 * This policy allows caching of requests directed to /ui_styles/stylesheet.
 */
class AllowGeneratedStylesheet implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    if ($request->isMethod(Request::METHOD_GET) && $request->getPathInfo() == '/ui_styles/stylesheet') {
      // @phpstan-ignore-next-line
      return static::ALLOW;
    }
    return NULL;
  }

}
