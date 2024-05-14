<?php

declare(strict_types=1);

namespace Drupal\ui_styles_layout_builder\HookHandler;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ui_styles\StylePluginManagerInterface;
use Drupal\ui_styles\UiStylesUtility;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Layout Builder block form alter.
 */
class FormLayoutBuilderBlockAlter implements ContainerInjectionInterface {

  use DependencySerializationTrait;
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
  public function __construct(StylePluginManagerInterface $stylesManager) {
    $this->stylesManager = $stylesManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    // @phpstan-ignore-next-line
    return new static(
      $container->get('plugin.manager.ui_styles')
    );
  }

  /**
   * Add UI Styles on block config form.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   */
  public function formAlter(array &$form, FormStateInterface $formState): void {
    if (empty($this->stylesManager->getGroupedDefinitions())) {
      return;
    }
    /** @var \Drupal\layout_builder\Form\ConfigureBlockFormBase $formObject */
    $formObject = $formState->getFormObject();
    $component = $formObject->getCurrentComponent();

    foreach ($this->getBlockParts() as $part_id => $part_title) {
      $form[$part_id] = [
        '#type' => 'details',
        '#title' => $part_title,
        '#open' => FALSE,
      ];

      if ($part_id === 'ui_styles_title') {
        $form[$part_id]['#states'] = [
          'invisible' => [
            ':input[name="settings[label_display]"]' => ['checked' => FALSE],
          ],
        ];
      }

      /** @var array $selected */
      $selected = $component->get($part_id) ?: [];
      /** @var string $extra */
      $extra = $component->get($part_id . '_extra') ?: '';
      // @phpstan-ignore-next-line
      $form[$part_id] = $this->stylesManager->alterForm($form[$part_id], $selected, $extra);
    }

    // Our submit handler must execute before the default one, because the
    // default handler stores the section & component data in the tempstore
    // and we need to update those objects before that happens.
    \array_unshift($form['#submit'], [$this, 'submitForm']);

    // Set form actions to a high weight, just so that we can make our form
    // style element appear right before them.
    $form['actions']['#weight'] = (int) 100;
  }

  /**
   * Custom submit handler for submitting Layout Builder block forms.
   *
   * Persists the configured block style to the component configuration data,
   * which is later persisted to section storage by layout builder's base form.
   */
  public function submitForm(array $form, FormStateInterface $formState): void {
    /** @var \Drupal\layout_builder\Form\ConfigureBlockFormBase $formObject */
    $formObject = $formState->getFormObject();
    $component = $formObject->getCurrentComponent();

    foreach ($this->getBlockParts() as $part_id => $part_title) {
      /** @var array $partValue */
      $partValue = $formState->getValue($part_id);
      // Those values are flat for backward compatibility with initial design.
      // Once https://www.drupal.org/project/drupal/issues/3015152 is ready,
      // move them to proper third_party_settings and wrap them in a bag.
      $component->set($part_id, UiStylesUtility::extractSelectedStyles($partValue));
      $component->set($part_id . '_extra', $partValue['_ui_styles_extra']);
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
      'ui_styles_wrapper' => $this->t('Block styles'),
      'ui_styles_title' => $this->t('Title styles'),
      'ui_styles' => $this->t('Content styles'),
    ];
  }

}
