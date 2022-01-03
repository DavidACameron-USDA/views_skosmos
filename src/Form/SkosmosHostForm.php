<?php

namespace Drupal\views_skosmos\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SkosmosHostForm.
 */
class SkosmosHostForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $host = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $host->label(),
      '#description' => $this->t("Label for the Host."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $host->id(),
      '#machine_name' => [
        'exists' => '\Drupal\views_skosmos\Entity\SkosmosHost::load',
      ],
      '#disabled' => !$host->isNew(),
    ];

    $form['uri'] = [
      '#type' => 'url',
      '#title' => $this->t('URI'),
      '#default_value' => $host->getUri(),
      '#description' => $this->t('Enter the URI of the Skosmos API including the path.  Do  not include any vocabulary ID.'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $host = $this->entity;
    $status = $host->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Host.', [
          '%label' => $host->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Host.', [
          '%label' => $host->label(),
        ]));
    }
    $form_state->setRedirectUrl($host->toUrl('collection'));
  }

}

