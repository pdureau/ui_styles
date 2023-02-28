<?php

declare(strict_types = 1);

namespace Drupal\Tests\ui_styles_layout_builder\Functional;

use Drupal\block_content\BlockContentInterface;
use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\node\NodeInterface;
use Drupal\Tests\block_content\Functional\BlockContentTestBase;
use Drupal\user\UserInterface;

/**
 * Test the ui styles layout builder.
 *
 * @group ui_styles
 * @group ui_styles_layout_builder
 */
class UiStylesLayoutBuilderTest extends BlockContentTestBase {

  /**
   * Default theme.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * A test node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected NodeInterface $node;

  /**
   * A test block content.
   *
   * @var \Drupal\block_content\BlockContentInterface
   */
  protected BlockContentInterface $blockContent;

  /**
   * The user used in the tests.
   *
   * @var \Drupal\user\UserInterface
   */
  protected UserInterface $user;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'block_content',
    'field_ui',
    'layout_builder',
    'node',
    'ui_styles_layout_builder',
    'ui_styles_layout_builder_test',
  ];

  /**
   * The list of Layout Builder block classes expected or not expected.
   *
   * @var array
   */
  protected array $blockClasses = [
    'test-class-title-block-wrapper',
    'test-class-title-block-title',
    'test-class-title-block-content',
    'test-class-body-block-wrapper',
    'test-class-body-block-title',
    'test-class-body-block-content',
    'test-class-block-content-entity-block-wrapper',
    'test-class-block-content-entity-block-title',
    // @todo in https://www.drupal.org/project/ui_styles/issues/3334615
    // or in https://www.drupal.org/project/ui_styles/issues/3334791.
    // 'test-class-block-content-entity-block-content',
    'test-class-title-block-extra-wrapper',
    'test-class-title-block-extra-title',
    'test-class-title-block-extra-content',
    'test-class-body-block-extra-wrapper',
    'test-class-body-block-extra-title',
    'test-class-body-block-extra-content',
    'test-class-block-content-entity-block-extra-wrapper',
    'test-class-block-content-entity-block-extra-title',
    // @todo in https://www.drupal.org/project/ui_styles/issues/3334615
    // or in https://www.drupal.org/project/ui_styles/issues/3334791.
    // 'test-class-block-content-entity-block-extra-content',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->user = $this->drupalCreateUser([], NULL, TRUE);

    $this->drupalPlaceBlock('local_tasks_block');

    // Create a content type.
    $this->drupalCreateContentType(
      [
        'type' => 'page',
        'name' => 'Basic page',
      ]
    );

    // Create a node.
    $this->node = $this->createNode([
      'type' => 'page',
      'title' => 'My node title',
      'body' => [
        [
          'value' => 'My node body',
        ],
      ],
    ]);

    // Create a block content.
    $this->blockContent = $this->createBlockContent('My block content');

    // Enable layout builder on content type.
    $layout_builder_view_display = LayoutBuilderEntityViewDisplay::load('node.page.default');
    if ($layout_builder_view_display != NULL) {
      $layout_builder_view_display->enableLayoutBuilder()
        ->setOverridable()
        ->save();
    }
  }

  /**
   * Tests to add classes with UI Styles on section.
   */
  public function testUiStylesSection(): void {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $this->drupalLogin($this->user);

    // Add a class on a section.
    $this->drupalGet('/admin/structure/types/manage/page/display/default/layout');

    // Add a style on section.
    $page->clickLink('Configure Section 1');

    $page->fillField('ui_styles[_ui_styles_extra]', 'test-class-extra');
    $page->selectFieldOption('ui_styles[ui_styles_test_class]', 'test-class-section');

    $page->pressButton('Update');
    $page->pressButton('Save layout');

    $this->drupalGet('node/' . $this->node->id());
    $assert_session->responseContains('test-class-extra');
    $assert_session->responseContains('test-class-section');
  }

  /**
   * Tests to add classes with UI Styles on block.
   */
  public function testUiStylesBlock(): void {
    $assert_session = $this->assertSession();
    $this->drupalLogin($this->user);

    $this->drupalGet('node/' . $this->node->id());
    foreach ($this->blockClasses as $class) {
      $assert_session->responseNotContains($class);
    }

    // Add styles on block.
    $this->drupalGet('/admin/structure/types/manage/page/display/default/layout');
    $this->addBlocksAndCheck();
  }

  /**
   * Tests to add classes with UI Styles on Section on content.
   */
  public function testUiStylesSectionOverride(): void {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $this->drupalLogin($this->user);

    $this->drupalGet('node/' . $this->node->id());
    $assert_session->responseNotContains('test-class-extra');
    $assert_session->responseNotContains('test-class-section');

    $this->drupalGet('node/' . $this->node->id() . '/layout');
    // Add a style on section.
    $page->clickLink('Configure Section 1');

    $page->fillField('ui_styles[_ui_styles_extra]', 'test-class-extra');
    $page->selectFieldOption('ui_styles[ui_styles_test_class]', 'test-class-section');

    $page->pressButton('Update');
    $page->pressButton('Save layout');

    $this->drupalGet('node/' . $this->node->id());
    $assert_session->responseContains('test-class-extra');
    $assert_session->responseContains('test-class-section');
  }

  /**
   * Tests to add classes with UI Styles on block on content.
   */
  public function testUiStylesBlockOverride(): void {
    $assert_session = $this->assertSession();
    $this->drupalLogin($this->user);

    $this->drupalGet('node/' . $this->node->id());
    foreach ($this->blockClasses as $class) {
      $assert_session->responseNotContains($class);
    }

    $this->drupalGet('node/' . $this->node->id() . '/layout');
    $this->addBlocksAndCheck();
  }

  /**
   * Add blocks in Layout Builder and check for CSS classes.
   */
  protected function addBlocksAndCheck(): void {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Title block.
    $page->clickLink('Add block in Section 1');
    $page->clickLink('Title');
    $page->checkField('edit-settings-label-display');
    $page->fillField('ui_styles_wrapper[_ui_styles_extra]', 'test-class-title-block-extra-wrapper');
    $page->selectFieldOption('ui_styles_wrapper[ui_styles_test_class]', 'test-class-title-block-wrapper');
    $page->fillField('ui_styles_title[_ui_styles_extra]', 'test-class-title-block-extra-title');
    $page->selectFieldOption('ui_styles_title[ui_styles_test_class]', 'test-class-title-block-title');
    $page->fillField('ui_styles[_ui_styles_extra]', 'test-class-title-block-extra-content');
    $page->selectFieldOption('ui_styles[ui_styles_test_class]', 'test-class-title-block-content');
    $page->pressButton('Add block');

    // Body field block.
    $page->clickLink('Add block in Section 1');
    $page->clickLink('Body');
    $page->checkField('edit-settings-label-display');
    $page->fillField('ui_styles_wrapper[_ui_styles_extra]', 'test-class-body-block-extra-wrapper');
    $page->selectFieldOption('ui_styles_wrapper[ui_styles_test_class]', 'test-class-body-block-wrapper');
    $page->fillField('ui_styles_title[_ui_styles_extra]', 'test-class-body-block-extra-title');
    $page->selectFieldOption('ui_styles_title[ui_styles_test_class]', 'test-class-body-block-title');
    $page->fillField('ui_styles[_ui_styles_extra]', 'test-class-body-block-extra-content');
    $page->selectFieldOption('ui_styles[ui_styles_test_class]', 'test-class-body-block-content');
    $page->pressButton('Add block');

    // Block content block.
    $page->clickLink('Add block in Section 1');
    $page->clickLink('My block content');
    $page->checkField('edit-settings-label-display');
    $page->fillField('ui_styles_wrapper[_ui_styles_extra]', 'test-class-block-content-entity-block-extra-wrapper');
    $page->selectFieldOption('ui_styles_wrapper[ui_styles_test_class]', 'test-class-block-content-entity-block-wrapper');
    $page->fillField('ui_styles_title[_ui_styles_extra]', 'test-class-block-content-entity-block-extra-title');
    $page->selectFieldOption('ui_styles_title[ui_styles_test_class]', 'test-class-block-content-entity-block-title');
    $page->fillField('ui_styles[_ui_styles_extra]', 'test-class-block-content-entity-block-extra-content');
    $page->selectFieldOption('ui_styles[ui_styles_test_class]', 'test-class-block-content-entity-block-content');
    $page->pressButton('Add block');

    $page->pressButton('Save layout');

    $this->drupalGet('node/' . $this->node->id());
    foreach ($this->blockClasses as $class) {
      $assert_session->responseContains($class);
    }
  }

}
