<?php

namespace Drupal\views_skosmos;

use GuzzleHttp\Client as GuzzleClient;
use SkosmosClient\Api\ConceptSpecificMethodsApi;
use SkosmosClient\Api\GlobalMethodsApi;
use SkosmosClient\Api\VocabularySpecificMethodsApi;
use SkosmosClient\Configuration;

class ClientFactory {

  /**
   * A Guzzle HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $guzzleClient;

  /**
   * The base URI of the Skosmos API.
   *
   * @var string
   */
  protected $uri;

  /**
   * Constructs a new ClientFactory.
   *
   * @param GuzzleHttp\Client $client
   *   A Guzzle HTTP client.
   * @param string $uri
   *   (optional) The base URI of the Skosmos API being queried. This may be
   *   NULL because the URI is stored as part of a view query's table
   *   information, which isn't available when the query plugin is initialized.
   *   The view must set the URI during the init() step.
   */
  public function __construct(GuzzleClient $client, string $uri = NULL) {
    $this->guzzleClient = $client;
    $this->uri = $uri;
  }

  /**
   * Gets the base URI.
   *
   * @return string
   *   The URI.
   */
  public function getUri() {
    return $this->uri;
  }

  /**
   * Sets the base URI.
   *
   * @param string $uri
   *   A URI to a Skosmos API.
   *
   * @return \Drupal\views_skosmos\ClientFactory
   *   The ClientFactory object.
   */
  public function setUri(string $uri) {
    $this->uri = $uri;
    return $this;
  }

  /**
   * Builds a new ConceptSpecificMethodsApi client.
   *
   * @return SkosmosClient\Api\ConceptSpecificMethodsApi
   *   The API client.
   */
  public function getConceptClient(): ConceptSpecificMethodsApi {
    return new ConceptSpecificMethodsApi($this->guzzleClient, $this->getConfig());
  }

  /**
   * Builds a new GlobalMethodsApi client.
   *
   * @return SkosmosClient\Api\GlobalMethodsApi
   *   The API client.
   */
  public function getGlobalClient(): GlobalMethodsApi {
    return new GlobalMethodsApi($this->guzzleClient, $this->getConfig());
  }

  /**
   * Builds a new VocabularySpecificMethodsApi client.
   *
   * @return SkosmosClient\Api\VocabularySpecificMethodsApi
   *   The API client.
   */
  public function getVocabClient(): VocabularySpecificMethodsApi {
    return new VocabularySpecificMethodsApi($this->guzzleClient, $this->getConfig());
  }

  /**
   * Initializes standard configuration for the API clients.
   *
   * @return \SkosmosClient\Configuration
   *   The configuration.
   *
   * @throws \RuntimeException
   *   Thrown if the base URI property has not been set.
   */
  protected function getConfig() {
    if (empty($this->uri)) {
      throw new \RuntimeException('The base URI of the Skosmos API has not been set in the ClientFactory.');
    }

    $client_config = new Configuration();
    $client_config->setHost($this->uri);
    return $client_config;
  }

}

