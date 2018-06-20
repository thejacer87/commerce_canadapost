<?php

namespace Drupal\commerce_canadapost\Api;

/**
 * Defines the interface for the Rating API integration service.
 */
interface RatingServiceInterface {

  /**
   * Get rates from the Canada Post API.
   *
   * @param string $originPostalCode
   *   The origin postal code.
   * @param string $postalCode
   *   The destination postal code.
   * @param string $weight
   *   The weight of the package.
   *
   * @return array
   *   The rates returned by Canada Post.
   */
  public function getRates($originPostalCode, $postalCode, $weight);

}
