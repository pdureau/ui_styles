<?php

namespace Drupal\ui_styles_layout_builder\EventSubscriber;

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
    $configSectionRoute = $collection->get('layout_builder.configure_section');
    if ($configSectionRoute) {
      $configSectionRoute->setDefault('_form', '\Drupal\ui_styles_layout_builder\Form\ConfigureSectionForm');
    }
  }

}
