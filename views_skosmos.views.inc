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
    // Add the /vocabularies base table.
    $key = 'skosmos_vocabularies_' . $host->id();
    $table = &$data[$key];
    $table['table']['group'] = t('Skosmos @name', ['@name' => $host->label()]);
    $table['table']['base'] = [
      'title' => t('Skosmos @name /vocabularies', ['@name' => $host->label()]),
      'help' => t('List all vocabularies'),
      'query_id' => 'views_skosmos_vocabularies',
      'host_uri' => $host->getUri(),
    ];
    $table['uri'] = [
      'title' => t('URI'),
      'help' => t('The URI of a vocabulary'),
      'field' => [
        'id' => 'standard',
      ],
    ];
    $table['id'] = [
      'title' => t('ID'),
      'help' => t('The ID of a vocabulary'),
      'field' => [
        'id' => 'standard',
      ],
    ];
    $table['title'] = [
      'title' => t('Title'),
      'help' => t('The title of a vocabulary'),
      'field' => [
        'id' => 'standard',
      ],
    ];
    $table['lang'] = [
      'title' => t('Language'),
      'help' => t('The language of the vocabulary'),
      'filter' => [
        'id' => 'language',
      ],
    ];

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

    // Add the /{vocab}/data base table.
    $key = 'skosmos_concept_' . $host->id();
    $table = &$data[$key];
    $table['table']['group'] = t('Skosmos @name', ['@name' => $host->label()]);
    $table['table']['base'] = [
      'title' => t('Skosmos @name concept', ['@name' => $host->label()]),
      'help' => t('Concept metadata'),
      'query_id' => 'views_skosmos_concept',
      'host_uri' => $host->getUri(),
    ];
    $table['vocid'] = [
      'title' => t('Vocabulary'),
      'help' => t('A SKOS vocabulary ID'),
      'filter' => [
        'id' => 'string',
      ],
      'argument' => [
        'id' => 'standard',
      ],
    ];
    $table['uri'] = [
      'title' => t('URI'),
      'help' => t('The URI of the concept'),
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'concept_uri',
      ],
    ];
    $table['created'] = [
      'title' => t('Created'),
      'help' => t('The date the concept was created'),
      'field' => [
        'id' => 'date',
      ],
    ];
    $table['modified'] = [
      'title' => t('Modified'),
      'help' => t('The date the concept was last changed'),
      'field' => [
        'id' => 'date',
      ],
    ];
    $table['scheme'] = [
      'title' => t('Schemes'),
      'help' => t('The ConceptSchemes of which the Concept is a member'),
      'field' => [
        'id' => 'views_skosmos_label_list',
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
      'help' => t('The alternate labels of the concept'),
      'field' => [
        'id' => 'views_skosmos_list',
      ],
    ];
    $table['definition'] = [
      'title' => t('Definition'),
      'help' => t("The concept's definition"),
      'field' => [
        'id' => 'standard',
      ],
    ];
    $table['definition_source'] = [
      'title' => t('Definition Source'),
      'help' => t("The creator of the concept's definition"),
      'field' => [
        'id' => 'standard',
      ],
    ];
    $table['broader'] = [
      'title' => t('Broader Concept'),
      'help' => t('The broader concept in the hierarchy'),
      'field' => [
        'id' => 'views_skosmos_label_list',
      ],
    ];
    $table['narrower'] = [
      'title' => t('Narrower Concepts'),
      'help' => t('The narrower concepts in the hierarchy'),
      'field' => [
        'id' => 'views_skosmos_label_list',
      ],
    ];
    $table['related'] = [
      'title' => t('Related Concepts'),
      'help' => t('The related concepts'),
      'field' => [
        'id' => 'views_skosmos_label_list',
      ],
    ];
    $table['broad_match'] = [
      'title' => t('Broader Matches'),
      'help' => t('Broader concept URIs from other schemes'),
      'field' => [
        'id' => 'views_skosmos_label_list',
      ],
    ];
    $table['narrow_match'] = [
      'title' => t('Narrower Matches'),
      'help' => t('Narrower concept URIs from other schemes'),
      'field' => [
        'id' => 'views_skosmos_label_list',
      ],
    ];
    $table['related_match'] = [
      'title' => t('Related Matches'),
      'help' => t('Related concept URIs from other schemes'),
      'field' => [
        'id' => 'views_skosmos_label_list',
      ],
    ];
    $table['close_match'] = [
      'title' => t('Close Matches'),
      'help' => t('Closely-matching concept URIs from other schemes'),
      'field' => [
        'id' => 'views_skosmos_label_list',
      ],
    ];
    $table['exact_match'] = [
      'title' => t('Exact Matches'),
      'help' => t('Exactly-matching concept URIs from other schemes'),
      'field' => [
        'id' => 'views_skosmos_label_list',
      ],
    ];
    $table['lang'] = [
      'title' => t('Language'),
      'help' => t('The language of the concept metadata'),
      'filter' => [
        'id' => 'language',
      ],
    ];

    // Queries the /{vocab}/broaderTransitive base table.
    $key = 'skosmos_concept_breadcrumbs_' . $host->id();
    $table = &$data[$key];
    $table['table']['group'] = t('Skosmos @name', ['@name' => $host->label()]);
    $table['table']['base'] = [
      'title' => t('Skosmos @name concept breadcrumbs', ['@name' => $host->label()]),
      'help' => t('Concept breadcrumbs'),
      'query_id' => 'views_skosmos_concept_breadcrumbs',
      'host_uri' => $host->getUri(),
    ];
    $table['vocid'] = [
      'title' => t('Vocabulary'),
      'help' => t('A SKOS vocabulary ID'),
      'filter' => [
        'id' => 'string',
      ],
      'argument' => [
        'id' => 'standard',
      ],
    ];
    $table['uri'] = [
      'title' => t('URI'),
      'help' => t('The URI of the concept'),
      'argument' => [
        'id' => 'concept_uri',
      ],
    ];
    $table['breadcrumbs'] = [
      'title' => t('Breadcrumbs'),
      'help' => t('Breadcrumbs derived from the broader concepts'),
      'field' => [
        'id' => 'views_skosmos_label_list',
      ],
    ];
  }

  return $data;
}

