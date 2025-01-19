<?php

declare(strict_types=1);

namespace Drupal\ui_styles_entity_status\HookHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ui_styles_entity_status\UiStylesEntityStatusInterface;

/**
 * Alter theme settings form.
 */
class FormSystemThemeSettingsAlter {

  use StringTranslationTrait;

  /**
   * Add unpublished entity styles form in system theme settings.
   */
  public function alter(array &$form, FormStateInterface $form_state): void {
    $theme = '';
    // Extract theme name from $form.
    if (isset($form['config_key']['#value']) && \is_string($form['config_key']['#value'])) {
      $config_key = $form['config_key']['#value'];
      $config_key_parts = \explode('.', $config_key);

      if (isset($config_key_parts[0])) {
        $theme = $config_key_parts[0];
      }
    }
    // Impossible to determine on which theme settings form we are.
    if (empty($theme)) {
      return;
    }

    /** @var array $settings */
    $settings = \theme_get_setting(UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY, $theme) ?? [];
    $form[UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY] = [
      '#type' => 'ui_styles_styles',
      '#title' => $this->t('Unpublished entity styles'),
      '#theme' => $theme,
      '#default_value' => [
        'selected' => $settings['selected'] ?? [],
        'extra' => $settings['extra'] ?? '',
      ],
      '#tree' => TRUE,
    ];
  }

}
