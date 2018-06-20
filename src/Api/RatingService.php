<?php

namespace Drupal\commerce_canadapost\Api;

use CanadaPost\Exception\ClientException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use CanadaPost\Rating;

/**
 * Provides the default Rating API integration services.
 */
class RatingService implements RatingServiceInterface {

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
  public function getRates($originPostalCode, $postalCode, $weight) {
    $config = [
      'username' => $this->config->get('api.username'),
      'password' => $this->config->get('api.password'),
      'customerNumber' => $this->config->get('api.customer_number'),
    ];

    try {
      $request = new Rating($config);
      $rates = $request->getRates($originPostalCode, $postalCode, $weight);
    }
    catch (ClientException $exception) {
      $message = sprintf(
        'An error has been returned by the Canada Post when fetching the shipping rates. The error was: "%s"',
        json_encode($exception->getResponseBody())
      );
      $this->logger->error($message);
      return;
    }

    return $rates;
  }

}
