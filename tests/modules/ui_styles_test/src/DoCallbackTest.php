<?php

declare(strict_types = 1);

namespace Drupal\ui_styles_test;

use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Dummy test class for doCallback.
 */
class DoCallbackTest implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks(): array {
    return [
      'myCallbackValidTest',
      'myCallbackNotValidTest',
      'myCallbackNotValidThemeTest',
    ];
  }

  /**
   * Test valid theme.
   */
  public static function myCallbackValidTest(): array {
    return ['#theme' => 'valid_theme'];
  }

  /**
   * Test not valid theme.
   */
  public static function myCallbackNotValidTest(): array {
    return ['#theme' => 'no_valid_theme'];
  }

  /**
   * Test not valid theme key.
   */
  public static function myCallbackNotValidThemeTest(): array {
    return ['#not_valid' => 'no_valid_theme'];
  }

}
