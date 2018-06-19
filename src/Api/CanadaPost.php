<?php

namespace Drupal\commerce_canadapost\Api;

/**
 * Base class for Canada Post API services.
 */
abstract class CanadaPost {

  const BASE_URL = 'https://ct.soa-gw.canadapost.ca/rs/';

  /**
   * The Canada Post API username.
   *
   * @var string
   */
  protected $username;

  /**
   * The Canada Post API password.
   *
   * @var string
   */
  protected $password;

  /**
   * The Canada Post API customer number.
   *
   * @var string
   */
  protected $customerNumber;

  /**
   * Constructor.
   *
   * @param string|null $username
   *   Canada Post username.
   * @param string|null $password
   *   Canada Post password.
   * @param string|null $customerNumber
   *   Canada Post customer number.
   */
  public function __construct(
    $username = NULL,
    $password = NULL,
    $customerNumber = NULL
  ) {
    $this->username = $username;
    $this->password = $password;
    $this->customerNumber = $customerNumber;
  }

  /**
   * Get username.
   *
   * @return string
   *   The username.
   */
  public function getUsername() {
    return $this->username;
  }

  /**
   * Set the username.
   *
   * @param string $username
   *   The username.
   */
  public function setUsername($username) {
    $this->username = $username;
  }

  /**
   * Get password.
   *
   * @return string
   *   The password.
   */
  public function getPassword() {
    return $this->password;
  }

  /**
   * Set the password.
   *
   * @param string $password
   *   The password.
   */
  public function setPassword($password) {
    $this->password = $password;
  }

  /**
   * Get customer number.
   *
   * @return string
   *   The customer number.
   */
  public function getCustomerNumber() {
    return $this->customerNumber;
  }

  /**
   * Set the customer number.
   *
   * @param string $customerNumber
   *   The customer number.
   */
  public function setCustomerNumber($customerNumber) {
    $this->customerNumber = $customerNumber;
  }

}
