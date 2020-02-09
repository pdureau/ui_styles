<?php

namespace Drupal\layout_builder_classes\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Swap out Layout Builder's AddBlock and UpdateBlock
    // forms with out own versions which add getters for some properties
    // that we need access to in our form alter.
    // @see https://drupal.org/i/3023334
    // @see https://drupal.org/i/3044117
    $addBlockRoute = $collection->get('layout_builder.add_block');
    if ($addBlockRoute) {
      $addBlockRoute->setDefault('_form', '\Drupal\layout_builder_classes\Form\AddBlockForm');
    }
    $updateBlockRoute = $collection->get('layout_builder.update_block');
    if ($updateBlockRoute) {
      $updateBlockRoute->setDefault('_form', '\Drupal\layout_builder_classes\Form\UpdateBlockForm');
    }
    $configureSectionRoute = $collection->get('layout_builder.configure_section');
    if ($configureSectionRoute) {
      $configureSectionRoute->setDefault('_form', '\Drupal\layout_builder_classes\Form\ConfigureSectionForm');
    }
  }

}
