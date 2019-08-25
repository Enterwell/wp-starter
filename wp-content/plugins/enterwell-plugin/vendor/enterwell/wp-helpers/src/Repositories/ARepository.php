<?php

namespace Ew\WpHelpers\Repositories;
/**
 * Abstract repository class.
 *
 * This class provides basic data manipulation for all repositories.
 * Those repositories are used for objects that have data stored in
 * custom db tables (that could be post types with meta data that we decided
 * to store in custom tables rather dan postmeta).
 *
 *
 * @since       1.0.0
 *
 * @package     Ew
 * @subpackage  Ew/repositories
 * @author      Matej Bosnjak <matej.bosnjak@enterwell.net>
 */
abstract class ARepository {
	/**
	 * @since       1.0.0
	 * @var         \wpdb    WordPress database manipulation object.
	 */
	protected $db;

	/**
	 * @since       1.0.0
	 * @var         string  Table name of the repository.
	 */
	protected $table_name;

	/**
	 * @since       1.0.0
	 * @var array   Allowed formats for variables in table.
	 */
	protected $allowed_formats;

	/**
	 * ARepository constructor.
	 *
	 * @param   string $table_name Table name for repository.
	 *
	 * @since   1.0.0
	 *
	 * @throws  \Exception         Repository is created when there is no wpdb.
	 */
	public function __construct( $table_name ) {
		// Get wpdb from globals.
		global $wpdb;

		// If there is no wpdb throw exception - cannot use repository here
		if ( empty( $wpdb ) ) {
			throw new \Exception( "No \$wpdb here!" );
		}

		// Set db
		$this->db = $wpdb;

		// Set table name
		$this->table_name = $this->db->prefix . $table_name;

		// Set allowed formats
		$this->allowed_formats = [ '%d', '%s', '%f' ];
	}

	/**
	 * Returns single object (constructed by _construct_object function)
	 * specified by field name and value.
	 *
	 * @since 1.0.0
	 *
	 * @param $field_name       string          Database field name (column name)
	 * @param $field_value      mixed           Column value.
	 * @param $field_format     string          Format of value.
	 * @param $object_data      mixed           Additional object data.
	 *
	 * @return                  bool|mixed      False if no such row, instance of the object if row exists.
	 */
	protected function _get_single_by_field( $field_name, $field_value, $field_format, $object_data = null ) {
		$field_format = $this->_get_valid_field_format( $field_format );

		// Prepare sql
		$sql = $this->db->prepare( "SELECT * FROM {$this->table_name} WHERE $field_name = $field_format", $field_value );

		// Get row
		$row = $this->db->get_row( $sql, ARRAY_A );

		if ( empty( $row ) ) {
			if ( empty( $object_data ) ) {
				return false;
			}

			return $this->_construct_object( [], $object_data );
		}

		return $this->_construct_object( $row, $object_data );
	}

	/**
	 * Checks if field format is valid, if it is not returns
	 * default format.
	 *
	 * @since 1.0.0
	 *
	 * @param   $field_format       string      Desired field format.
	 *
	 * @return                      string      Valid field format.
	 */
	protected function _get_valid_field_format( $field_format ) {
		// If format is not valid - set it to string
		if ( ! in_array( $field_format, $this->allowed_formats ) ) {
			$field_format = '%s';
		}

		return $field_format;
	}

	/**
	 * Constructs object instance from table row and additional object data.
	 * Additional data could be WP_Post object or any other data related
	 * to object that is not stored in object table.
	 *
	 * @since   1.0.0
	 *
	 * @param array $table_row
	 * @param mixed $object_data
	 *
	 * @return          mixed
	 */
	protected abstract function _construct_object( $table_row, $object_data = null );

	/**
	 * Returns array of objects (constructed by _construct_object function)
	 * specified by field name and value.
	 *
	 * @since 1.0.0
	 *
	 * @param $field_name               string          Database field name (column name)
	 * @param $field_value              mixed           Column value.
	 * @param $field_format             string          Format of value.
	 * @param $object_data_extractor    callable        Fuction used to extract single object data from array of object data.
	 * @param $objects_data             mixed           Additional data for all objects.
	 *
	 * @return                  array           False if no such row, instance of the object if row exists.
	 */
	protected function _get_all_by_field( $field_name, $field_value, $field_format, $object_data_extractor = null, $objects_data = null ) {
		$results = [];

		$field_format = $this->_get_valid_field_format( $field_format );

		// Prepare sql
		$sql = $this->db->prepare( "SELECT * FROM {$this->table_name} WHERE $field_name = $field_format", $field_value );

		// Get row
		$rows = $this->db->get_results( $sql, ARRAY_A );

		if ( empty( $rows ) ) {
			return $results;
		}

		foreach ( $rows as $row ) {
			$object_data = ! empty( $object_data_extractor ) ? $object_data_extractor( $row, $objects_data ) : null;
			$results[]   = $this->_construct_object( $row, $object_data );
		}

		return $results;
	}

	/**
	 * Return all objects in database.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $get_object_data Function that gets object data from object row.
	 *
	 * @return  array       Array of all objects in the db.
	 */
	protected function _get_all( $get_object_data = null ) {
		$results = [];

		$rows = $this->db->get_results( "SELECT * FROM {$this->table_name}", ARRAY_A );

		if ( empty( $rows ) ) {
			return $results;
		}

		foreach ( $rows as $row ) {
			$object_data = ! empty( $get_object_data ) ? $get_object_data( $row ) : null;
			$results[]   = $this->_construct_object( $row, $object_data );
		}

		return $results;
	}

	/**
	 * Deletes row in database specified by field name and value.
	 *
	 * @since 1.0.0
	 *
	 * @param $field_name       string          Database field name (column name)
	 * @param $field_value      mixed           Column value.
	 * @param $field_format     string          Format of value.
	 *
	 * @return                  bool            True if successful, false otherwise.
	 */
	protected function _delete_row_by_field( $field_name, $field_value, $field_format ) {
		$field_format = $this->_get_valid_field_format( $field_format );

		$res = $this->db->delete( $this->table_name, [ $field_name => $field_value ], $field_format );

		return $res !== false;
	}
}