<?php
/**
 * AWS Signature Version 4 for S3-compatible APIs (R2).
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class R2WB_S3_Signer
 */
class R2WB_S3_Signer {

	/**
	 * Sign request and return headers including Authorization.
	 *
	 * @param string $method    HTTP method (GET, PUT, etc.).
	 * @param string $host     Host (e.g. accountid.r2.cloudflarestorage.com).
	 * @param string $path     URI path (e.g. /bucket/key).
	 * @param string $query    Query string without leading ?.
	 * @param array  $headers  Existing headers (lowercase keys).
	 * @param string $body     Request body (for PUT/POST).
	 * @param string $access_key Access Key ID.
	 * @param string $secret_key Secret Access Key.
	 * @param string $region   Region (R2 uses 'auto').
	 * @return array Headers with Authorization and x-amz-date, x-amz-content-sha256.
	 */
	public static function sign( $method, $host, $path, $query, array $headers, $body, $access_key, $secret_key, $region = 'auto' ) {
		$date = gmdate( 'Ymd\THis\Z' );
		$date_short = gmdate( 'Ymd' );
		$payload_hash = ( $body === '' || $body === null ) ? 'UNSIGNED-PAYLOAD' : hash( 'sha256', $body );

		$headers['host'] = $host;
		$headers['x-amz-date'] = $date;
		$headers['x-amz-content-sha256'] = $payload_hash;

		$signed_headers = array();
		$canonical_headers = '';
		$keys = array_keys( $headers );
		sort( $keys );
		foreach ( $keys as $k ) {
			$v = $headers[ $k ];
			$canonical_headers .= strtolower( $k ) . ':' . trim( $v ) . "\n";
			$signed_headers[] = strtolower( $k );
		}
		$signed_headers_str = implode( ';', $signed_headers );

		$canonical_uri = self::canonical_uri( $path );
		$canonical_query = self::canonical_query( $query );

		$canonical_request = $method . "\n"
			. $canonical_uri . "\n"
			. $canonical_query . "\n"
			. $canonical_headers . "\n"
			. $signed_headers_str . "\n"
			. $payload_hash;

		$credential_scope = $date_short . '/' . $region . '/s3/aws4_request';
		$string_to_sign = "AWS4-HMAC-SHA256\n" . $date . "\n" . $credential_scope . "\n" . hash( 'sha256', $canonical_request );

		$k_secret = 'AWS4' . $secret_key;
		$k_date = hash_hmac( 'sha256', $date_short, $k_secret, true );
		$k_region = hash_hmac( 'sha256', $region, $k_date, true );
		$k_service = hash_hmac( 'sha256', 's3', $k_region, true );
		$k_signing = hash_hmac( 'sha256', 'aws4_request', $k_service, true );
		$signature = hash_hmac( 'sha256', $string_to_sign, $k_signing );

		$auth = 'AWS4-HMAC-SHA256 Credential=' . $access_key . '/' . $credential_scope
			. ', SignedHeaders=' . $signed_headers_str
			. ', Signature=' . $signature;

		$headers['authorization'] = $auth;
		return $headers;
	}

	/**
	 * Canonical URI: path with each segment percent-encoded.
	 *
	 * @param string $path Path.
	 * @return string
	 */
	private static function canonical_uri( $path ) {
		if ( $path === '' || $path === '/' ) {
			return '/';
		}
		$parts = explode( '/', trim( $path, '/' ) );
		$encoded = array_map( 'rawurlencode', $parts );
		return '/' . implode( '/', $encoded );
	}

	/**
	 * Canonical query string (sorted, encoded).
	 *
	 * @param string $query Query string.
	 * @return string
	 */
	private static function canonical_query( $query ) {
		if ( $query === '' ) {
			return '';
		}
		$params = array();
		parse_str( $query, $params );
		ksort( $params );
		$pairs = array();
		foreach ( $params as $k => $v ) {
			$pairs[] = rawurlencode( $k ) . '=' . rawurlencode( (string) $v );
		}
		return implode( '&', $pairs );
	}
}
