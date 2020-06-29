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

    // Create a content type.
    $this->drupalCreateContentType(
      [
        'type' => 'page',
        'name' => 'Basic page',
      ]
    );

    // Enable layout builder on content type.
    LayoutBuilderEntityViewDisplay::load('node.page.default')
      ->enableLayoutBuilder()
      ->setOverridable()
      ->save();
  }

  /**
   * Tests to add classes with UI Styles.
   */
  public function testUiStyles() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $user = $this->drupalCreateUser([], NULL, TRUE);

    // Create a node.
    $node = $this->createNode([
      'type' => 'page',
      'title' => 'My node title',
      'body' => [
        [
          'value' => 'My node body',
        ],
      ],
    ]);

    $this->drupalLogin($user);

    // Add a class on a section.
    $this->drupalGet('/admin/structure/types/manage/page/display/default/layout');

    // Add a style on section.
    $page->clickLink('Configure Section 1');
    $page->selectFieldOption('edit-ui-styles-test-class', 'test-class-section');

    $page->pressButton('Update');
    $page->pressButton('Save layout');

    $this->drupalGet('node/' . $node->id());
    $assert_session->responseContains('test-class-section');

    // Add styles on block.
    $this->drupalGet('/admin/structure/types/manage/page/display/default/layout');

    $page->clickLink('Add block in Section 1, Content region');
    $page->clickLink('Title');

    $page->checkField('settings[label_display]');
    $page->selectFieldOption('edit-ui-styles-test-class', 'test-class-block');

    $page->pressButton('Add block');
    $page->pressButton('Save layout');

    $this->drupalGet('node/' . $node->id());
    $assert_session->responseContains('test-class-block');
  }

}
