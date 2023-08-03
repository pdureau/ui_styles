<?php

declare(strict_types = 1);

namespace Drupal\ui_styles_block\HookHandler;

use Drupal\block\BlockInterface;
use Drupal\ui_styles\UiStylesUtility;

/**
 * Handle block presave.
 */
class BlockPresave {

  /**
   * Set third party settings.
   *
   * @param \Drupal\block\BlockInterface $block
   *   The block being pre-saved.
   */
  public function setThirdPartySettings(BlockInterface $block): void {
    $uiStyles = $block->get('ui_styles');
    if ($uiStyles == NULL || !\is_array($uiStyles)) {
      return;
    }
    foreach ($uiStyles as $part_id => $part_styles) {
      $selected = UiStylesUtility::extractSelectedStyles($part_styles);
      $extra = $part_styles['_ui_styles_extra'];
      $block->setThirdPartySetting('ui_styles', $part_id, [
        'selected' => $selected,
        'extra' => $extra,
      ]);
    }
  }

}
