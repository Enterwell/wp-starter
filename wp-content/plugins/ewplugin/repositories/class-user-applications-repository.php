<?php
/**
 * Created by PhpStorm.
 * User: mateikki
 * Date: 31.8.2020.
 * Time: 13:33
 */

namespace EwStarter\Repositories;

use Ew\WpHelpers\Classes\Db_Data;
use Ew\WpHelpers\Repositories\ARepository;
use EwStarter\Models\User_Application;
use EwStarter\Repositories\Interfaces\User_Applications_Repository_Interface;

/**
 * Class User_Applications_Repository
 * @package EwStarter
 */
class User_Applications_Repository extends ARepository implements User_Applications_Repository_Interface {
	/**
	 * User_Applications_Repository constructor.
	 * @throws \Exception
	 */
	public function __construct() {
		parent::__construct( 'user_applications' );
	}

	/**
	 * Saves user application.
	 *
	 * @param User_Application $user_application
	 *
	 * @return User_Application
	 * @throws \Exception
	 * @since 1.0.0
	 *
	 */
	public function save( User_Application $user_application ): User_Application {
		$this->validate( $user_application );
		$db_data = $this->get_db_data( $user_application );

		if ( ! empty( $user_application->id ) ) {
			// Update in the db
			$res = $this->db->update(
				$this->table_name,
				$db_data['values'],
				[ 'id' => $user_application->id ],
				$db_data['formats'],
				'%d'
			);

			// Check if updated
			if ( $res === false ) {
				throw new \Exception( 'User_Application UPDATE failed!' );
			}
		} else {
			// Insert into db
			$res = $this->db->insert(
				$this->table_name,
				$db_data['values'],
				$db_data['formats']
			);

			// Check if insert failed
			if ( $res === false ) {
				throw new \Exception( 'User_Application CREATE failed!' );
			}

			// Set up inserted id
			$user_application->id = $this->db->insert_id;
		}

		return $user_application;
	}

	/**
	 * Get user application by id.
	 *
	 * @param int $id
	 *
	 * @return User_Application
	 */
	public function get( int $id ): User_Application {
		return $this->_get_single_by_field( 'id', intval( $id ), '%d' );
	}

	/**
	 * Constructs object instance from table row and additional object data.
	 * Additional data could be WP_Post object or any other data related
	 * to object that is not stored in object table.
	 *
	 * @param array $table_row
	 * @param mixed $object_data
	 *
	 * @return          mixed
	 * @since   1.0.0
	 *
	 */
	protected function _construct_object( $table_row, $object_data = null ): User_Application {
		return new User_Application( $table_row );
	}

	/**
	 * Validates UA before save
	 *
	 * @param User_Application $user_application
	 *
	 * @throws \Exception
	 */
	private function validate( User_Application $user_application ): void {
		$errors        = [];
		$required_vars = [
			'first_name',
			'last_name',
			'email',
			'phone',
			'street_and_number',
			'city',
			'postal_code',
			'invoice_file',
			'invoice_file'
		];

		foreach ( $required_vars as $required_var ) {
			if ( empty( $user_application->$required_var ) ) {
				$errors[] = "[$required_var] is empty!";
			}
		}

		// Validate email format (if the email is not empty)
		if ( ! empty( $user_application->email ) && ! filter_var( $user_application->email, FILTER_VALIDATE_EMAIL ) ) {
			$errors[] = '[email] is not in valid format!';
		}

		if ( ! empty( $errors ) ) {
			throw new \Exception( implode( ',', $errors ) );
		}
	}

	/**
	 * Gets db data for UA (prepared for db action)
	 *
	 * @param User_Application $user_application
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	private function get_db_data( User_Application $user_application ): array {
		$db_data = new Db_Data();

		$db_data->add_data_if_not_empty( 'first_name', $user_application->first_name, '%s' );
		$db_data->add_data_if_not_empty( 'last_name', $user_application->last_name, '%s' );
		$db_data->add_data_if_not_empty( 'email', $user_application->email, '%s' );
		$db_data->add_data_if_not_empty( 'phone', $user_application->phone, '%s' );
		$db_data->add_data_if_not_empty( 'street_and_number', $user_application->street_and_number, '%s' );
		$db_data->add_data_if_not_empty( 'city', $user_application->city, '%s' );
		$db_data->add_data_if_not_empty( 'postal_code', $user_application->postal_code, '%s' );
		$db_data->add_data_if_not_empty( 'invoice_file', $user_application->invoice_file, '%s' );
		$db_data->add_data_if_not_empty( 'date_created', $user_application->date_created->format( DATE_ATOM ), '%s' );

		return $db_data->get_data();
	}
}
