<?php
/**
 * Secure storage/retrieval of R2 secret key.
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class R2WB_Credentials
 */
class R2WB_Credentials {

	/**
	 * Get decrypted secret access key.
	 *
	 * @return string
	 */
	public static function get_secret_key() {
		$stored = get_option( 'r2wb_secret_access_key', '' );
		if ( strpos( (string) $stored, 'r2wb_enc:' ) === 0 ) {
			return self::decrypt( substr( $stored, 9 ) );
		}
		return (string) $stored;
	}

	/**
	 * Encrypt and store secret key.
	 *
	 * @param string $secret Plain secret.
	 */
	public static function set_secret_key( $secret ) {
		if ( (string) $secret === '' ) {
			return;
		}
		$encrypted = 'r2wb_enc:' . self::encrypt( $secret );
		update_option( 'r2wb_secret_access_key', $encrypted );
	}

	/**
	 * Simple encryption using wp_salt.
	 *
	 * @param string $value Plain value.
	 * @return string Base64-encoded encrypted string.
	 */
	private static function encrypt( $value ) {
		$key = wp_salt( 'auth' );
		$iv  = openssl_random_pseudo_bytes( 16 );
		$enc = openssl_encrypt( $value, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv );
		return base64_encode( $iv . $enc );
	}

	/**
	 * Decrypt value.
	 *
	 * @param string $encoded Base64-encoded encrypted string.
	 * @return string
	 */
	private static function decrypt( $encoded ) {
		$key  = wp_salt( 'auth' );
		$raw  = base64_decode( $encoded, true );
		if ( $raw === false || strlen( $raw ) < 17 ) {
			return '';
		}
		$iv  = substr( $raw, 0, 16 );
		$enc = substr( $raw, 16 );
		$dec = openssl_decrypt( $enc, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv );
		return $dec !== false ? $dec : '';
	}
}
