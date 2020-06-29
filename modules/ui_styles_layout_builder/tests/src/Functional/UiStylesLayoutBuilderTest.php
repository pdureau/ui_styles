<?php

namespace Drupal\Tests\ui_styles\Functional;

use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\Tests\BrowserTestBase;

/**
 * Test the ui styles layout builder.
 *
 * @group ui_styles
 */
class UiStylesLayoutBuilderTest extends BrowserTestBase {

  /**
   * Default theme.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * Disable schema validation when running tests.
   *
   * @var bool
   *
   * @todo: Fix this by providing actual schema validation.
   */
  protected $strictConfigSchema = FALSE;

  /**
   * A test node to which comments will be posted.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'layout_builder',
    'block',
    'block_content',
    'node',
    'ui_styles_layout_builder_test',
    'ui_styles_layout_builder',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

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
    LayoutBuilderEntityViewDisplay::load('node.page.default')
      ->enableLayoutBuilder()
      ->setOverridable()
      ->save();
  }

  /**
   * Tests to add classes with UI Styles on section.
   */
  public function testUiStylesSection() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $user = $this->drupalCreateUser([], NULL, TRUE);

    $this->drupalLogin($user);

    // Add a class on a section.
    $this->drupalGet('/admin/structure/types/manage/page/display/default/layout');

    // Add a style on section.
    $page->clickLink('Configure Section 1');

    $page->fillField('ui_style[_ui_styles_extra]', 'test-class-extra');
    $page->selectFieldOption('ui_style[ui_styles_test_class]', 'test-class-section');

    $page->pressButton('Update');
    $page->pressButton('Save layout');

    $this->drupalGet('node/' . $this->node->id());
    $assert_session->responseContains('test-class-section');
    // $assert_session->responseContains('test-class-extra');
  }

  /**
   * Tests to add classes with UI Styles on block.
   */
  public function testUiStylesBlock() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $user = $this->drupalCreateUser([], NULL, TRUE);

    $this->drupalLogin($user);

    // Add styles on block.
    $this->drupalGet('/admin/structure/types/manage/page/display/default/layout');

    $page->clickLink('Add block in Section 1');
    $page->clickLink('Title');

    $page->checkField('edit-settings-label-display');

    $page->fillField('ui_styles_title[_ui_styles_extra]', 'test-class-title-extra');
    $page->fillField('ui_style[_ui_styles_extra]', 'test-class-extra');
    $page->selectFieldOption('ui_styles_title[ui_styles_test_class]', 'test-class-title');
    $page->selectFieldOption('ui_style[ui_styles_test_class]', 'test-class-block');

    $page->pressButton('Add block');
    $page->pressButton('Save layout');

    $this->drupalGet('node/' . $this->node->id());
    $assert_session->responseContains('test-class-title');
    $assert_session->responseContains('test-class-block');
    $assert_session->responseContains('test-class-title-extra');
    $assert_session->responseContains('test-class-extra');
  }

  /**
   * Tests to add classes with UI Styles on Section on content.
   */
  public function testUiStylesSectionOverride() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $user = $this->drupalCreateUser([], NULL, TRUE);

    $this->drupalLogin($user);

    $this->drupalGet('node/' . $this->node->id());
    $assert_session->responseNotContains('test-class-section');
    // $assert_session->responseNotContains('test-class-extra');

    $this->drupalGet('node/' . $this->node->id() . '/layout');
    // Add a style on section.
    $page->clickLink('Configure Section 1');

    $page->fillField('ui_style[_ui_styles_extra]', 'test-class-extra');
    $page->selectFieldOption('ui_style[ui_styles_test_class]', 'test-class-section');

    $page->pressButton('Update');
    $page->pressButton('Save layout');

    $this->drupalGet('node/' . $this->node->id());
    $assert_session->responseContains('test-class-section');
    // $assert_session->responseContains('test-class-extra');
  }

  /**
   * Tests to add classes with UI Styles on block on content.
   */
  public function testUiStylesBlockOverride() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $user = $this->drupalCreateUser([], NULL, TRUE);

    $this->drupalLogin($user);

    $this->drupalGet('node/' . $this->node->id());
    $assert_session->responseNotContains('test-class-block');
    $assert_session->responseNotContains('test-class-title');

    $this->drupalGet('node/' . $this->node->id() . '/layout');
    // Add styles on block.
    $page->clickLink('Add block in Section 1');
    $page->clickLink('Title');

    $page->checkField('edit-settings-label-display');

    $page->fillField('ui_styles_title[_ui_styles_extra]', 'test-class-title-extra');
    $page->fillField('ui_style[_ui_styles_extra]', 'test-class-extra');
    $page->selectFieldOption('ui_styles_title[ui_styles_test_class]', 'test-class-title');
    $page->selectFieldOption('ui_style[ui_styles_test_class]', 'test-class-block');

    $page->pressButton('Add block');
    $page->pressButton('Save layout');

    $this->drupalGet('node/' . $this->node->id());
    $assert_session->responseContains('test-class-title');
    $assert_session->responseContains('test-class-block');
    $assert_session->responseContains('test-class-title-extra');
    $assert_session->responseContains('test-class-extra');
  }
}
