<?php

class ITSEC_Lib_Opaque_Tokens {
	const TTL = 1800; // 30 minutes
	const MAX_TTL = 604800; // Maximum TTL for tokens.

	const E_INVALID = 'itsec-opaque-token-invalid';
	const E_MISSING = 'itsec-opaque-token-not-found';
	const E_EXPIRED = 'itsec-opaque-token-expired';
	const E_HASH_FAILED = 'itsec-opaque-token-hash-failed';

	/**
	 * Verify that a token is valid and get it's data.
	 *
	 * @param string $type
	 * @param string $token
	 * @param int    $ttl
	 *
	 * @return array|WP_Error
	 */
	public static function verify_and_get_token_data( $type, $token, $ttl = self::TTL ) {
		global $wpdb;

		if ( $ttl > self::MAX_TTL ) {
			_doing_it_wrong( __METHOD__, sprintf( 'Token ttl must not be greater than %d seconds.', self::MAX_TTL ), '5.3.0' );

			return new WP_Error( self::E_INVALID, __( 'Invalid token.', 'better-wp-security' ) );
		}

		$token = base64_decode( $token );

		if ( strpos( $token, '|' ) === false ) {
			return new WP_Error( self::E_INVALID, __( 'Invalid token.', 'better-wp-security' ) );
		}

		list( $id, $unhashed ) = explode( '|', $token );

		$data = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->base_prefix}itsec_opaque_tokens WHERE token_id = %s AND token_type = %s LIMIT 1",
			$id,
			$type
		), ARRAY_A );

		if ( ! $data ) {
			return new WP_Error( self::E_MISSING, __( 'Token not found.', 'better-wp-security' ) );
		}

		if ( strtotime( $data['token_created_at'] ) + $ttl < ITSEC_Core::get_current_time_gmt() ) {
			self::delete_token( $token );

			return new WP_Error( self::E_EXPIRED, __( 'Token expired.', 'better-wp-security' ) );
		}

		if ( ! ITSEC_Lib::verify_token( $unhashed, $data['token_hashed'] ) ) {
			return new WP_Error( self::E_INVALID, __( 'Invalid token.', 'better-wp-security' ) );
		}

		return json_decode( $data['token_data'], true );
	}

	/**
	 * Create a new token.
	 *
	 * @param string $type
	 * @param array  $data
	 *
	 * @return string|WP_Error
	 */
	public static function create_token( $type, array $data ) {
		global $wpdb;

		$id     = ITSEC_Lib::generate_token();
		$token  = ITSEC_Lib::generate_token();
		$hashed = ITSEC_Lib::hash_token( $token );

		if ( ! $id || ! $token || ! $hashed ) {
			return new WP_Error( self::E_HASH_FAILED, __( 'Failed to generate token and hash.', 'better-wp-security' ) );
		}

		$wpdb->insert( $wpdb->base_prefix . 'itsec_opaque_tokens', array(
			'token_id'         => $id,
			'token_hashed'     => $hashed,
			'token_type'       => $type,
			'token_data'       => wp_json_encode( $data ),
			'token_created_at' => gmdate( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() ),
		) );

		return base64_encode( "{$id}|{$token}" );
	}

	/**
	 * Delete a token.
	 *
	 * @param string $token
	 */
	public static function delete_token( $token ) {
		global $wpdb;

		$token = base64_decode( $token );
		list( $id ) = explode( '|', $token );

		$wpdb->delete( $wpdb->base_prefix . 'itsec_opaque_tokens', array( 'token_id' => $id ) );
	}

	/**
	 * Delete tokens that are more than 7 days old.
	 */
	public static function delete_expired_tokens() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->base_prefix}itsec_opaque_tokens WHERE token_created_at < %s",
			gmdate( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() - self::MAX_TTL )
		) );
	}
}
