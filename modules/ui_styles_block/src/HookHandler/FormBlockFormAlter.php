<?php

declare(strict_types=1);

namespace Drupal\ui_styles_block\HookHandler;

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Block Layout Alter.
 */
class FormBlockFormAlter {

  use StringTranslationTrait;

  /**
   * Add UI Styles on block config form.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   */
  public function blockFormAlter(array &$form, FormStateInterface $formState): void {
    /** @var \Drupal\block\BlockForm $form_object */
    $form_object = $formState->getFormObject();
    /** @var \Drupal\block\BlockInterface $block */
    $block = $form_object->getEntity();

    if (!($form_object instanceof EntityFormInterface)) {
      return;
    }

    $theme = $block->getTheme();
    if ($theme == NULL) {
      return;
    }

    $form['ui_styles'] = [
      '#type' => 'container',
    ];

    foreach ($this->getBlockParts() as $part_id => $part_title) {
      /** @var array $settings */
      $settings = $block->getThirdPartySetting('ui_styles', $part_id, []);
      $form['ui_styles'][$part_id] = [
        '#type' => 'ui_styles_styles',
        '#title' => $part_title,
        '#theme' => $theme,
        '#default_value' => [
          'selected' => $settings['selected'] ?? [],
          'extra' => $settings['extra'] ?? '',
        ],
      ];

      if ($part_id === 'title') {
        $form['ui_styles'][$part_id]['#states'] = [
          'invisible' => [
            ':input[name="settings[label_display]"]' => ['checked' => FALSE],
          ],
        ];
      }
    }
  }

  /**
   * Get the block parts.
   *
   * @return array
   *   The block parts.
   */
  protected function getBlockParts(): array {
    return [
      'block' => $this->t('Block styles'),
      'title' => $this->t('Title styles'),
      'content' => $this->t('Content styles'),
    ];
  }

}
