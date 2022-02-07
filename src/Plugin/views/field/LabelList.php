<?php

namespace Drupal\views_skosmos\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Plugin\views\field\MultiItemsFieldHandlerInterface;
use Drupal\views\ResultRow;

/**
 * Field handler to display multiple-valued concept relation fields.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("views_skosmos_label_list")
 */
class LabelList extends FieldPluginBase implements MultiItemsFieldHandlerInterface {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['link_to_uri'] = ['default' => TRUE];
    $options['type'] = ['default' => 'separator'];
    $options['separator'] = ['default' => ', '];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['link_to_uri'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Link the label to the URI'),
      '#default_value' => !empty($this->options['link_to_uri']),
    ];

    $form['type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Display type'),
      '#options' => [
        'ul' => $this->t('Unordered list'),
        'ol' => $this->t('Ordered list'),
        'separator' => $this->t('Simple separator'),
      ],
      '#default_value' => $this->options['type'],
    ];

    $form['separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Separator'),
      '#default_value' => $this->options['separator'],
      '#states' => [
        'visible' => [
          ':input[name="options[type]"]' => ['value' => 'separator'],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getItems(ResultRow $values) {
    $items = [];

    foreach ($this->getValue($values) as $value) {
      $items[] = [
        'uri' => $value->getUri(),
        'label' => $value->getLabel(),
      ];
    }
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function renderItems($items) {
    if (!empty($items)) {
      if ($this->options['type'] == 'separator') {
        $render = [
          '#type' => 'inline_template',
          '#template' => '{{ items|safe_join(separator) }}',
          '#context' => [
            'items' => $items,
            'separator' => $this->sanitizeValue($this->options['separator'], 'xss_admin'),
          ],
        ];
      }
      else {
        $render = [
          '#theme' => 'item_list',
          '#items' => $items,
          '#title' => NULL,
          '#list_type' => $this->options['type'],
        ];
      }
      return \Drupal::service('renderer')->render($render);
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function render_item($count, $item) {
    if (!empty($this->options['link_to_uri'])) {
      return Link::fromTextAndUrl($this->sanitizeValue($item['label']), Url::fromUri($item['uri']))->toString();
    }
    else {
      return $this->sanitizeValue($item['label']);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function documentSelfTokens(&$tokens) {
    $tokens['{{ ' . $this->options['id'] . '__label' . ' }}'] = $this->t('The label of the Concept.');
    $tokens['{{ ' . $this->options['id'] . '__uri' . ' }}'] = $this->t('The URI of the Concept.');
  }

  /**
   * {@inheritdoc}
   */
  protected function addSelfTokens(&$tokens, $item) {
    $tokens['{{ ' . $this->options['id'] . '__label }}'] = $item['label'];
    $tokens['{{ ' . $this->options['id'] . '__uri }}'] = $item['uri'];
  }

}

