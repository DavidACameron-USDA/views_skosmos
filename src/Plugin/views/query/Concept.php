<?php

namespace Drupal\views_skosmos\Plugin\views\query;

use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use SkosmosClient\ApiException;
use SkosmosClient\Model\RdfGraph;

/**
 * Query plugin which wraps calls to a Skosmos API /{vocid}/index endpoint.
 *
 * @ViewsQuery(
 *   id = "views_skosmos_concept",
 *   title = @Translation("Skosmos /{vocid}/data"),
 *   help = @Translation("Query a Skosmos /{vocid}/data endpoint.")
 * )
 */
class Concept extends SkosmosQueryPluginBase {

  /**
   * Default function arguments for the vocidDataGet method.
   *
   * @var mixed[]
   */
  const DEFAULT_ARGUMENTS = [
    'vocid' => NULL,
    'format' => NULL,
    'uri' => NULL,
    'lang' => NULL,
  ];

  /**
   * {@inheritdoc}
   */
  public function build(ViewExecutable $view) {
    $view->build_info['query'] = $this->query();
  }

  /**
   * {@inheritdoc}
   */
  public function query($get_count = FALSE) {
    // SQL-based queries would actually build a query object here. Instead,
    // we'll build the arguments for the client function.
    $args = self::DEFAULT_ARGUMENTS;
    if (isset($this->where)) {
      foreach ($this->where as $group) {
        foreach ($group['conditions'] as $condition ) {
          // Remove periods from the beginning of field names.
          $field_name = ltrim($condition['field'], '.');
          if ($field_name == 'lang') {
            // @todo This is a temporary fix so that a single-language filter
            // doesn't have to be developed right now.
            $condition['value'] = $condition['value'][0] == '***LANGUAGE_language_interface***' ? \Drupal::languageManager()->getCurrentLanguage()->getId() : $condition['value'][0];
          }
          if (array_key_exists($field_name, $args)) {
            $args[$field_name] = $condition['value'];
          }
        }
      }
    }

    return $args;
  }

  /**
   * {@inheritdoc}
   */
  public function execute(ViewExecutable $view) {
    $view->result = [];
    $view->total_rows = 0;
    $view->execute_time = 0;

    if (empty($view->build_info['query']['vocid'])) {
      $this->messenger()->addError("The vocabulary ID filter must be configured in the view before a query will be executed.");
      return;
    }

    // Don't query without a URI being set.
    if (empty($view->build_info['query']['uri'])) {
      return;
    }

    $start = microtime(TRUE);
    $view->result = $this->getRows($view->build_info['query']);
    $view->execute_time = microtime(TRUE) - $start;
    $view->total_rows = count($view->result);
  }

  /**
   * Returns an array of results from the query.
   *
   * @param mixed[] $args
   *   Arguments for the query function that will be executed.
   *
   * @return ResultRow[]
   *   The array of results.
   */
  protected function getRows($args): array {
    $rows = [];
    $index = 0;

    $result = $this->executeQuery($args);
    try {
      $concept = $result->getConcept();
    }
    catch (\Exception $e) {
      return [];
    }

    $row = [];
    $row['uri'] = $concept->getUri();
    $row['created'] = strtotime($concept->getCreated());
    $row['modified'] = strtotime($concept->getModified());
    $row['pref_label'] = $concept->getPrefLabel();
    $row['alt_label'] = $concept->getAltLabels();
    $row['definition'] = $concept->getDefinition();
    $row['definition_source'] = $concept->getDefinitionSource();
    $row['scheme'] = $concept->getScheme();
    $row['broader'] = $concept->getBroader();
    $row['narrower'] = $concept->getNarrower();
    $row['related'] = $concept->getRelated();
    $row['broad_match'] = $concept->getBroadMatch();
    $row['narrow_match'] = $concept->getNarrowMatch();
    $row['related_match'] = $concept->getRelatedMatch();
    $row['close_match'] = $concept->getCloseMatch();
    $row['exact_match'] = $concept->getExactMatch();
    $row['index'] = $index++;

    $rows[] = new ResultRow($row);

    return $rows;
  }

  /**
   * Queries the API's /{vocid}/data endpoint.
   *
   * @param mixed[] $args
   *   Arguments for the query function that will be executed.
   *
   * @return \SkosmosClient\Model\RdfGraph
   *   The results of the query.
   */
  protected function executeQuery($args): RdfGraph {
    // Arrays with string keys cannot be unpacked, so remove them.
    $args = array_values($args);
    try {
      $results = $this->getVocabClient()->vocidDataGet(...$args);
    }
    catch (ApiException $e) {
      // Return an empty RdfGraph object.
      return new RdfGraph();
    }
    return $results;
  }

}

