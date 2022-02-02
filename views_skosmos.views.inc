<?php

/**
 * @file
 * Views hook implementations for the Views Skosmos module.
 */

use Drupal\views_skosmos\Entity\SkosmosHost;

/**
 * Implements hook_views_data().
 *
 * For each host, we provide the following tables:
 * - A base table for the /search endpoint
 */
function views_skosmos_views_data() {
  $data = [];

  foreach (SkosmosHost::loadMultiple() as $host) {
    // Add the /search base table.
    $key = 'skosmos_search_' . $host->id();
    $table = &$data[$key];
    $table['table']['group'] = t('Skosmos @name', ['@name' => $host->label()]);
    $table['table']['base'] = [
      'title' => t('Skosmos @name /search', ['@name' => $host->label()]),
      'help' => t('Search concepts and collections by query term'),
      'query_id' => 'views_skosmos_search',
      'host_uri' => $host->getUri(),
    ];
    $table['uri'] = [
      'title' => t('URI'),
      'help' => t('The URI of a concept'),
      'field' => [
        'id' => 'standard',
      ],
    ];
    $table['type'] = [
      'title' => t('Type'),
      'help' => t('The type of data in the concept'),
      'field' => [
        'id' => 'views_skosmos_list',
      ],
    ];
    $table['pref_label'] = [
      'title' => t('Preferred Label'),
      'help' => t('The preferred label of the concept'),
      'field' => [
        'id' => 'standard',
      ],
    ];
    $table['alt_label'] = [
      'title' => t('Alternate Label'),
      'help' => t('The alternate label of the concept'),
      'field' => [
        'id' => 'standard',
      ],
    ];
    $table['hidden_label'] = [
      'title' => t('Hidden Label'),
      'help' => t('The hidden label of the concept'),
      'field' => [
        'id' => 'standard',
      ],
    ];
    $table['lang'] = [
      'title' => t('Language'),
      'help' => t('The language of the concept'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'language',
      ],
    ];
    $table['vocid'] = [
      'title' => t('Vocabulary'),
      'help' => t('A SKOS vocabulary ID'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'argument' => [
        'id' => 'standard',
      ],
    ];
    $table['exvocab'] = [
      'title' => t('Ex-vocabulary'),
      'help' => t('The former vocabulary in which the concept was located'),
      'field' => [
        'id' => 'standard',
      ],
    ];
    $table['notation'] = [
      'title' => t('Notation'),
      'help' => t('The notation of the concept'),
      'field' => [
        'id' => 'standard',
      ],
    ];
    $table['query'] = [
      'title' => t('Query'),
      'help' => t('The term to search for'),
      'filter' => [
        'id' => 'views_skosmos_query',
      ],
    ];
  }

  return $data;
}

