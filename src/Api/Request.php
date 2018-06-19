<?php

namespace Drupal\commerce_canadapost\Api;

/**
 * Canada Post API Service.
 *
 * @package Drupal\commerce_canadapost
 */
abstract class Request {
  /**
   * The configuration array.
   *
   * @var array
   */
  protected $configuration;

  /**
   * Sets configuration for requests.
   *
   * @param array $configuration
   *   A configuration array from a CommerceShippingMethod.
   */
  public function __construct(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * Returns authentication array for a request.
   *
   * @return array
   *   An array of authentication parameters.
   *
   * @throws \Exception
   */
  public function getAuth() {
    $config = $this->configuration['api_information'];

    if (empty($config['username']) || empty($config['password']) || empty($config['customer_number']) || empty($this->configuration['api_information']['mode'])) {
      throw new \Exception('Configuration is required.');
    }

    return [
      'username' => $this->configuration['api_information']['username'],
      'password' => $this->configuration['api_information']['password'],
      'customer_number' => $this->configuration['api_information']['customer_number'],
      'mode' => $this->configuration['api_information']['mode'],
    ];
  }

}
