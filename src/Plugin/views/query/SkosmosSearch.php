<?php
namespace Drupal\views_skosmos\Plugin\views\query;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Drupal\views_skosmos\ClientFactory;
use Drupal\views_skosmos\ViewsHelperTrait;
use Drupal\views_skosmos\Entity\SkosmosHost;
use SkosmosClient\ApiException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Query plugin which wraps calls to a Skosmos API /search endpoint.
 *
 * @ViewsQuery(
 *   id = "views_skosmos_search",
 *   title = @Translation("Skosmos /search"),
 *   help = @Translation("Query a Skosmos /search endpoint.")
 * )
 */
class SkosmosSearch extends QueryPluginBase {

  use ViewsHelperTrait;

  /**
   * Default function arguments for the searchGet method.
   *
   * @var mixed[]
   */
  const DEFAULT_ARGUMENTS = [
    'query' => '',
    'lang' => NULL,
    'labellang' => NULL,
    'vocab' => NULL,
    'type' => NULL,
    'parent' => NULL,
    'group' => NULL,
    'maxhits' => NULL,
    'offset' => NULL,
    'fields' => NULL,
    'unique' => NULL,
  ];

  /**
   * The prefix of the base table of this query.
   *
   * @var string
   */
  const TABLE_PREFIX = 'skosmos_search_';

  /**
   * The views_skosmos.client_factory service.
   *
   * @var \Drupal\views_skosmos\ClientFactory
   */
  protected $clientFactory;

  /**
   * A Skosmos API global methods client.
   *
   * @var \SkosmosClient\Api\GlobalMethodsApi
   */
  protected $globalClient;

  /**
   * Constructs a SkosmosSearch object.
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

    $host = static::getHostFromView($view, self::TABLE_PREFIX);
    $this->globalClient = $this->clientFactory->getGlobalClient($host->getUri());
  }

  /**
   * {@inheritdoc}
   */
  public function execute(ViewExecutable $view) {
    $args = self::DEFAULT_ARGUMENTS;
    foreach ($this->where as $group) {
      foreach ($group['conditions'] as $condition ) {
        // Remove periods from the beginning of field names.
        $field_name = ltrim($condition['field'], '.');
        if (array_key_exists($field_name, $args)) {
          $args[$field_name] = $condition['value'];
        }
      }
    }

    $index = 0;
    // Arrays with string keys cannot be unpacked, so remove them.
    $args = array_values($args);
    try {
      /** @var \SkosmosClient\Model\SearchResults $results */
      $results = $this->globalClient->searchGet(...$args);
    }
    catch (ApiException $e) {
      // @todo Handle exceptions properly.
      return;
    }

    /** @var \SkosmosClient\Model\SearchResult $result */
    foreach ($results->getResults() as $result) {
      $row = [];
      $row['uri'] = $result->getUri();
      $row['type'] = $result->getType();
      $row['pref_label'] = $result->getPrefLabel();
      $row['alt_label'] = $result->getAltLabel();
      $row['hidden_label'] = $result->getHiddenLabel();
      $row['lang'] = $result->getLang();
      $row['vocab'] = $result->getVocab();
      $row['exvocab'] = $result->getExvocab();
      $row['notation'] = $result->getNotation();
      $row['index'] = $index++;

      $view->result[] = new ResultRow($row);
    }
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

