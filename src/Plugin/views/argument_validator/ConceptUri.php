<?php

namespace Drupal\views_skosmos\Plugin\views\argument_validator;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\views\Plugin\views\argument_validator\ArgumentValidatorPluginBase;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\views_skosmos\ClientFactory;
use Drupal\views_skosmos\ViewsHelperTrait;
use SkosmosClient\ApiException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Validate whether an argument is a Concept URI or not.
 *
 * @ingroup views_argument_validate_plugins
 *
 * @ViewsArgumentValidator(
 *   id = "views_skosmos_concept_uri",
 *   title = @Translation("Concept URI")
 * )
 */
class ConceptUri extends ArgumentValidatorPluginBase {

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
   * Constructs a ConceptUri object.
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
  public function validateArgument($argument) {
    try {
      $this->globalClient->dataGetWithHttpInfo($argument);
    }
    catch (ApiException $e) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextDefinition() {
    return new ContextDefinition('string', $this->argument->adminLabel(), FALSE);
  }

}

