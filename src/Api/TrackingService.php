<?php

namespace Drupal\commerce_canadapost\Api;

use CanadaPost\Exception\ClientException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use CanadaPost\Tracking;

/**
 * Provides the default Tracking API integration services.
 */
class TrackingService implements TrackingServiceInterface {

  /**
   * The Canada Post configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Constructs a new TrackingService object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->config = $config_factory->get('commerce_canadapost.settings');
    $this->logger = $logger_factory->get(COMMERCE_CANADAPOST_LOGGER_CHANNEL);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchTrackingSummary($tracking_pin) {
    $config = [
      'username' => $this->config->get('api.username'),
      'password' => $this->config->get('api.password'),
      'customer_number' => $this->config->get('api.customer_number'),
    ];

    try {
      $tracking = new Tracking($config);
      $response = $tracking->getSummary($tracking_pin);
    }
    catch (ClientException $exception) {
      $message = sprintf(
        'An error has been returned by the Canada Post when fetching the tracking summary for the tracking PIN "%s". The error was: "%s"',
        $tracking_pin,
        json_encode($exception->getResponseBody())
      );
      $this->logger->error($message);
      return;
    }

    return $this->parseResponse($response);
  }

  /**
   * Parse results from Canada Post API into ShippingRates.
   *
   * @param array $response
   *   The response from the Canada Post API Rating service.
   *
   * @return \Drupal\commerce_shipping\ShippingRate[]
   *   The Canada Post shipping rates.
   */
  private function parseResponse(array $response) {
    if (!empty($response['tracking-summary']['pin-summary'])) {
      return $response['tracking-summary']['pin-summary'];
    }

    return [];
  }

}
