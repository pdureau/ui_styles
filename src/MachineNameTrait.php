<?php

declare(strict_types = 1);

namespace Drupal\ui_styles;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Helper trait to get machine name version of a string.
 */
trait MachineNameTrait {

  /**
   * The transliteration service.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected TransliterationInterface $transliteration;

  /**
   * Generates a machine name from a string.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|string $string
   *   The string to convert.
   *
   * @return string
   *   The converted string.
   *
   * @see \Drupal\Core\Block\BlockBase::getMachineNameSuggestion()
   * @see \Drupal\system\MachineNameController::transliterate()
   */
  protected function getMachineName($string): string {
    $transliterated = $this->transliteration->transliterate($string, LanguageInterface::LANGCODE_DEFAULT, '_');
    $transliterated = \mb_strtolower($transliterated);
    $transliterated = \preg_replace('@[^a-z0-9_.]+@', '_', $transliterated);
    return $transliterated ?? '';
  }

}
