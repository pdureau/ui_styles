<?php

declare(strict_types=1);

namespace Drupal\ui_styles_block\HookHandler;

use Drupal\block\BlockInterface;

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
      if (!empty($part_styles)) {
        $block->setThirdPartySetting('ui_styles', $part_id, $part_styles);
      }
      else {
        $block->unsetThirdPartySetting('ui_styles', $part_id);
      }
    }
  }

}
