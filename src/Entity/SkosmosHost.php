<?php

namespace Drupal\views_skosmos\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the SkosmosHost entity.
 *
 * @ConfigEntityType(
 *   id = "skosmos_host",
 *   label = @Translation("Skosmos API Host"),
 *   label_singular = @Translation("host"),
 *   label_plural = @Translation("hosts"),
 *   label_count = @PluralTranslation(
 *     singular = "@count host",
 *     plural = "@count hosts"
 *   ),
 *   label_collection = @Translation("Skosmos API Hosts"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\views_skosmos\SkosmosHostListBuilder",
 *     "form" = {
 *       "add" = "Drupal\views_skosmos\Form\SkosmosHostForm",
 *       "edit" = "Drupal\views_skosmos\Form\SkosmosHostForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "skosmos_host",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/services/skosmos/{skosmos_host}",
 *     "add-form" = "/admin/config/services/skosmos/add",
 *     "edit-form" = "/admin/config/services/skosmos/{skosmos_host}/edit",
 *     "delete-form" = "/admin/config/services/skosmos/{skosmos_host}/delete",
 *     "collection" = "/admin/config/services/skosmos"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "uri"
 *   }
 * )
 */
class SkosmosHost extends ConfigEntityBase implements SkosmosHostInterface {

  /**
   * The Host ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Host label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Host URI.
   *
   * @var string
   */
  protected $uri;

  /**
   * {@inheritdoc}
   */
  public function setUri(string $uri) {
    $this->uri = $uri;
  }

  /**
   * {@inheritdoc}
   */
  public function getUri(): string {
    return $this->uri ?? '';
  }

}

