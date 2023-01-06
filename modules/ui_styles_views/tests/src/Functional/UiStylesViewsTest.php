<?php

declare(strict_types = 1);

namespace Drupal\Tests\ui_styles_views\Functional;

use Drupal\Core\Url;
use Drupal\Tests\views\Functional\ViewTestBase;

/**
 * Test the UI Styles Views module.
 *
 * @group ui_styles
 * @group ui_styles_views
 */
class UiStylesViewsTest extends ViewTestBase {

  /**
   * Default theme.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'ui_styles_views',
    'ui_styles_views_test_config',
  ];

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = [
    'ui_styles_views_test',
  ];

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
   */
  protected function setUp($import_test_views = TRUE, $modules = ['ui_styles_views_test_config']): void {
    parent::setUp($import_test_views, $modules);

    // To ensure the pager appears.
    $this->createNode([
      'type' => 'page',
      'title' => 'Node 1',
    ]);
    $this->createNode([
      'type' => 'page',
      'title' => 'Node 2',
    ]);
  }

  /**
   * Tests classes added to views sections.
   */
  public function testViewsSections(): void {
    $assert_session = $this->assertSession();

    $this->drupalGet(Url::fromRoute('view.ui_styles_views_test.page_1'));
    $assert_session->responseContains('my-exposed-form-class');
    $assert_session->responseContains('my-style-class');
    $assert_session->responseContains('my-pager-class');
  }

}
