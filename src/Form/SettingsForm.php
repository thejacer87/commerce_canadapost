<?php

namespace Drupal\commerce_canadapost\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Canada Post settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_canadapost_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['commerce_canadapost.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('commerce_canadapost.settings');

    $form['api'] = [
      '#type' => 'details',
      '#title' => $this->t('API authentication'),
      '#open' => TRUE,
    ];

    $form['api']['api_customer_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Customer number'),
      '#default_value' => $config->get('api.customer_number'),
      '#required' => TRUE,
    ];
    $form['api']['api_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#default_value' => $config->get('api.username'),
      '#required' => TRUE,
    ];
    $form['api']['api_password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#default_value' => $config->get('api.password'),
      '#required' => TRUE,
    ];
    $form['api']['api_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Mode'),
      '#default_value' => $config->get('api.mode'),
      '#options' => [
        'test' => $this->t('Test'),
        'live' => $this->t('Live'),
      ],
      '#required' => TRUE,
    ];
    $form['api']['rate']['origin_postal_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Origin postal code'),
      '#default_value' => $config->get('api.rate.origin_postal_code'),
      '#description' => $this->t("Enter the postal code that your shipping rates will originate. If left empty, shipping rates will be rated from your store's postal code."),
    ];

    $form['option_codes'] = [
      '#type'  => 'details',
      '#title' => $this->t('Shipping rate options codes'),
      '#open'  => TRUE,
    ];

    $form['option_codes']['codes'] = [
      '#type' => 'checkboxes',
      '#options' => [
        'so'   => 'Signature (SO)',
        'pa18' => 'Proof of Age Required - 18 (PA18)',
        'pa19' => 'Proof of Age Required - 19 (PA19)',
        'hfp'  => 'Card for pickup (HFP)',
        'dns'  => 'Do not safe drop (DNS)',
        'lad'  => 'Leave at door - do not card (LAD)',
      ],
      '#default_value' => $config->get('option_codes.codes'),
      '#description' => $this->t(
        "Select which options to add to the get rates request. <strong>NOTE:</strong> Some options conflict with each other (eg. PA18, PA19 and DNS), so be sure to check the logs if the rates fail to load on checkout as the Canada Post API can't currently handle the conflicts."
      ),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this
      ->config('commerce_canadapost.settings')
      ->set('api.customer_number', $form_state->getValue('api_customer_number'))
      ->set('api.username', $form_state->getValue('api_username'))
      ->set('api.password', $form_state->getValue('api_password'))
      ->set('api.mode', $form_state->getValue('api_mode'))
      ->set('api.rate.origin_postal_code', $form_state->getValue('origin_postal_code'))
      ->set('option_codes.codes', $form_state->getValue('codes'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
