<?php

declare(strict_types=1);

namespace Drupal\ui_styles\Service;

/**
 * Interface for the style sheet generator service.
 */
interface StylesheetGeneratorInterface {

  /**
   * Drupal cache static files for one year by default.
   */
  public const MAX_AGE = 31536000;

  /**
   * Generate CSS from styles.
   *
   * @param string $prefix
   *   The prefix to add to isolated styles.
   *
   * @return string
   *   The generated CSS.
   */
  public function generateStylesheet(string $prefix = ''): string;

}
