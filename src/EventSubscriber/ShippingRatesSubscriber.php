<?php

namespace Drupal\commerce_canadapost\EventSubscriber;

use Drupal\commerce_shipping\Event\ShippingEvents;
use Drupal\commerce_shipping\Event\ShippingRatesPostCalculateEvent;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\commerce_shipping\ShippingService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener for altering the controller for commerce_order.
 */
class ShippingRatesSubscriber implements EventSubscriberInterface {

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

  public function modifyShippingRates(ShippingRatesPostCalculateEvent $event) {
    $shipping_rates = $event->getRates();
    $converted_shipping_rates = [];

    /** @var  \Drupal\commerce_shipping\ShippingRate $shipping_rate * */
    foreach ($shipping_rates as $shipping_rate) {
      /** @var \Drupal\commerce_price\Price $amount */
      $amount = $shipping_rate->getAmount();

      $conversion = '0.5';
      $converted_amount = $amount->multiply($conversion);

      $service_code = $shipping_rate->getId();
      $service_name = $shipping_rate->getService()->getLabel();

      $shipping_service = new ShippingService(
        $service_code,
        $service_name
      );
      $rates[] = new ShippingRate(
        $service_code,
        $shipping_service,
        $converted_amount
      );

    }

    $shipping_rates = $converted_shipping_rates;

  }
}
