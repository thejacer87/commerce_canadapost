<?php

namespace Drupal\commerce_canadapost\Api;

use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\commerce_shipping\ShippingRate;
use Drupal\commerce_shipping\ShippingService;
use Exception;

/**
 * Class RateRequest.
 *
 * @package Drupal\commerce_canadapost
 */
class RateRequest extends Request {

  /**
   * Fetch rates from the Canada Post API.
   *
   * @param \Drupal\commerce_shipping\Entity\Shipment $commerce_shipment
   *   A Drupal Commerce shipment entity.
   *
   * @return array
   *   The rates returned by Canada Post formatted for Shipping Method.
   *
   * @throws \Exception
   */
  public function getRates(Shipment $commerce_shipment) {
    // Validate a commerce shipment has been provided.
    if (empty($commerce_shipment)) {
      throw new Exception('Shipment not provided');
    }
    $rates = [];
    $auth = $this->getAuth();

    $request = new Rate(
      $auth['username'],
      $auth['password'],
      $auth['customer_number'],
      $commerce_shipment
    );

    $response = $request->sendRequest();
    if (empty($response)) {
      return $rates;
    }

    foreach ($response as $rate) {
      $service_code = $rate['code'];
      $service_name = $rate['name'];
      $price = new Price((string) $rate['price'], 'CAD');

      $shipping_service = new ShippingService(
        $service_code,
        $service_name
      );
      $rates[] = new ShippingRate(
        $service_code,
        $shipping_service,
        $price
      );
    }

    return $rates;
  }

}
