<?php

declare(strict_types=1);

namespace Drupal\ui_styles_page\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ui_styles\StylePluginManagerInterface;
use Drupal\ui_styles_page\UiStylesPageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Theme settings for regions styles.
 */
class RegionsThemeSettingsForm extends ConfigFormBase {

  /**
   * The plugin manager.
   *
   * @var \Drupal\ui_styles\StylePluginManagerInterface
   */
  protected StylePluginManagerInterface $stylesManager;

  /**
   * An array of configuration names that should be editable.
   *
   * @var array
   */
  protected array $editableConfig = [];

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    $instance = parent::create($container);
    $instance->stylesManager = $container->get('plugin.manager.ui_styles');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return $this->editableConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ui_styles_page.regions.theme_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $theme = ''): array {
    if (empty($theme)) {
      return $form;
    }

    if (empty($this->stylesManager->getDefinitionsForTheme($theme))) {
      $form['warning'] = [
        '#theme' => 'status_messages',
        '#message_list' => [
          'warning' => [
            $this->t('There are no styles available for this theme.'),
          ],
        ],
      ];
      return $form;
    }

    $form_state->set('theme_name', $theme);
    $this->editableConfig = [
      $theme . '.settings',
    ];
    $system_regions = \system_region_list($theme);
    /** @var array $settings */
    $settings = $this->config($theme . '.settings')->get(UiStylesPageInterface::REGION_STYLES_KEY_THEME_SETTINGS) ?? [];

    $form[UiStylesPageInterface::REGION_STYLES_KEY_THEME_SETTINGS] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    foreach ($system_regions as $region_name => $region) {
      $form[UiStylesPageInterface::REGION_STYLES_KEY_THEME_SETTINGS][$region_name] = [
        '#type' => 'ui_styles_styles',
        '#title' => $region,
        '#theme' => $theme,
        '#default_value' => [
          'selected' => $settings[$region_name]['selected'] ?? [],
          'extra' => $settings[$region_name]['extra'] ?? '',
        ],
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $theme = $form_state->get('theme_name');
    $this->editableConfig = [
      $theme . '.settings',
    ];
    /** @var array $values */
    $values = $form_state->getValue(UiStylesPageInterface::REGION_STYLES_KEY_THEME_SETTINGS) ?? [];
    $values = \array_filter($values);

    $config = $this->config($theme . '.settings');
    if (empty($values)) {
      $config->clear(UiStylesPageInterface::REGION_STYLES_KEY_THEME_SETTINGS);
    }
    else {
      $config->set(UiStylesPageInterface::REGION_STYLES_KEY_THEME_SETTINGS, $values);
    }
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
