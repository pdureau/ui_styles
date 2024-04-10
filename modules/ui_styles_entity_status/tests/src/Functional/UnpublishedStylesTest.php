<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles_entity_status\Functional;

use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\ui_styles_entity_status\UiStylesEntityStatusInterface;

/**
 * Unpublished status styles functional tests.
 *
 * @group ui_styles
 * @group ui_styles_entity_status
 */
class UnpublishedStylesTest extends UiStylesEntityStatusFunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'layout_builder',
    'node',
  ];

  /**
   * Test if classes are set correctly.
   */
  public function testHtmlResult(): void {
    $this->drupalLogin($this->adminUser);
    $assert_session = $this->assertSession();
    $theme_settings = $this->config($this->defaultTheme . '.settings');
    $theme_settings->set(UiStylesEntityStatusInterface::UNPUBLISHED_CLASSES_THEME_SETTING_KEY, [
      'selected' => [
        'fake' => 'fake',
      ],
      'extra' => 'free-value',
    ]);
    $theme_settings->save();
    $selectors = [
      'article.fake.free-value',
      '.fake.free-value.layout.layout--onecol',
    ];

    // Create a content type.
    $this->drupalCreateContentType(
      [
        'type' => 'page',
        'name' => 'Basic page',
      ]
    );

    // Enable layout builder on content type.
    $layout_builder_view_display = LayoutBuilderEntityViewDisplay::load('node.page.default');
    if ($layout_builder_view_display != NULL) {
      $layout_builder_view_display->enableLayoutBuilder()
        ->setOverridable()
        ->save();
    }

    // Create a node.
    $node = $this->createNode();

    // Test with the node published.
    $this->drupalGet($node->toUrl());
    foreach ($selectors as $selector) {
      $assert_session->elementNotExists('css', $selector);
    }

    // Test with the node unpublished.
    $node->setUnpublished();
    $node->save();
    $this->drupalGet($node->toUrl());
    foreach ($selectors as $selector) {
      $assert_session->elementExists('css', $selector);
    }
  }

  /**
   * The list of admin user permissions.
   *
   * @return array
   *   The list of admin user permissions.
   */
  protected function getAdminUserPermissions(): array {
    return \array_merge([
      'bypass node access',
    ], parent::getAdminUserPermissions());
  }

}
