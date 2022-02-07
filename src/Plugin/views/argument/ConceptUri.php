<?php

namespace Drupal\views_skosmos\Plugin\views\argument;

use Drupal\views\Plugin\views\argument\Standard;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\views_skosmos\ClientFactory;
use Drupal\views_skosmos\ViewsHelperTrait;
use SkosmosClient\ApiException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Argument handler to accept a Concept URI.
 *
 * @ViewsArgument("concept_uri")
 */
class ConceptUri extends Standard {

  use ViewsHelperTrait;

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
   * Constructs a Concept object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   i*   The plugin_id for the plugin instance.
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
    $this->globalClient = $this->clientFactory->getGlobalClient();
  }

  /**
   * {@inheritdoc}
   */
  public function title() {
    // @todo This is a hack that won't work with all language filter options.
    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
    try {
      $result = $this->globalClient->labelGet($this->argument, $lang);
      return $result->getPrefLabel();
    }
    catch (ApiException $e) {
      // Something went wrong with the API. Display the default.
      return '';
    }
  }

}

