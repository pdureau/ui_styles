<?php

declare(strict_types = 1);

namespace Drupal\Tests\ui_styles\Functional;

use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\Tests\BrowserTestBase;

/**
 * Test the ui styles layout builder.
 *
 * @group ui_styles
 * @group ui_styles_layout_builder
 */
class UiStylesLayoutBuilderTest extends BrowserTestBase {

  /**
   * Default theme.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * A test node to which comments will be posted.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * The user used in the tests.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

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
    $this->drupalLogin($this->user);

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
    $assert_session->responseNotContains('test-class-title-extra');
    $assert_session->responseNotContains('test-class-title');
    $assert_session->responseNotContains('test-class-extra');
    $assert_session->responseNotContains('test-class-block');

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
    $page->fillField('ui_styles_title[_ui_styles_extra]', 'test-class-title-extra');
    $page->selectFieldOption('ui_styles_title[ui_styles_test_class]', 'test-class-title');
    $page->fillField('ui_styles[_ui_styles_extra]', 'test-class-extra');
    $page->selectFieldOption('ui_styles[ui_styles_test_class]', 'test-class-block');
    $page->pressButton('Add block');

    // Body field block.
    $page->clickLink('Add block in Section 1');
    $page->clickLink('Body');
    $page->checkField('edit-settings-label-display');
    $page->fillField('ui_styles_title[_ui_styles_extra]', 'test-class-body-title-extra');
    $page->selectFieldOption('ui_styles_title[ui_styles_test_class]', 'test-class-body-field-title');
    $page->fillField('ui_styles[_ui_styles_extra]', 'test-class-body-extra');
    $page->selectFieldOption('ui_styles[ui_styles_test_class]', 'test-class-body-field');
    $page->pressButton('Add block');

    $page->pressButton('Save layout');

    $this->drupalGet('node/' . $this->node->id());
    $assert_session->responseContains('test-class-title-extra');
    $assert_session->responseContains('test-class-title');
    $assert_session->responseContains('test-class-extra');
    $assert_session->responseContains('test-class-block');
    $assert_session->responseContains('test-class-body-title-extra');
    $assert_session->responseContains('test-class-body-field-title');
    $assert_session->responseContains('test-class-body-extra');
    $assert_session->responseContains('test-class-body-field');
  }

}
