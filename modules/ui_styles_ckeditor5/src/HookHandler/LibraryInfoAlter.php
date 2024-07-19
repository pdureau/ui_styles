<?php

declare(strict_types=1);

namespace Drupal\ui_styles_ckeditor5\HookHandler;

/**
 * Use the appropriate CKE5 JS depending on core version.
 */
class LibraryInfoAlter {

  /**
   * The replaced CK5 related libraries and their compiled JS files.
   *
   * @var string[]
   */
  protected array $replacedLibraries = [
    'internal.ckeditor5.ui_styles_block' => 'js/build/uiStylesBlock.js',
    'internal.ckeditor5.ui_styles_inline' => 'js/build/uiStylesInline.js',
  ];

  /**
   * Use the appropriate CKE5 JS depending on core version.
   *
   * @param array $libraries
   *   An associative array of libraries, passed by reference.
   * @param string $extension
   *   Can either be 'core' or the machine name of the extension that registered
   *   the libraries.
   */
  public function alter(array &$libraries, string $extension): void {
    if ($extension != 'ui_styles_ckeditor5') {
      return;
    }

    if (\version_compare(\Drupal::VERSION, '10.3', '<')) {
      $this->replacePaths($libraries, '/build_10_2/');
    }
  }

  /**
   * Replace path to JS files.
   *
   * @param array $libraries
   *   An associative array of libraries, passed by reference.
   * @param string $replacedBy
   *   The path of the path being changed.
   */
  protected function replacePaths(array &$libraries, string $replacedBy): void {
    foreach ($this->replacedLibraries as $library => $filePath) {
      if (isset($libraries[$library]['js'][$filePath])) {
        $libraries[$library]['js'][\str_replace('/build/', $replacedBy, $filePath)] = $libraries[$library]['js'][$filePath];
        unset($libraries[$library]['js'][$filePath]);
      }
    }
  }

}
