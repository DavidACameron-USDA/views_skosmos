<?php

namespace Drupal\views_skosmos;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of SkosmosHost entities.
 */
class SkosmosHostListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Host');
    $header['uri'] = $this->t('URI');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['uri'] = $entity->getUri();
    return $row + parent::buildRow($entity);
  }

}

