<?php

namespace Drupal\views_skosmos;

use GuzzleHttp\Client as GuzzleClient;
use SkosmosClient\Api\GlobalMethodsApi;
use SkosmosClient\Configuration;

class ClientFactory {

  protected $guzzleClient;

  /**
   * Constructs a new ClientFactory.
   *
   * @param GuzzleHttp\Client $client
   *   A Guzzle HTTP client.
   */
  public function __construct(GuzzleClient $client) {
    $this->guzzleClient = $client;
  }

  /**
   * Builds a new GlobalMethodsApi client.
   *
   * @param string $uri
   *   The URI of the Skosmos API.
   *
   * @return SkosmosClient\Api\GlobalMethodsApi
   *   The API client.
   */
  public function getGlobalClient(string $uri): GlobalMethodsApi{
    $client_config = new Configuration();
    $client_config->setHost($uri);

    return new GlobalMethodsApi($this->guzzleClient, $client_config);
  }

}

