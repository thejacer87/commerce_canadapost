<?php

namespace Drupal\commerce_canadapost\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Canada Post settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Constants.
   */
  const PERCENTAGE = 'percentage';
  const FIXED = 'fixed';

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

    $form['shipping_rates'] = [
      '#type' => 'details',
      '#title' => $this->t('Shipping Rate Modifications'),
      '#open' => TRUE,
    ];

    $form['shipping_rates']['amount'] = [
      '#type' => 'commerce_number',
      '#size' => 16,
      '#title' => $this->t('Amount'),
      '#default_value' => $config->get('shipping_rates.amount'),
      '#description' => $this->t('Modify the shipping rates by a fixed amount or percentage.'),
      '#required' => FALSE,
    ];

    $form['shipping_rates']['modifier'] = [
      '#type' => 'select',
      '#title' => $this->t('Modifier'),
      '#options' => [$this::FIXED => '$', $this::PERCENTAGE => '%'],
      '#default_value' => $config->get('shipping_rates.modifier'),
      '#required' => FALSE,
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
      ->set('shipping_rates.modifier', $form_state->getValue('modifier'))
      ->set('shipping_rates.amount', $form_state->getValue('amount'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
