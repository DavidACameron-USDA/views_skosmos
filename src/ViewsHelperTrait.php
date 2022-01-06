<?php

namespace Drupal\views_skosmos;

use Drupal\views\ViewExecutable;
use Drupal\views_skosmos\Entity\SkosmosHost;

trait ViewsHelperTrait {

  /**
   * Loads the host that is being queried by a view.
   *
   * @param ViewsExecutable $view
   *   The view.
   * @param string $table_prefix
   *   The prefix of a views_skosmos base table. For example, the /search
   *   endpoint's base table is prefixed by 'skosmos_search_'.
   *
   * @return \Drupal\views_skosmos\SkosmosHostInterface|null
   *   The requested host, or NULL if it could not be found and loaded.
   */
  public static function getHostFromView(ViewExecutable $view, string $table_prefix) {
    $table = $view->storage->get('base_table');
    $length = strlen($table_prefix);
    if (substr($table, 0, $length) == $table_prefix) {
      $index_id = substr($table, $length);
      return SkosmosHost::load($index_id);
    }
    return NULL;
  }

}

