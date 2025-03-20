<?php

declare(strict_types=1);

namespace Drupal\ui_styles_test;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\ui_styles\MachineNameTrait;

/**
 * Class to test MachineNameTestTrait.
 */
class MachineNameTraitTestClass {

  use MachineNameTrait;

  public function __construct(
    protected TransliterationInterface $transliteration,
  ) {}

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
