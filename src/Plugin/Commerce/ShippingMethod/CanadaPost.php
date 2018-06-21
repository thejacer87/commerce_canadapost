<?php

namespace Drupal\commerce_canadapost\Plugin\Commerce\ShippingMethod;

use Drupal\commerce_canadapost\Api\RatingServiceInterface;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\commerce_shipping\PackageTypeManagerInterface;
use Drupal\commerce_shipping\Plugin\Commerce\ShippingMethod\ShippingMethodBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The rating service.
   *
   * @var \Drupal\commerce_canadapost\Api\RatingServiceInterface
   */
  protected $ratingService;

  /**
   * Constructs a new CanadaPost object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_shipping\PackageTypeManagerInterface $package_type_manager
   *   The package type manager.
   * @param \Drupal\commerce_canadapost\Api\RatingServiceInterface $rating_service
   *   The Canada Post Rating service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PackageTypeManagerInterface $package_type_manager, RatingServiceInterface $rating_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $package_type_manager);

    $this->ratingService = $rating_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.commerce_package_type'),
      $container->get('commerce_canadapost.rating_api'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'api_information' => [
        'username' => '',
        'password' => '',
        'customer_number' => '',
        'mode' => 'test',
      ],
      'shipping_information' => [
        'origin_postal_code' => '',
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
        'test' => $this->t('Test'),
        'live' => $this->t('Live'),
      ],
      '#default_value' => $this->configuration['api_information']['mode'],
    ];

    $form['shipping_information'] = [
      '#type' => 'details',
      '#title' => $this->t('Shipping information'),
      '#description' => $this->isConfigured() ? $this->t('Update your Shipping information.') : $this->t('Fill in your Shipping information.'),
      '#weight' => $this->isConfigured() ? 10 : -10,
      '#open' => !$this->isConfigured(),
    ];

    $form['shipping_information']['origin_postal_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Origin postal code'),
      '#description' => $this->t("Enter the postal code that your shipping rates will originate. If left empty, shipping rates will be rated from your store's postal code."),
      '#default_value' => $this->configuration['shipping_information']['origin_postal_code'],
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
    $this->configuration['shipping_information']['origin_postal_code'] = $values['shipping_information']['origin_postal_code'];
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
    // Only attempt to collect rates if an address exists on the shipment.
    if ($shipment->getShippingProfile()->get('address')->isEmpty()) {
      return [];
    }

    return $this->ratingService->getRates($shipment);
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
      && !empty($api_information['password'])
      && !empty($api_information['customer_number'])
      && !empty($api_information['mode'])
    );
  }

}
