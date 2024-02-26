<?php

namespace EwStarter\Models;

use DateTime;

/**
 * Class User_Application
 *
 * Class that models one application
 * for the prize game.
 *
 * @package EwStarter
 * @since 1.0.0
 */
class User_Application {
	/**
	 * @var int
	 *
	 * @since 1.0.0
	 */
	public int $id;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public string $first_name;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public string $last_name;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public string $email;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public string $phone;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public string $street_and_number;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public string $city;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public string $postal_code;

	/**
	 * @var string
	 * @since 1.0.0
	 */
	public string $invoice_file;

	/**
	 * @var DateTime
	 * @since 1.0.0
	 */
	public DateTime $date_created;

	/**
	 * User_Application constructor.
	 * @since 1.0.0
	 *
	 * @param array $row Database row data.
	 */
	public function __construct( array $row = [] ) {
		// Init current date as date created
		$this->date_created = new DateTime();

		// We construct empty object
		if ( empty( $row ) ) {
			return;
		}

		// We assume that all row fields are filled in
		// since we have validation for that
		$this->id                = intval( $row['id'] );
		$this->first_name        = $row['first_name'];
		$this->last_name         = $row['last_name'];
		$this->email             = $row['email'];
		$this->phone             = $row['phone'];
		$this->street_and_number = $row['street_and_number'];
		$this->city              = $row['city'];
		$this->postal_code       = $row['postal_code'];
		$this->invoice_file      = $row['invoice_file'];
		$this->date_created      = DateTime::createFromFormat( DATE_ATOM, $row['date_created'] );
	}
}
