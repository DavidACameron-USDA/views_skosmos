<?php

namespace Drupal\views_skosmos\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining SkosmosHost entities.
 */
interface SkosmosHostInterface extends ConfigEntityInterface {

  /**
   * Sets the URI of the Skosmos API host.
   *
   * @param string $uri
   *   The URI.
   */
  public function setUri(string $uri);

  /**
   * Gets the URI of the Skosmos API host.
   *
   * @return string
   *   The URI.
   */
  public function getUri(): string;

}

