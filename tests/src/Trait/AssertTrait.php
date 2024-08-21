<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles\Trait;

/**
 * Helper trait to add assertion methods.
 */
trait AssertTrait {

  /**
   * Check for string in page content with a specific number of times.
   *
   * @param string $haystack
   *   The text to search into.
   * @param string $needle
   *   The text to search in the page content.
   * @param int $times
   *   The number of times it should be found.
   */
  public function assertContainsTimes(string $haystack, string $needle, int $times): void {
    $count = \substr_count($haystack, $needle);
    $message = \sprintf('The string "%s" was found %s times instead of %s times in the HTML response of the current page.', $needle, $count, $times);
    $this->assertEquals($times, $count, $message);
  }

}
