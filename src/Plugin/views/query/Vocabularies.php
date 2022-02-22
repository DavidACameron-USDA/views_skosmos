<?php

namespace Drupal\views_skosmos\Plugin\views\query;

use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use SkosmosClient\ApiException;
use SkosmosClient\Model\VocabularyList;

/**
 * Query plugin which lists vocabularies served by a Skosmos API.
 *
 * @ViewsQuery(
 *   id = "views_skosmos_vocabularies",
 *   title = @Translation("Vocabularies"),
 *   help = @Translation("List vocabularies served by a Skosmos API")
 * )
 */
class Vocabularies extends SkosmosQueryPluginBase {

  /**
   * Default function arguments for the vocidIndexLetterGet method.
   *
   * @var mixed[]
   */
  const DEFAULT_ARGUMENTS = [
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

    if (empty($view->build_info['query']['lang'])) {
      $this->messenger()->addError("The language filter must be configured in the view's query settings before a query will be executed.");
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
    /** @var \SkosmosClient\Model\Vocabulary $result */
    foreach ($results->getVocabularies() as $result) {
      $row = [];
      $row['uri'] = $result->getUri();
      $row['id'] = $result->getId();
      $row['title'] = $result->getTitle();
      $row['index'] = $index++;

      $rows[] = new ResultRow($row);
    }

    return $rows;
  }

  /**
   * Queries the API's /vocabularies endpoint.
   *
   * @param mixed[] $args
   *   Arguments for the query function that will be executed.
   *
   * @return \SkosmosClient\Model\VocabularyList
   *   The results of the query.
   */
  protected function executeQuery($args): VocabularyList {
    // Arrays with string keys cannot be unpacked, so remove them.
    $args = array_values($args);
    try {
      $results = $this->getGlobalClient()->vocabulariesGet(...$args);
    }
    catch (ApiException $e) {
      $this->messenger()->addError($e->getMessage());
      // Return an empty Vocabulary object.
      return new VocabularyList();
    }
    return $results;
  }

}

