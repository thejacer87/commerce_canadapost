<?php

namespace Drupal\commerce_canadapost\EventSubscriber;

use Drupal\commerce_canadapost\Form\SettingsForm;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\RounderInterface;
use Drupal\commerce_shipping\Event\ShippingEvents;
use Drupal\commerce_shipping\Event\ShippingRatesPostCalculateEvent;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\commerce_shipping\ShippingService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener for altering the controller for commerce_order.
 */
class ShippingRatesSubscriber implements EventSubscriberInterface {

  /**
   * The Canada Post configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The rounder.
   *
   * @var \Drupal\commerce_price\RounderInterface
   */
  protected $rounder;

  /**
   * Constructs a new ShippingRatesSubscriber object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\commerce_price\RounderInterface $rounder
   *   The rounder.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RounderInterface $rounder) {
    $this->config = $config_factory->get('commerce_canadapost.settings');
    $this->rounder = $rounder;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      ShippingEvents::SHIPPING_RATES_POST_CALCULATE => [
        'modifyShippingRates',
        -10,
      ],
    ];
    return $events;
  }

  /**
   * Modifies the shipping rates.
   *
   * @param \Drupal\commerce_shipping\Event\ShippingRatesPostCalculateEvent $event
   *   The event.
   */
  public function modifyShippingRates(ShippingRatesPostCalculateEvent $event) {
    $shipping_rates = $event->getRates();
    $rates = [];
    $amount = $this->config->get('shipping_rates.amount');

    if (empty($amount)) {
      return;
    }

    /** @var \Drupal\commerce_shipping\ShippingRate $shipping_rate */
    foreach ($shipping_rates as $shipping_rate) {
      $service_code = $shipping_rate->getId();
      $service_name = $shipping_rate->getService()->getLabel();

      $shipping_service = new ShippingService(
        $service_code,
        $service_name
      );

      /** @var \Drupal\commerce_price\Price $amount */
      $amount = $shipping_rate->getAmount();
      $converted_amount = $this->convertAmount($amount);
      $rates[] = new ShippingRate(
        $service_code,
        $shipping_service,
        $converted_amount
      );
    }

    $event->setRates($rates);
  }

  /**
   * Convert the new amount based on the Canada Post settings config.
   *
   * @param \Drupal\commerce_price\Price $amount
   *   The amount to convert.
   *
   * @return \Drupal\commerce_price\Price
   *   The converted amount.
   */
  public function convertAmount(Price $amount) {
    $modifier_type = $this->config->get('shipping_rates.modifier');
    $modifier_amount = $this->config->get('shipping_rates.amount');

    if ($modifier_type === SettingsForm::PERCENTAGE) {
      $percentage = 1 + ($modifier_amount / 100);
      $converted_amount = $amount->multiply((string) $percentage);
    }
    else {
      $price = new Price((string) $modifier_amount, 'CAD');
      $converted_amount = $amount->add($price);
    }

    return $this->rounder->round($converted_amount);
  }

}
