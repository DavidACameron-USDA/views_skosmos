<?php

namespace Drupal\views_skosmos\Plugin\views\query;

use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use SkosmosClient\ApiException;
use SkosmosClient\Label;
use SkosmosClient\Model\BroaderTransitiveResult;

/**
 * Query plugin which wraps calls to a Skosmos /{vocid}/broaderTransitive
 * endpoint.
 *
 * @ViewsQuery(
 *   id = "views_skosmos_concept_breadcrumbs",
 *   title = @Translation("Concept Breadcrumbs"),
 *   help = @Translation("Builds Concept breadcrumbs from broaderTransitive data.")
 * )
 */
class ConceptBreadcrumbs extends SkosmosQueryPluginBase {

  /**
   * Default function arguments for the vocidBroaderTransitiveGet method.
   *
   * @var mixed[]
   */
  const DEFAULT_ARGUMENTS = [
    'vocid' => NULL,
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

    $results = $this->executeQuery($args);

    foreach ($this->buildBreadcrumbs($results, $args['uri']) as $trail) {
      $row = [];
      $row['breadcrumbs'] = $trail;
      $row['index'] = $index++;

      $rows[] = new ResultRow($row);
    }

    return $rows;
  }

  /**
   * Queries the API's /{vocid}/broaderTransitive endpoint.
   *
   * @param mixed[] $args
   *   Arguments for the query function that will be executed.
   *
   * @return \SkosmosClient\Model\BroaderTransitiveResult
   *   The results of the query.
   */
  protected function executeQuery($args): BroaderTransitiveResult {
    // Arrays with string keys cannot be unpacked, so remove them.
    $args = array_values($args);
    try {
      $results = $this->getConceptClient()->vocidBroaderTransitiveGet(...$args);
    }
    catch (ApiException $e) {
      // Return an empty BroaderTransitiveResult object.
      return new BroaderTransitiveResult();
    }
    return $results;
  }

  /**
   * Builds the raw results from the API into breadcrumb trail rows.
   *
   * @param \SkosmosClient\Model\BroaderTransitiveResult $results
   *   The raw results from the API.
   * @param string $uri
   *   The URI of the concept being viewed.
   *
   * @return \SkosmosClient\Label[][]
   *   An array of breadcrumb trails, each of which is an array of Label
   *   objects.
   */
  protected function buildBreadcrumbs(BroaderTransitiveResult $results, string $uri) {
    $formatted_results = [];
    foreach ($results->getBroaderTransitive() as $broader) {
      $formatted_results[$broader->getUri()] = [
        'direct' => $broader->getBroader(),
        'label' => $broader->getPrefLabel(),
      ];
    }
    return $this->getTrails($formatted_results, $uri);
  }

  /**
   * Recursive function for organizing the breadcrumb trails for the view.
   *
   * Copied and adapted from https://github.com/NatLibFi/Skosmos/model/Vocabulary.php#L602
   *
   * @param array $bTresult
   *   Contains the results of the broaderTransitive query.
   * @param string $uri
   * @param array $path
   *
   * @return \SkosmosClient\Label[][]
   *   An array of breadcrumb trails, each of which is an array of Label
   *   objects.
   */
  protected function getTrails($bTresult, $uri, $path = null)
  {
    $crumbs = array();
    if (!isset($path)) {
      $path = array();
    }

    // check that there is no cycle (issue #220)
    foreach ($path as $childcrumb) {
      if ($childcrumb->getUri() == $uri) {
        // found a cycle - short-circuit and stop
        return $crumbs;
      }
    }
    if (isset($bTresult[$uri]['direct'])) {
      foreach ($bTresult[$uri]['direct'] as $broaderUri) {
        $newpath = array_merge($path, array(new Label($uri, $bTresult[$uri]['label'])));
        if ($uri !== $broaderUri) {
          $crumbs = array_merge($crumbs, $this->getTrails($bTresult, $broaderUri, $newpath));
        }
      }
    } else { // we have reached the end of a path and we need to start a new row in the 'stack'
      if (isset($bTresult[$uri])) {
        $path = array_merge($path, array(new Label($uri, $bTresult[$uri]['label'])));
      }
      $crumbs[] = array_reverse($path);
    }
    return $crumbs;
  }

}

