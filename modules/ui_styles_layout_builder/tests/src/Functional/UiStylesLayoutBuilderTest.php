<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles_layout_builder\Functional;

use Drupal\Tests\block_content\Functional\BlockContentTestBase;
use Drupal\Tests\ui_styles\Trait\AssertTrait;
use Drupal\block_content\BlockContentInterface;
use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

/**
 * Test the ui styles layout builder.
 *
 * @group ui_styles
 * @group ui_styles_layout_builder
 */
class UiStylesLayoutBuilderTest extends BlockContentTestBase {

  use AssertTrait;

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
    'test-class-block-content-entity-block-content',
    'test-class-title-block-extra-wrapper',
    'test-class-title-block-extra-title',
    'test-class-title-block-extra-content',
    'test-class-body-block-extra-wrapper',
    'test-class-body-block-extra-title',
    'test-class-body-block-extra-content',
    'test-class-block-content-entity-block-extra-wrapper',
    'test-class-block-content-entity-block-extra-title',
    'test-class-block-content-entity-block-extra-content',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $user = $this->drupalCreateUser([], NULL, TRUE);
    if (!($user instanceof UserInterface)) {
      $this->fail('Impossible to create the tests user.');
    }
    $this->user = $user;

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
    $this->blockContent->set('body', [
      'value' => 'My body text (block content)',
      'format' => 'plain_text',
    ]);
    $this->blockContent->save();

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
    $page = $this->getSession()->getPage();

    $this->drupalLogin($this->user);

    // Add a class on a section.
    $this->drupalGet('/admin/structure/types/manage/page/display/default/layout');

    // Add a style on section.
    $page->clickLink('Configure Section 1');

    $page->fillField('ui_styles[section][wrapper][_ui_styles_extra]', 'test-class-extra-section');
    $page->selectFieldOption('ui_styles[section][wrapper][ui_styles_test_class]', 'test-class-section');
    $page->fillField('ui_styles[regions][content][wrapper][_ui_styles_extra]', 'test-class-extra-region');
    $page->selectFieldOption('ui_styles[regions][content][wrapper][ui_styles_test_class]', 'test-class-region');

    $page->pressButton('Update');
    $page->pressButton('Save layout');

    $this->drupalGet('node/' . $this->node->id());
    $pageContent = $this->getSession()->getPage()->getContent();
    $this->assertContainsTimes($pageContent, 'test-class-extra-section', 1);
    $this->assertContainsTimes($pageContent, 'test-class-section', 1);
    $this->assertContainsTimes($pageContent, 'test-class-extra-region', 1);
    $this->assertContainsTimes($pageContent, 'test-class-region', 1);

    // Add a section with multiple regions and add classes to it.
    $this->drupalGet('/admin/structure/types/manage/page/display/default/layout');
    $page->clickLink('Add section');
    $page->clickLink('Two column');
    $page->fillField('ui_styles[section][wrapper][_ui_styles_extra]', 'test-class-extra-2-cols-section');
    $page->selectFieldOption('ui_styles[section][wrapper][ui_styles_test_class]', 'test-class-section-2-col');
    $page->fillField('ui_styles[regions][first][wrapper][_ui_styles_extra]', 'test-class-extra-first');
    $page->selectFieldOption('ui_styles[regions][first][wrapper][ui_styles_test_class]', 'test-class-region-first');
    $page->fillField('ui_styles[regions][second][wrapper][_ui_styles_extra]', 'test-class-extra-second');
    $page->selectFieldOption('ui_styles[regions][second][wrapper][ui_styles_test_class]', 'test-class-region-second');
    $page->pressButton('Add section');
    $page->pressButton('Save layout');

    $this->drupalGet('node/' . $this->node->id());
    $pageContent = $this->getSession()->getPage()->getContent();
    $this->assertContainsTimes($pageContent, 'test-class-extra-2-cols-section', 1);
    $this->assertContainsTimes($pageContent, 'test-class-section-2-col', 1);
    $this->assertContainsTimes($pageContent, 'test-class-extra-first', 1);
    $this->assertContainsTimes($pageContent, 'test-class-region-first', 1);
    $this->assertContainsTimes($pageContent, 'test-class-extra-second', 1);
    $this->assertContainsTimes($pageContent, 'test-class-region-second', 1);
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
    $assert_session->responseNotContains('test-class-extra-section');
    $assert_session->responseNotContains('test-class-section');
    $assert_session->responseNotContains('test-class-extra-region');
    $assert_session->responseNotContains('test-class-region');

    $this->drupalGet('node/' . $this->node->id() . '/layout');
    // Add a style on section.
    $page->clickLink('Configure Section 1');

    $page->fillField('ui_styles[section][wrapper][_ui_styles_extra]', 'test-class-extra-section');
    $page->selectFieldOption('ui_styles[section][wrapper][ui_styles_test_class]', 'test-class-section');
    $page->fillField('ui_styles[regions][content][wrapper][_ui_styles_extra]', 'test-class-extra-region');
    $page->selectFieldOption('ui_styles[regions][content][wrapper][ui_styles_test_class]', 'test-class-region');

    $page->pressButton('Update');
    $page->pressButton('Save layout');

    $this->drupalGet('node/' . $this->node->id());
    $pageContent = $this->getSession()->getPage()->getContent();
    $this->assertContainsTimes($pageContent, 'test-class-extra-section', 1);
    $this->assertContainsTimes($pageContent, 'test-class-section', 1);
    $this->assertContainsTimes($pageContent, 'test-class-extra-region', 1);
    $this->assertContainsTimes($pageContent, 'test-class-region', 1);

    // Add a section with multiple regions and add classes to it.
    $this->drupalGet('node/' . $this->node->id() . '/layout');
    $page->clickLink('Add section');
    $page->clickLink('Two column');
    $page->fillField('ui_styles[section][wrapper][_ui_styles_extra]', 'test-class-extra-2-cols-section');
    $page->selectFieldOption('ui_styles[section][wrapper][ui_styles_test_class]', 'test-class-section-2-col');
    $page->fillField('ui_styles[regions][first][wrapper][_ui_styles_extra]', 'test-class-extra-first');
    $page->selectFieldOption('ui_styles[regions][first][wrapper][ui_styles_test_class]', 'test-class-region-first');
    $page->fillField('ui_styles[regions][second][wrapper][_ui_styles_extra]', 'test-class-extra-second');
    $page->selectFieldOption('ui_styles[regions][second][wrapper][ui_styles_test_class]', 'test-class-region-second');
    $page->pressButton('Add section');
    $page->pressButton('Save layout');

    $this->drupalGet('node/' . $this->node->id());
    $pageContent = $this->getSession()->getPage()->getContent();
    $this->assertContainsTimes($pageContent, 'test-class-extra-2-cols-section', 1);
    $this->assertContainsTimes($pageContent, 'test-class-section-2-col', 1);
    $this->assertContainsTimes($pageContent, 'test-class-extra-first', 1);
    $this->assertContainsTimes($pageContent, 'test-class-region-first', 1);
    $this->assertContainsTimes($pageContent, 'test-class-extra-second', 1);
    $this->assertContainsTimes($pageContent, 'test-class-region-second', 1);
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
    $page = $this->getSession()->getPage();

    // Title block.
    $page->clickLink('Add block in Section 1');
    $page->clickLink('Title');
    $page->checkField('edit-settings-label-display');
    $page->fillField('ui_styles_wrapper[wrapper][_ui_styles_extra]', 'test-class-title-block-extra-wrapper');
    $page->selectFieldOption('ui_styles_wrapper[wrapper][ui_styles_test_class]', 'test-class-title-block-wrapper');
    $page->fillField('ui_styles_title[wrapper][_ui_styles_extra]', 'test-class-title-block-extra-title');
    $page->selectFieldOption('ui_styles_title[wrapper][ui_styles_test_class]', 'test-class-title-block-title');
    $page->fillField('ui_styles[wrapper][_ui_styles_extra]', 'test-class-title-block-extra-content');
    $page->selectFieldOption('ui_styles[wrapper][ui_styles_test_class]', 'test-class-title-block-content');
    $page->pressButton('Add block');

    // Body field block.
    $page->clickLink('Add block in Section 1');
    $page->clickLink('Body');
    $page->checkField('edit-settings-label-display');
    $page->fillField('ui_styles_wrapper[wrapper][_ui_styles_extra]', 'test-class-body-block-extra-wrapper');
    $page->selectFieldOption('ui_styles_wrapper[wrapper][ui_styles_test_class]', 'test-class-body-block-wrapper');
    $page->fillField('ui_styles_title[wrapper][_ui_styles_extra]', 'test-class-body-block-extra-title');
    $page->selectFieldOption('ui_styles_title[wrapper][ui_styles_test_class]', 'test-class-body-block-title');
    $page->fillField('ui_styles[wrapper][_ui_styles_extra]', 'test-class-body-block-extra-content');
    $page->selectFieldOption('ui_styles[wrapper][ui_styles_test_class]', 'test-class-body-block-content');
    $page->pressButton('Add block');

    // Block content block.
    $page->clickLink('Add block in Section 1');
    $page->clickLink('My block content');
    $page->checkField('edit-settings-label-display');
    $page->fillField('ui_styles_wrapper[wrapper][_ui_styles_extra]', 'test-class-block-content-entity-block-extra-wrapper');
    $page->selectFieldOption('ui_styles_wrapper[wrapper][ui_styles_test_class]', 'test-class-block-content-entity-block-wrapper');
    $page->fillField('ui_styles_title[wrapper][_ui_styles_extra]', 'test-class-block-content-entity-block-extra-title');
    $page->selectFieldOption('ui_styles_title[wrapper][ui_styles_test_class]', 'test-class-block-content-entity-block-title');
    $page->fillField('ui_styles[wrapper][_ui_styles_extra]', 'test-class-block-content-entity-block-extra-content');
    $page->selectFieldOption('ui_styles[wrapper][ui_styles_test_class]', 'test-class-block-content-entity-block-content');
    $page->pressButton('Add block');

    $page->pressButton('Save layout');

    $this->drupalGet('node/' . $this->node->id());
    $pageContent = $this->getSession()->getPage()->getContent();
    foreach ($this->blockClasses as $class) {
      $this->assertContainsTimes($pageContent, $class, 1);
    }
  }

}
