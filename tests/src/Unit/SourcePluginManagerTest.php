<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_styles\Unit;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\ui_styles\Source\SourcePluginManager;
use Drupal\ui_styles_test\DummySourcePluginManager;

/**
 * Test the source plugin manager.
 *
 * @group ui_styles
 *
 * @coversDefaultClass \Drupal\ui_styles\Source\SourcePluginManager
 */
class SourcePluginManagerTest extends UnitTestCase {

  /**
   * The source plugin manager.
   *
   * @var \Drupal\ui_styles_test\DummySourcePluginManager
   */
  protected DummySourcePluginManager $sourcePluginManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    /** @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject $moduleHandler */
    $moduleHandler = $this->createMock(ModuleHandlerInterface::class);
    $moduleHandler->expects($this->any())
      ->method('getModuleDirectories')
      ->willReturn([]);

    $cache = $this->createMock(CacheBackendInterface::class);

    $namespaces = new \ArrayObject([]);

    $this->sourcePluginManager = new DummySourcePluginManager($namespaces, $cache, $moduleHandler);
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor(): void {
    $this->assertInstanceOf(
      SourcePluginManager::class,
      $this->sourcePluginManager
    );
  }

  /**
   * @covers ::getSortedDefinitions
   */
  public function testGetSortedDefinitions(): void {
    $this->sourcePluginManager->setSources([
      'id_b1' => [
        'weight' => 1,
        'id' => 'id_b1',
      ],
      'id_a1' => [
        'weight' => 1,
        'id' => 'id_a1',
      ],
      'id_b0' => [
        'weight' => 0,
        'id' => 'id_b0',
      ],
      'id_a0' => [
        'weight' => 0,
        'id' => 'id_a0',
      ],
    ]);

    $expected = [
      'id_a0',
      'id_b0',
      'id_a1',
      'id_b1',
    ];

    $sorted_definitions = $this->sourcePluginManager->getSortedDefinitions();
    $this->assertEquals($expected, \array_keys($sorted_definitions));
  }

}
