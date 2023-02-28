<?php

declare(strict_types = 1);

namespace Drupal\ui_styles_test;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\ui_styles\MachineNameTrait;

/**
 * Class to test MachineNameTestTrait.
 */
class MachineNameTraitTestClass {
  use MachineNameTrait;

  /**
   * Constructor.
   *
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   The transliteration service.
   */
  public function __construct(
    TransliterationInterface $transliteration
  ) {
    $this->transliteration = $transliteration;
  }

  /**
   * Wrapper around protected method.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|string $string
   *   The string to convert.
   *
   * @return string
   *   The converted string.
   */
  public function callMachineName($string): string {
    return $this->getMachineName($string);
  }

}
