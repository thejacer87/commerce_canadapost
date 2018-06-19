<?php

namespace Drupal\commerce_canadapost\Plugin\Commerce\ShippingMethod;

use Drupal\commerce_canadapost\Api\RateRequest;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Canada Post shipping method.
 *
 * @CommerceShippingMethod(
 *  id = "canadapost",
 *  label = @Translation("Canada Post"),
 *  services = {
 *    "DOM.EP" = @translation("Expedited Parcel"),
 *    "DOM.RP" = @translation("Regular Parcel"),
 *    "DOM.PC" = @translation("Priority"),
 *    "DOM.XP" = @translation("Xpresspost")
 *   }
 * )
 */
class CanadaPost extends ShippingMethodBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'api_information' => [
        'username' => '',
        'password' => '',
        'customer_number' => '',
        'mode' => 'dev',
      ],
      'options' => [
        'log' => [],
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['api_information'] = [
      '#type' => 'details',
      '#title' => $this->t('API information'),
      '#description' => $this->isConfigured() ? $this->t('Update your Canada Post API information.') : $this->t('Fill in your Canada Post API information.'),
      '#weight' => $this->isConfigured() ? 10 : -10,
      '#open' => !$this->isConfigured(),
    ];

    $form['api_information']['username'] = [
      '#type' => 'textfield',
      '#title' => t('Username'),
      '#default_value' => $this->configuration['api_information']['username'],
      '#required' => TRUE,
    ];

    $form['api_information']['password'] = [
      '#type' => 'textfield',
      '#title' => t('Password'),
      '#default_value' => $this->configuration['api_information']['password'],
      '#required' => TRUE,
    ];

    $form['api_information']['customer_number'] = [
      '#type' => 'textfield',
      '#title' => t('Customer Number'),
      '#default_value' => $this->configuration['api_information']['customer_number'],
      '#required' => TRUE,
    ];

    $form['api_information']['mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Mode'),
      '#description' => $this->t('Choose whether to use the test or live mode.'),
      '#options' => [
        'dev' => $this->t('Test'),
        'prod' => $this->t('Live'),
      ],
      '#default_value' => $this->configuration['api_information']['mode'],
    ];

    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('Canada Post Options'),
      '#description' => $this->t('Additional options for Canada Post'),
    ];
    $form['options']['log'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Log the following messages for debugging'),
      '#options' => [
        'request' => $this->t('API request messages'),
        'response' => $this->t('API response messages'),
      ],
      '#default_value' => $this->configuration['options']['log'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue($form['#parents']);

    $this->configuration['api_information']['username'] = $values['api_information']['username'];
    $this->configuration['api_information']['password'] = $values['api_information']['password'];
    $this->configuration['api_information']['customer_number'] = $values['api_information']['customer_number'];
    $this->configuration['api_information']['mode'] = $values['api_information']['mode'];
    $this->configuration['options']['log'] = $values['options']['log'];

    return parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Calculates rates for the given shipment.
   *
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *   The shipment.
   *
   * @return \Drupal\commerce_shipping\ShippingRate[]
   *   The rates.
   */
  public function calculateRates(ShipmentInterface $shipment) {
    $rates = [];

    // Only attempt to collect rates if an address exits on the shipment.
    if (!$shipment->getShippingProfile()->get('address')->isEmpty()) {
      $request = new RateRequest($this->configuration);
      $rates = $request->getRates($shipment);
    }

    return $rates;
  }

  /**
   * Determine if we have the minimum information to connect to Canada Post.
   *
   * @return bool
   *   TRUE if there is enough information to connect, FALSE otherwise.
   */
  protected function isConfigured() {
    $api_information = $this->configuration['api_information'];

    return (
      !empty($api_information['username'])
      && !empty($api_information['password']
        && !empty($api_information['customer_number']))
    );
  }

}
