<?php

declare(strict_types=1);

namespace Drupal\ui_styles_entity_status\HookHandler;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ui_styles\StylePluginManagerInterface;
use Drupal\ui_styles\UiStylesUtility;
use Drupal\ui_styles_entity_status\UiStylesEntityStatusInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alter theme settings form.
 */
class FormSystemThemeSettingsAlter implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The styles plugin manager.
   *
   * @var \Drupal\ui_styles\StylePluginManagerInterface
   */
  protected StylePluginManagerInterface $stylesManager;

  /**
   * Constructor.
   *
   * @param \Drupal\ui_styles\StylePluginManagerInterface $stylesManager
   *   The styles plugin manager.
   */
  public function __construct(
    StylePluginManagerInterface $stylesManager,
  ) {
    $this->stylesManager = $stylesManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('plugin.manager.ui_styles')
    );
  }

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

    if (empty($this->stylesManager->getDefinitionsForTheme($theme))) {
      return;
    }

    $form[UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY] = [
      '#type' => 'details',
      '#title' => $this->t('Unpublished entity styles'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];

    $selected = [];
    $extra = '';
    /** @var array $settings */
    $settings = \theme_get_setting(UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY, $theme) ?? [];
    if (!empty($settings)) {
      $selected = $settings['selected'];
      $extra = $settings['extra'];
    }
    // @phpstan-ignore-next-line
    $form[UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY] = $this->stylesManager->alterForm($form[UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY], $selected, $extra, $theme);

    $form['#validate'][] = [$this, 'validateSystemThemeSettingsForm'];
  }

  /**
   * Filter values.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateSystemThemeSettingsForm(array &$form, FormStateInterface $form_state): void {
    /** @var array $unpublished_classes */
    $unpublished_classes = $form_state->getValue(UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY);
    $selected = UiStylesUtility::extractSelectedStyles($unpublished_classes);
    $extra = $unpublished_classes['_ui_styles_extra'] ?? '';
    if (empty($selected) && empty($extra)) {
      $form_state->setValue(UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY, []);
    }
    else {
      $form_state->setValue(UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY, [
        'selected' => $selected,
        'extra' => $extra,
      ]);
    }
  }

}
