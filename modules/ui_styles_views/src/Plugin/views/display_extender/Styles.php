<?php

declare(strict_types = 1);

namespace Drupal\ui_styles_views\Plugin\views\display_extender;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ui_styles\UiStylesUtility;
use Drupal\views\Plugin\views\display_extender\DisplayExtenderPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Styles display extender plugin.
 *
 * @ingroup views_display_extender_plugins
 *
 * @ViewsDisplayExtender(
 *     id = "ui_styles",
 *     title = @Translation("Styles display extender"),
 *     help = @Translation("Settings to styles to many view sections."),
 *     no_ui = FALSE
 * )
 */
class Styles extends DisplayExtenderPluginBase {

  /**
   * The styles plugin manager.
   *
   * @var \Drupal\ui_styles\StylePluginManagerInterface
   */
  protected $stylesManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->stylesManager = $container->get('plugin.manager.ui_styles');
    return $instance;
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state): void {
    if ($form_state->get('section') != 'ui_styles') {
      return;
    }
    $form['#title'] .= $this->t('UI Styles');

    foreach ($this->getHandledSections() as $section_id => $section_name) {
      if (!$this->isApplicable($section_id)) {
        continue;
      }

      $values = [
        'selected' => [],
        'extra' => '',
      ];
      if (\array_key_exists($section_id, $this->options)) {
        $values = $this->options[$section_id];
      }
      $selected = $values['selected'];
      $extra = $values['extra'];
      $form[$section_id] = [
        '#type' => 'details',
        '#title' => $section_name,
        '#open' => FALSE,
        '#tree' => TRUE,
      ];
      $form[$section_id] = $this->stylesManager->alterForm($form[$section_id], $selected, $extra);
    }
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state): void {
    if ($form_state->get('section') != 'ui_styles') {
      return;
    }

    $form_state_values = $form_state->cleanValues()->getValues();
    foreach ($form_state_values as $section_id => $values) {
      $this->options[$section_id]['selected'] = UiStylesUtility::extractSelectedStyles($values);
      $this->options[$section_id]['extra'] = $values['_ui_styles_extra'];
    }
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  public function optionsSummary(&$categories, &$options): void {
    $has_style = FALSE;
    foreach ($this->options as $section_values) {
      if (!empty($section_values['extra']) || !empty($section_values['selected'])) {
        $has_style = TRUE;
        break;
      }
    }

    $options['ui_styles'] = [
      'category' => 'other',
      'title' => $this->t('UI Styles'),
      'desc' => $this->t('Apply styles to view parts.'),
      'value' => $has_style ? $this->t('Yes') : $this->t('No'),
    ];
  }

  /**
   * Get selected styles.
   *
   * @param string $section
   *   The view section.
   *
   * @return array
   *   Array of HTML classes.
   */
  public function getSelectedStyles(string $section): array {
    if (!\array_key_exists($section, $this->options)) {
      return [];
    }
    return \array_values($this->options[$section]['selected']);
  }

  /**
   * Get extra styles.
   *
   * @param string $section
   *   The view section.
   *
   * @return string
   *   Extra styles.
   */
  public function getExtraStyles(string $section): string {
    if (!\array_key_exists($section, $this->options)) {
      return '';
    }
    return $this->options[$section]['extra'];
  }

  /**
   * If styles can be applied to the section.
   *
   * @param string $section
   *   The view section.
   *
   * @return bool
   *   Applicable or not.
   */
  protected function isApplicable(string $section): bool {
    $display = $this->view->getDisplay();
    $display_definition = $display->getPluginDefinition();
    // Exclude Entity Reference, ReST Export, Feed... displays.
    if (\array_key_exists('entity_reference_display', $display_definition) && $display_definition['entity_reference_display']) {
      return FALSE;
    }
    if (\array_key_exists('returns_response', $display_definition) && $display_definition['returns_response']) {
      return FALSE;
    }
    if ($section == 'pager_options' && !$display->isPagerEnabled()) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * The list of currently handled sections.
   *
   * We don't implement "use_more" ("More link") because the form and the
   * data structure is too different from other sections.
   *
   * @todo add styles to each rows.
   * Area handler plugins (header, footer, empty) are not managed from here.
   *
   * @return array
   *   The list of handled sections label keyed by section ID.
   */
  protected function getHandledSections(): array {
    return [
      'exposed_form_options' => $this->t('Exposed form'),
      'style_options' => $this->t('Style'),
      'pager_options' => $this->t('Pager'),
    ];
  }

}
