<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles_library\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\UserInterface;

/**
 * UI Styles library functional tests.
 *
 * @group ui_styles
 * @group ui_styles_library
 */
class UiStylesLibraryTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ui_styles',
    'ui_styles_library',
    'ui_styles_library_test',
  ];

  /**
   * The admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected UserInterface $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $user = $this->drupalCreateUser($this->getAdminUserPermissions());
    if (!($user instanceof UserInterface)) {
      $this->fail('Impossible to create the tests user.');
    }

    $this->adminUser = $user;
  }

  /**
   * The list of admin user permissions.
   *
   * @return array
   *   The list of admin user permissions.
   */
  protected function getAdminUserPermissions(): array {
    return [
      'access_ui_styles_library',
    ];
  }

  /**
   * Test libraries page.
   */
  public function testDisplayLibraries(): void {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet(Url::fromRoute('ui_styles_library.overview'));
    $this->assertSession()->pageTextContains('Test');
    $this->assertSession()->pageTextContains('Test ui styles library.');
    $this->assertSession()->linkExists('External documentation');
    $this->assertSession()->linkByHrefExists('https://test.com');
    $this->assertSession()->linkExists('Example');
    $this->assertSession()->linkByHrefExists('https://example.com');
  }

}
