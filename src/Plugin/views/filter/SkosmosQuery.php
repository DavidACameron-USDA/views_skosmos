<?php

namespace Drupal\views_skosmos\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\StringFilter;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;

/**
 * Filters Skosmos API calls by keyword search.
 *
 * @ViewsFilter("views_skosmos_query")
 */
class SkosmosQuery extends StringFilter {

  /**
   * {@inheritdoc}
   */
  public function operatorOptions($which = 'title') {
    return [
      'contains' => t('Contains'),
      'starts' => t('Starts with'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function opContains($field) {
    $operator = $this->getConditionOperator('LIKE');
    $this->query->addWhere($this->options['group'], $field, '*' . $this->value . '*', $operator);
  }

  /**
   * {@inheritdoc}
   */
  protected function opStartsWith($field) {
    $operator = $this->getConditionOperator('LIKE');
    $this->query->addWhere($this->options['group'], $field, $this->value . '*', $operator);
  }

}

