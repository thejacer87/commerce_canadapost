<?php

namespace Drupal\commerce_canadapost\Commands;

use Drupal\commerce_canadapost\Api\RatingServiceInterface;
use Drupal\commerce_canadapost\Api\TrackingServiceInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for the Commerce Canada Post module.
 */
class Commands extends DrushCommands {

  /**
   * The Tracking API service.
   *
   * @var \Drupal\commerce_canadapost\Api\TrackingServiceInterface
   */
  protected $trackingApi;

  /**
   * The Rating API service.
   *
   * @var \Drupal\commerce_canadapost\Api\RatingServiceInterface
   */
  protected $ratingApi;

  /**
   * Constructs a new Commands object.
   *
   * @param \Drupal\commerce_canadapost\Api\RatingServiceInterface $service_api
   *   The Rating API service.
   * @param \Drupal\commerce_canadapost\Api\TrackingServiceInterface $tracking_api
   *   The Tracking API service.
   */
  public function __construct(RatingServiceInterface $service_api, TrackingServiceInterface $tracking_api) {
    $this->trackingApi = $tracking_api;
    $this->ratingApi = $service_api;
  }

  /**
   * Fetch the tracking number for the given tracking PIN.
   *
   * @param string $tracking_pin
   *   The tracking PIN for which to fetch the tracking number.
   *
   * @command commerce-canadapost-tracking-number
   *
   * @usage commerce-cp-tn 1234567
   *   Fetch the tracking number for the 1234567 tracking PIN.
   *
   * @aliases commerce-cp-tn
   */
  public function fetchTrackingNumber($tracking_pin) {
    $tracking_summary = $this->trackingApi->fetchTrackingNumber($tracking_pin);

    $this->output->writeln(var_export($tracking_summary, TRUE));
  }

  /**
   * Get rates for the provided postal codes and package weight.
   *
   * @param array $options
   *   An array of options.
   *
   * @command commerce-canadapost-get-rates
   *
   * @option origin_postal_code
   *  The origin postal code.
   *  Defaults to H2B1A0.
   * @option postal_code
   *  The destination postal code.
   *  Defaults to K1K4T3.
   * @option weight
   *  The weight (in grams) of the shipment.
   *  Defaults to 1.
   *
   * @usage commerce-canadapost-get-rates
   *   Get the shipping rates for a package (1g) from H2B1A0 to K1K4T3.
   * @usage commerce-canadapost-get-rates
   *   Alias to get the shipping rates for a package (1g) from H2B1A0 to
   * K1K4T3.
   * @usage commerce-cp-gr H0H0H0
   *   Get the shipping rates for a package (1g) from H0H0H0 to K1K4T3.
   * @usage commerce-cp-gr H0H0H0 K1V1J8
   *   Get the shipping rates for a package (1g) from H0H0H0 to K1V1J8.
   * @usage commerce-cp-gr H0H0H0 K1V1J8 100
   *   Get the shipping rates for a package (100g) from H0H0H0 to K1V1J8.
   *
   * @aliases commerce-cp-gr
   */
  public function getRates(
    array $options
      = [
        'origin_postal_code' => 'H2B1A0',
        'postal_ode' => 'K1K4T3',
        'weight' => 1,
      ]
  ) {
    $origin_postal_code = $options['origin_postal_code'];
    $postal_code = $options['postal_code'];
    $weight = $options['weight'];
    $rates = $this->ratingApi->getRates($origin_postal_code, $postal_code, $weight);

    $this->output->writeln(var_export($rates['price-quotes'], TRUE));
  }

}
