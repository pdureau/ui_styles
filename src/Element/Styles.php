<?php

declare(strict_types=1);

namespace Drupal\ui_styles\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\Attribute\FormElement;
use Drupal\Core\Render\Element\FormElementBase;
use Drupal\ui_styles\Source\SourceInterface;
use Drupal\ui_styles\Source\SourcePluginManagerInterface;
use Drupal\ui_styles\StylePluginManagerInterface;

/**
 * Provides a form element to select styles.
 *
 * Properties:
 * - #default_value: (optional) The default classes. Structured as an array with
 *   keys:
 *   - selected: (optional) array where keys are styles plugin id and values are
 *     styles options.
 *   - extra: (optional) string of extra CSS classes.
 * - #wrapper_type: (optional) Allows to change the wrapper type. Defaults
 *   to details.
 * - #open: (optional) Indicates whether the container should be open by
 *   default if it is a details. Defaults to FALSE.
 * - #theme: (optional) Allows to expose only the styles available with this
 *   theme. Default to default theme.
 *
 * Usage example:
 *
 * @code
 * $form['styles'] = [
 *   '#type' => 'ui_styles_styles',
 *   '#title' => $this->>t('Styles'),
 *   '#default_value' => [
 *      'selected' => $selected,
 *      'extra' => $extra,
 *    ],
 * ];
 *
 * @endcode
 */
#[FormElement('ui_styles_styles')]
class Styles extends FormElementBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;
    return [
      '#input' => TRUE,
      '#multiple' => FALSE,
      '#default_value' => [
        'selected' => [],
        'extra' => '',
      ],
      '#wrapper_type' => 'details',
      '#open' => FALSE,
      '#theme' => '',
      '#process' => [
        [$class, 'buildForm'],
        [$class, 'processGroup'],
      ],
      '#element_validate' => [
        [$class, 'validateStyles'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if (\is_array($input) && isset($input['wrapper']) && !empty($input['wrapper'])) {
      $value = [
        'selected' => static::extractSelectedStyles($input['wrapper']),
        'extra' => $input['wrapper']['_ui_styles_extra'] ?? '',
      ];
    }
    else {
      $value = $element['#default_value'] ?? [
        'selected' => [],
        'extra' => '',
      ];
    }
    return \array_filter($value);
  }

  /**
   * Build the form element.
   *
   * @param array $element
   *   The form element to process.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current state of the form.
   * @param array $completeForm
   *   The complete form structure.
   *
   * @return array
   *   The form element.
   */
  public static function buildForm(array &$element, FormStateInterface $formState, array &$completeForm): array {
    $theme = $element['#theme'] ?? '';
    $stylesManager = static::stylesManager();
    if (!empty($theme)) {
      $groupedPluginDefinitions = $stylesManager->getDefinitionsForTheme($theme);
    }
    else {
      $groupedPluginDefinitions = $stylesManager->getGroupedDefinitions();
    }

    if (empty($groupedPluginDefinitions)) {
      return $element;
    }
    $sourceManager = static::sourceManager();
    $selected = $element['#default_value']['selected'] ?? [];
    $extra = $element['#default_value']['extra'] ?? '';
    $suffix = static::getAppliedSuffix();
    $multipleGroups = (\count($groupedPluginDefinitions) == 1) ? FALSE : TRUE;
    // Open the root details if specified or if a child has an error.
    $open = (!empty($element['#open']) || !empty($element['#children_errors'])) ? TRUE : FALSE;

    $element['wrapper'] = [
      '#type' => $element['#wrapper_type'] ?? 'details',
      '#title' => $element['#title'] ?? '',
      '#description' => $element['#description'] ?? '',
      '#open' => $open,
      '#states' => $element['#states'] ?? [],
    ];

    $globalUsed = $extra;
    foreach ($groupedPluginDefinitions as $groupPluginDefinitions) {
      $groupUsed = FALSE;
      $groupKey = '';
      foreach ($groupPluginDefinitions as $definition) {
        // Get applicable source plugin.
        $styleTypePlugin = $sourceManager->getApplicableSourcePlugin($definition);
        if (!$styleTypePlugin instanceof SourceInterface) {
          continue;
        }
        $id = $definition->id();
        $elementName = 'ui_styles_' . $id;
        $pluginElement = $styleTypePlugin->getWidgetForm($definition, $selected[$id] ?? '');

        // Check if current style is used.
        $used = FALSE;
        if (!empty($pluginElement['#default_value'])) {
          $globalUsed = TRUE;
          $groupUsed = TRUE;
          $used = TRUE;
        }

        // Create group if it does not exist yet.
        if ($multipleGroups && $definition->hasCategory()) {
          $groupKey = static::getMachineName($definition->getCategory());
          if (!isset($element['wrapper'][$groupKey])) {
            $element['wrapper'][$groupKey] = [
              '#type' => 'details',
              '#title' => $definition->getCategory(),
            ];
          }

          $element['wrapper'][$groupKey][$elementName] = $pluginElement;
          if ($used) {
            $element['wrapper'][$groupKey][$elementName]['#title'] .= $suffix;
          }
        }
        else {
          $element['wrapper'][$elementName] = $pluginElement;
          if ($used) {
            $element['wrapper'][$elementName]['#title'] .= $suffix;
          }
        }
      }

      if (!empty($groupKey) && $groupUsed && isset($element['wrapper'][$groupKey]['#title'])) {
        $element['wrapper'][$groupKey]['#title'] .= $suffix;
      }
    }
    $element['wrapper']['_ui_styles_extra'] = [
      '#type' => 'textfield',
      '#title' => \t('Extra classes'),
      '#description' => \t('You can add many values using spaces as separators.'),
      '#default_value' => $extra ?: '',
    ];

    if ($globalUsed && !empty($element['wrapper']['#title'])) {
      $element['wrapper']['#title'] .= $suffix;
    }

    return $element;
  }

  /**
   * Form element validation handler.
   *
   * Override $form_state value using #element_validate and not #after_build
   * because sub element color would recreate the structure.
   */
  public static function validateStyles(array &$element, FormStateInterface $form_state, array &$complete_form): void {
    $form_state->setValueForElement($element, $element['#value']);
  }

  /**
   * Wraps the styles manager.
   *
   * @return \Drupal\ui_styles\StylePluginManagerInterface
   *   The styles plugin manager.
   */
  protected static function stylesManager(): StylePluginManagerInterface {
    return \Drupal::service('plugin.manager.ui_styles');
  }

  /**
   * Wraps the source manager.
   *
   * @return \Drupal\ui_styles\Source\SourcePluginManagerInterface
   *   The source plugin manager.
   */
  protected static function sourceManager(): SourcePluginManagerInterface {
    return \Drupal::service('plugin.manager.ui_styles.source');
  }

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
  protected static function getMachineName($string): string {
    $transliterated = \Drupal::transliteration()->transliterate($string, LanguageInterface::LANGCODE_DEFAULT, '_');
    $transliterated = \mb_strtolower($transliterated);
    $transliterated = \preg_replace('@[^a-z0-9_.]+@', '_', $transliterated);
    return $transliterated ?? '';
  }

  /**
   * Get the suffix to indicate a style is applied.
   *
   * @return string
   *   The "applied" suffix.
   */
  protected static function getAppliedSuffix(): string {
    return ' <sup>(<mark>' . \t('applied') . '</mark>)</sup>';
  }

  /**
   * Get selected styles from form values.
   *
   * Handle groups.
   *
   * @param array $formValues
   *   The form values to extract the selected classes from.
   *
   * @return array
   *   The selected values.
   */
  protected static function extractSelectedStyles(array $formValues): array {
    $selected = [];
    foreach ($formValues as $id => $value) {
      // Case of a group.
      if (\is_array($value)) {
        $selected = \array_merge($selected, static::extractSelectedStyles($value));
      }

      if (empty($value)) {
        continue;
      }

      /** @var string $id */
      if (\strpos($id, 'ui_styles_') === 0) {
        $id = \str_replace('ui_styles_', '', $id);
        $selected[$id] = $value;
      }
    }
    return $selected;
  }

}
