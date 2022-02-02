<?php

namespace Drupal\views_skosmos;

use Drupal\views\ViewExecutable;
use Drupal\views\Views;
use Drupal\views_skosmos\Entity\SkosmosHost;

trait ViewsHelperTrait {

  /**
   * Loads the host URI that is being queried by a view.
   *
   * @param ViewsExecutable $view
   *   The view.
   *
   * @return string
   *   The host URI.
   */
  public static function getHostUriFromView(ViewExecutable $view) {
    $base_table = array_keys($view->getBaseTables())[0];
    $table_data = Views::viewsData()->get($base_table);
    return $table_data['table']['base']['host_uri'];
  }

}

