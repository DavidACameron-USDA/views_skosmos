<?php

namespace Drupal\views_skosmos\Plugin\views\query;

use Drupal\Core\Messenger\MessengerTrait;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\views_skosmos\ClientFactory;
use Drupal\views_skosmos\ViewsHelperTrait;
use SkosmosClient\Api\ConceptSpecificMethodsApi;
use SkosmosClient\Api\GlobalMethodsApi;
use SkosmosClient\Api\VocabularySpecificMethodsApi;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for Skosmos API queries with Views.
 */
abstract class SkosmosQueryPluginBase extends QueryPluginBase {

  use ViewsHelperTrait;
  use MessengerTrait;

  /**
   * The views_skosmos.client_factory service.
   *
   * @var \Drupal\views_skosmos\ClientFactory
   */
  protected $clientFactory;

  /**
   * A Skosmos API concept-specific methods client.
   *
   * @var \SkosmosClient\Api\ConceptSpecificMethodsApi
   */
  protected $conceptClient;

  /**
   * A Skosmos API global methods client.
   *
   * @var \SkosmosClient\Api\GlobalMethodsApi
   */
  protected $globalClient;

  /**
   * A Skosmos API vocabulary-specific methods client.
   *
   * @var \SkosmosClient\Api\VocabularySpecificMethodsApi
   */
  protected $vocabClient;

  /**
   * Constructs a Vocabularies object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\views_skosmos\ClientFactory $client_factory
   *   The views_skosmos.client_factory service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClientFactory $client_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->clientFactory = $client_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('views_skosmos.client_factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $uri = static::getHostUriFromView($view);
    $this->clientFactory->setUri($uri);
  }

  /**
   * Initializes the conceptClient and returns it.
   *
   * @return \SkosmosClient\Api\ConceptSpecificMethodsApi
   *   The Skosmos ConceptSpecificMethodsApi client.
   */
  protected function getConceptClient(): ConceptSpecificMethodsApi {
    if (!isset($this->conceptClient)) {
      $this->conceptClient = $this->clientFactory->getConceptClient();
    }
    return $this->conceptClient;
  }

  /**
   * Initializes the globalClient and returns it.
   *
   * @return \SkosmosClient\Api\GlobalMethodsApi
   *   The Skosmos GlobalMethodsApi client.
   */
  protected function getGlobalClient(): GlobalMethodsApi {
    if (!isset($this->globalClient)) {
      $this->globalClient = $this->clientFactory->getGlobalClient();
    }
    return $this->globalClient;
  }

  /**
   * Initializes the vocabClient and returns it.
   *
   * @return \SkosmosClient\Api\VocabularySpecificMethodsApi
   *   The Skosmos VocabularySpecificMethodsApi client.
   */
  protected function getVocabClient(): VocabularySpecificMethodsApi {
    if (!isset($this->vocabClient)) {
      $this->vocabClient = $this->clientFactory->getVocabClient();
    }
    return $this->vocabClient;
  }

  /**
   * {@inheritdoc}
   */
  public function addWhere($group, $field, $value = NULL, $operator = NULL) {
    // Ensure all variants of 0 are actually 0. Thus '', 0 and NULL are all
    // the default group.
    if (empty($group)) {
      $group = 0;
    }
    // Check for a group.
    if (!isset($this->where[$group])) {
      $this->setWhereGroup('AND', $group);
    }
    $this->where[$group]['conditions'][] = [
      'field' => $field,
      'value' => $value,
      // @todo Do we need the operator since we aren't working with SQL?
      'operator' => $operator,
    ];
  }

  /**
   * The following functions must be added to the plugin or it doesn't work
   * properly. They fulfill Views's expectations that it is working with a
   * SQL-based database, but aren't necessary for communicating with an API.
   *
   * addOrderBy() is analogous to addWhere() for adding sorts to the view.  It
   * isn't strictly necessary to have, but if a view happens to have a sort in
   * its configuration - which they always do by default - then it will be
   * broken if addOrderBy() isn't in the query plugin.
   */

  /**
   * {@inheritdoc}
   */
  public function ensureTable($table, $relationship = NULL) {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function addField($table, $field, $alias = '', $params = array()) {
    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function addOrderBy($table, $field = NULL, $order = 'ASC', $alias = '', $params = []) {

  }

}

