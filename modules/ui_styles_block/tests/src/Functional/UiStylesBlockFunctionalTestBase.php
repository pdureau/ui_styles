<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles_block\Functional;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\UserInterface;

/**
 * Provides common methods for UI Styles Block functional tests.
 */
abstract class UiStylesBlockFunctionalTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ui_styles',
    'ui_styles_block',
    'ui_styles_block_test',
  ];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

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
    $this->entityTypeManager = $this->container->get('entity_type.manager');

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
      'administer blocks',
    ];
  }

}
