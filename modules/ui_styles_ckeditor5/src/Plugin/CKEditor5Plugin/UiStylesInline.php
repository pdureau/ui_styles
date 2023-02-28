<?php

declare(strict_types = 1);

namespace Drupal\ui_styles_ckeditor5\Plugin\CKEditor5Plugin;

/**
 * UI Styles inline plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
final class UiStylesInline extends UiStylesBase {

  /**
   * The CKE5 config key.
   *
   * @var string
   */
  protected string $ckeditor5ConfigKey = 'uiStylesInline';

}
