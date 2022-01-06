<?php

namespace Drupal\views_skosmos\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Plugin\views\field\MultiItemsFieldHandlerInterface;
use Drupal\views\ResultRow;

/**
 * Field handler to display multiple-valued fields from a Skosmos API
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("views_skosmos_list")
 */
class SkosmosListField extends FieldPluginBase implements MultiItemsFieldHandlerInterface {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['type'] = ['default' => 'separator'];
    $options['separator'] = ['default' => ', '];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

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
      // FieldPluginBase expects each item to be an array. In the absence of
      // a defined, complex data structure we provide a default "value" key.
      $items[] = [
        'value' => $value,
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
    // Return the value key that was defined in getItems().
    return $item['value'];
  }

}

