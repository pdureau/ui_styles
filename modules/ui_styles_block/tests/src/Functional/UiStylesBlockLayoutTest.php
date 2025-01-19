<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles_block\Functional;

use Drupal\Core\Url;

/**
 * Block layout UI Styles tests.
 *
 * @group ui_styles
 * @group ui_styles_block
 */
class UiStylesBlockLayoutTest extends UiStylesBlockFunctionalTestBase {

  /**
   * Block test.
   */
  protected const TEST_BLOCK_ID = 'ui_styles_block_test';

  /**
   * Plugin block ID.
   */
  protected const TEST_PLUGIN_BLOCK_ID = 'system_powered_by_block';

  /**
   * The list of block parts.
   *
   * @var string[]
   */
  protected array $blockParts = [
    'block',
    'title',
    'content',
  ];

  /**
   * The list of classes expected or not expected.
   *
   * @var string[]
   */
  protected array $blockClasses = [
    'test-class-block',
    'test-class-title',
    'test-class-content',
    'test-class-extra-block',
    'test-class-extra-title',
    'test-class-extra-content',
  ];

  /**
   * Test config form.
   */
  public function testConfigForm(): void {
    $this->drupalLogin($this->adminUser);
    $edit = [
      'id' => $this::TEST_BLOCK_ID,
      'region' => 'content',
      'settings[label]' => $this->randomMachineName(),
      'settings[label_display]' => TRUE,
    ];
    foreach ($this->blockParts as $blockPart) {
      $edit['ui_styles[' . $blockPart . '][wrapper][ui_styles_test_class]'] = 'test-class-' . $blockPart;
      $edit['ui_styles[' . $blockPart . '][wrapper][_ui_styles_extra]'] = 'test-class-extra-' . $blockPart;
    }
    $this->drupalGet('admin/structure/block/add/' . $this::TEST_PLUGIN_BLOCK_ID . '/' . $this->defaultTheme);
    $this->submitForm($edit, 'Save block');

    // Load the block to check the configuration structure.
    $blockStorage = $this->entityTypeManager->getStorage('block');
    /** @var \Drupal\block\BlockInterface $block */
    $block = $blockStorage->load($this::TEST_BLOCK_ID);
    $config_styles = $block->getThirdPartySettings('ui_styles');
    foreach ($this->blockParts as $blockPart) {
      $this->assertEquals('test-class-' . $blockPart, $config_styles[$blockPart]['selected']['test_class']);
      $this->assertEquals('test-class-extra-' . $blockPart, $config_styles[$blockPart]['extra']);
    }
  }

  /**
   * Test that classes has been injected.
   */
  public function testHtmlAttributes(): void {
    $assert_session = $this->assertSession();
    $blockStorage = $this->entityTypeManager->getStorage('block');

    $this->drupalGet(Url::fromRoute('<front>'));
    foreach ($this->blockClasses as $class) {
      $assert_session->elementNotExists('css', '.' . $class);
    }

    // Place the block.
    /** @var \Drupal\block\BlockInterface $block */
    $block = $blockStorage->create($this->getBlockData());
    foreach ($this->blockParts as $blockPart) {
      $block->setThirdPartySetting('ui_styles', $blockPart, [
        'selected' => [
          'test_class' => 'test-class-' . $blockPart,
        ],
        'extra' => 'test-class-extra-' . $blockPart,
      ]);
    }
    $block->save();

    $this->drupalGet(Url::fromRoute('<front>'));
    foreach ($this->blockClasses as $class) {
      $assert_session->elementExists('css', '.' . $class);
    }
  }

  /**
   * Block test data.
   */
  protected function getBlockData(): array {
    return [
      'id' => $this::TEST_BLOCK_ID,
      'plugin' => $this::TEST_PLUGIN_BLOCK_ID,
      'region' => 'content',
      'theme' => $this->defaultTheme,
      'settings' => [
        'label' => $this->randomMachineName(),
        'label_display' => 'visible',
      ],
    ];
  }

}
