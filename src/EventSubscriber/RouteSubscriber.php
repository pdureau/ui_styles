<?php

namespace Drupal\ui_styles\EventSubscriber;

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
    // Needed until https://www.drupal.org/i/3044117 is in.
    $configureSectionRoute = $collection->get('layout_builder.configure_section');
    if ($configureSectionRoute) {
      $configureSectionRoute->setDefault('_form', '\Drupal\ui_styles\Form\ConfigureSectionForm');
    }
  }

}
