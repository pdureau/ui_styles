<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles_page\Functional;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ThemeInstallerInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\UserInterface;

/**
 * Provides common methods for UI Styles Page functional tests.
 */
abstract class UiStylesPageFunctionalTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'ui_styles_test_subsubtheme';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ui_styles',
    'ui_styles_page',
    'ui_styles_test',
  ];

  /**
   * List of themes to enable.
   *
   * @var array
   */
  protected array $themes = [
    'ui_styles_test_theme1',
    'ui_styles_test_theme2',
    'ui_styles_test_theme3',
    'ui_styles_test_subtheme',
    'ui_styles_test_subsubtheme',
  ];

  /**
   * List of themes default regions.
   *
   * @var array
   */
  protected array $defaultRegions = [
    'sidebar_first',
    'sidebar_second',
    'content',
    'header',
    'primary_menu',
    'secondary_menu',
    'footer',
    'highlighted',
    'help',
    'page_top',
    'page_bottom',
    'breadcrumb',
  ];

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeInstallerInterface
   */
  protected ThemeInstallerInterface $themeInstaller;

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
    $this->configFactory = $this->container->get('config.factory');
    $this->themeInstaller = $this->container->get('theme_installer');
    $this->themeInstaller->install($this->themes);
    \drupal_flush_all_caches();

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
      'administer themes',
    ];
  }

}
