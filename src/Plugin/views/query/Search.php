<?php

namespace Drupal\views_skosmos\Plugin\views\query;

use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use SkosmosClient\ApiException;
use SkosmosClient\Model\SearchResults;

/**
 * Query plugin which searches a SKOS Vocabulary.
 *
 * @ViewsQuery(
 *   id = "views_skosmos_search",
 *   title = @Translation("Skosmos Search"),
 *   help = @Translation("Search a SKOS Vocabulary")
 * )
 */
class Search extends SkosmosQueryPluginBase {

  /**
   * Default function arguments for the searchGet method.
   *
   * @var mixed[]
   */
  const DEFAULT_ARGUMENTS = [
    'query' => '',
    'lang' => NULL,
    'labellang' => NULL,
    'vocid' => NULL,
    'type' => NULL,
    'parent' => NULL,
    'group' => NULL,
    'maxhits' => NULL,
    'offset' => NULL,
    'fields' => NULL,
    'unique' => NULL,
  ];

  /**
   * {@inheritdoc}
   */
  public function build(ViewExecutable $view) {
    $view->initPager();
    $view->pager->query();

    $view->build_info['query'] = $this->query();
    $view->build_info['count_query'] = $this->query(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function query($get_count = FALSE) {
    // SQL-based queries would actually build a query object here. Instead,
    // we'll build the arguments for the client function.
    $args = self::DEFAULT_ARGUMENTS;
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

    if (!$get_count) {
      if (!empty($this->limit)) {
        $args['maxhits'] = $this->limit;
      }
      if (!empty($this->offset)) {
        $args['offset'] = $this->offset;
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

    // 'query' is a required parameter to this endpoint. If it's missing a 400
    // error is returned. There's no need to hit the endpoint in this case and
    // we don't want to log unnecessary exceptions. So just fail silently.
    if (empty($view->build_info['query']['query'])) {
      return;
    }

    // Execute the count query which returns all items so Views can set up the
    // pager.
    $view->pager->total_items = $this->getRowCount($view->build_info['count_query']);
    if (!empty($view->pager->options['offset'])) {
      $view->pager->total_items -= $view->pager->options['offset'];
    }
    $view->total_rows = $view->pager->total_items;

    $view->pager->preExecute($this->query);

    // Execute the actual query with the limit and offset to get the records
    // that will be displayed on the page.
    $start = microtime(TRUE);
    $view->result = $this->getRows($view->build_info['query']);
    $view->execute_time = microtime(TRUE) - $start;

    $view->pager->postExecute($view->result);
    $view->pager->updatePageInfo();
  }

  /**
   * Returns the number of rows in the results.
   *
   * @param mixed[] $args
   *   Arguments for the query function that will be executed.
   *
   * @return int
   *   The number of results returned by the query.
   */
  protected function getRowCount($args): int {
    $results = $this->executeQuery($args);
    return count($results->getResults());
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
    /** @var \SkosmosClient\Model\SearchResult $result */
    foreach ($results->getResults() as $result) {
      $row = [];
      $row['uri'] = $result->getUri();
      $row['type'] = $result->getType();
      $row['pref_label'] = $result->getPrefLabel();
      $row['alt_label'] = $result->getAltLabel();
      $row['hidden_label'] = $result->getHiddenLabel();
      $row['lang'] = $result->getLang();
      $row['vocid'] = $result->getVocab();
      $row['exvocab'] = $result->getExvocab();
      $row['notation'] = $result->getNotation();
      $row['index'] = $index++;

      $rows[] = new ResultRow($row);
    }

    return $rows;
  }

  /**
   * Queries the API's /search endpoint.
   *
   * @param mixed[] $args
   *   Arguments for the query function that will be executed.
   *
   * @return SearchResults
   *   The results of the query.
   */
  protected function executeQuery($args): SearchResults {
    // Arrays with string keys cannot be unpacked, so remove them.
    $args = array_values($args);
    try {
      /** @var \SkosmosClient\Model\SearchResults $results */
      $results = $this->getGlobalClient()->searchGet(...$args);
    }
    catch (ApiException $e) {
      $this->messenger()->addError($e->getMessage());
      // Return an empty SearchResults object.
      return new SearchResults();
    }
    return $results;
  }

}

