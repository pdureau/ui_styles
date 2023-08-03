<?php

declare(strict_types = 1);

namespace Drupal\ui_styles_block\HookHandler;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ui_styles\StylePluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Block Layout Alter.
 */
class FormBlockFormAlter implements ContainerInjectionInterface {
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
  public function blockFormAlter(array &$form, FormStateInterface $formState): void {
    /** @var \Drupal\block\BlockForm $form_object */
    $form_object = $formState->getFormObject();
    /** @var \Drupal\block\BlockInterface $block */
    $block = $form_object->getEntity();

    if (!($form_object instanceof EntityFormInterface)) {
      return;
    }

    $form['ui_styles'] = [
      '#type' => 'container',
    ];

    foreach ($this->getBlockParts() as $part_id => $part_title) {
      $form['ui_styles'][$part_id] = [
        '#type' => 'details',
        '#title' => $part_title,
        '#open' => FALSE,
      ];

      if ($part_id === 'title') {
        $form['ui_styles'][$part_id]['#states'] = [
          'invisible' => [
            ':input[name="settings[label_display]"]' => ['checked' => FALSE],
          ],
        ];
      }

      $selected = [];
      $extra = '';
      /** @var array $settings */
      $settings = $block->getThirdPartySetting('ui_styles', $part_id, []);
      if (!empty($settings)) {
        $selected = $settings['selected'];
        $extra = $settings['extra'];
      }
      // @phpstan-ignore-next-line
      $form['ui_styles'][$part_id] = $this->stylesManager->alterForm($form['ui_styles'][$part_id], $selected, $extra);
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
      'title' => $this->t('Block title styles'),
      'content' => $this->t('Block content styles'),
    ];
  }

}
