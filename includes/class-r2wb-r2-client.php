<?php
/**
 * R2 / S3 API client for Cloudflare R2.
 *
 * @package R2_WordPress_Backup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class R2WB_R2_Client
 */
class R2WB_R2_Client {

	/**
	 * Backup prefix in bucket (site-specific).
	 *
	 * @var string
	 */
	private $prefix;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->prefix = 'backups/' . self::get_site_slug() . '/';
	}

	/**
	 * Get site slug for backup path.
	 *
	 * @return string
	 */
	private static function get_site_slug() {
		$url = home_url( '', 'http' );
		$parsed = wp_parse_url( $url );
		$host = isset( $parsed['host'] ) ? $parsed['host'] : 'site';
		$host = preg_replace( '/[^a-z0-9\-]/', '-', strtolower( $host ) );
		return $host ?: 'site';
	}

	/**
	 * Get config from options.
	 *
	 * @return array{account_id:string, access_key:string, secret_key:string, bucket:string}|null
	 */
	private function get_config() {
		$account_id = get_option( 'r2wb_account_id', '' );
		$access_key = get_option( 'r2wb_access_key_id', '' );
		$secret_key = R2WB_Credentials::get_secret_key();
		$bucket     = get_option( 'r2wb_bucket', '' );

		if ( $account_id === '' || $access_key === '' || $secret_key === '' || $bucket === '' ) {
			return null;
		}
		return array(
			'account_id' => $account_id,
			'access_key' => $access_key,
			'secret_key' => $secret_key,
			'bucket'     => $bucket,
		);
	}

	/**
	 * Base URL for R2 (path-style).
	 *
	 * @param array $config Config from get_config().
	 * @return string
	 */
	private function get_base_url( $config ) {
		return 'https://' . $config['account_id'] . '.r2.cloudflarestorage.com';
	}

	/**
	 * Perform signed request.
	 *
	 * @param string $method  GET, PUT, DELETE.
	 * @param string $path    Path including bucket, e.g. /bucket/key.
	 * @param string $query   Query string.
	 * @param string $body    Request body (PUT).
	 * @param array  $config  Config array.
	 * @return array{code:int, body:string, headers:array}
	 */
	private function request( $method, $path, $query = '', $body = '', $config = null ) {
		if ( $config === null ) {
			$config = $this->get_config();
		}
		if ( $config === null ) {
			return array( 'code' => 0, 'body' => '', 'headers' => array() );
		}

		$host = $config['account_id'] . '.r2.cloudflarestorage.com';
		$headers = array(
			'content-type' => ( $method === 'PUT' && $body !== '' ) ? 'application/octet-stream' : '',
		);
		$headers = array_filter( $headers );

		$headers = R2WB_S3_Signer::sign(
			$method,
			$host,
			$path,
			$query,
			$headers,
			$body,
			$config['access_key'],
			$config['secret_key'],
			'auto'
		);

		$url = $this->get_base_url( $config ) . $path;
		if ( $query !== '' ) {
			$url .= '?' . $query;
		}

		$args = array(
			'method'  => $method,
			'headers' => $headers,
			'timeout' => 60,
		);
		if ( $body !== '' && $method === 'PUT' ) {
			$args['body'] = $body;
		}

		$response = wp_remote_request( $url, $args );
		$code = wp_remote_retrieve_response_code( $response );
		$body_res = wp_remote_retrieve_body( $response );
		$headers_res = wp_remote_retrieve_headers( $response );

		return array(
			'code'    => (int) $code,
			'body'   => $body_res,
			'headers' => is_object( $headers_res ) ? (array) $headers_res : $headers_res,
		);
	}

	/**
	 * List backup keys (objects under prefix).
	 *
	 * @return array List of keys (full key names).
	 */
	public function list_backups() {
		$config = $this->get_config();
		if ( $config === null ) {
			return array();
		}
		$bucket = $config['bucket'];
		$path = '/' . $bucket;
		$query = 'list-type=2&prefix=' . rawurlencode( $this->prefix ) . '&max-keys=1000';
		$all = array();
		$continuation = '';

		do {
			if ( $continuation !== '' ) {
				$query .= '&continuation-token=' . rawurlencode( $continuation );
			}
			$res = $this->request( 'GET', $path, $query, '', $config );
			if ( $res['code'] !== 200 ) {
				return $all;
			}
			$xml = @simplexml_load_string( $res['body'] );
			if ( $xml === false ) {
				return $all;
			}
			$xml->registerXPathNamespace( 's3', 'http://s3.amazonaws.com/doc/2006-03-01/' );
			$contents = $xml->xpath( '//s3:Contents/s3:Key' );
			if ( is_array( $contents ) ) {
				foreach ( $contents as $key_node ) {
					$all[] = (string) $key_node;
				}
			}
			$truncated = $xml->xpath( '//s3:IsTruncated' );
			$next = $xml->xpath( '//s3:NextContinuationToken' );
			$continuation = ( is_array( $next ) && isset( $next[0] ) ) ? (string) $next[0] : '';
		} while ( ( is_array( $truncated ) && isset( $truncated[0] ) && (string) $truncated[0] === 'true' ) && $continuation !== '' );

		return $all;
	}

	/**
	 * Number of backups in R2.
	 *
	 * @return int
	 */
	public function list_backups_count() {
		return count( $this->list_backups() );
	}

	/**
	 * Test connection: list with max-keys=1.
	 *
	 * @return true|WP_Error
	 */
	public function test_connection() {
		$config = $this->get_config();
		if ( $config === null ) {
			return new WP_Error( 'r2wb_not_configured', __( 'R2 credentials not configured. Save Account ID, Access Key, Secret Key, and Bucket.', 'r2-wordpress-backup' ) );
		}
		$bucket = $config['bucket'];
		$path = '/' . $bucket;
		$query = 'list-type=2&max-keys=1';
		$res = $this->request( 'GET', $path, $query, '', $config );
		if ( $res['code'] === 200 ) {
			return true;
		}
		if ( $res['code'] === 403 ) {
			return new WP_Error( 'r2wb_access_denied', __( 'Access denied. Check your R2 API token and bucket name.', 'r2-wordpress-backup' ) );
		}
		if ( $res['code'] === 404 ) {
			return new WP_Error( 'r2wb_bucket_not_found', __( 'Bucket not found.', 'r2-wordpress-backup' ) );
		}
		$msg = wp_remote_retrieve_response_message( array( 'code' => $res['code'], 'body' => $res['body'] ) );
		return new WP_Error( 'r2wb_connection_failed', sprintf( __( 'Connection failed (HTTP %d).', 'r2-wordpress-backup' ), $res['code'] ) );
	}

	/**
	 * Upload a file to R2.
	 *
	 * @param string $local_path  Full path to local file.
	 * @param string $remote_key  Key (path) in bucket.
	 * @return true|WP_Error
	 */
	public function upload( $local_path, $remote_key ) {
		if ( ! is_readable( $local_path ) ) {
			return new WP_Error( 'r2wb_file_unreadable', __( 'File not found or not readable.', 'r2-wordpress-backup' ) );
		}
		$body = file_get_contents( $local_path );
		if ( $body === false ) {
			return new WP_Error( 'r2wb_file_read_failed', __( 'Failed to read file.', 'r2-wordpress-backup' ) );
		}
		$config = $this->get_config();
		if ( $config === null ) {
			return new WP_Error( 'r2wb_not_configured', __( 'R2 credentials not configured.', 'r2-wordpress-backup' ) );
		}
		$bucket = $config['bucket'];
		$path = '/' . $bucket . '/' . self::encode_key( $remote_key );
		$res = $this->request( 'PUT', $path, '', $body, $config );
		if ( $res['code'] === 200 ) {
			return true;
		}
		return new WP_Error( 'r2wb_upload_failed', sprintf( __( 'Upload failed (HTTP %d).', 'r2-wordpress-backup' ), $res['code'] ) );
	}

	/**
	 * Download a file from R2.
	 *
	 * @param string $remote_key Key in bucket.
	 * @param string $local_path Full path to save file.
	 * @return true|WP_Error
	 */
	public function download( $remote_key, $local_path ) {
		if ( strpos( $remote_key, $this->prefix ) !== 0 ) {
			return new WP_Error( 'r2wb_invalid_key', __( 'Invalid backup key.', 'r2-wordpress-backup' ) );
		}
		$config = $this->get_config();
		if ( $config === null ) {
			return new WP_Error( 'r2wb_not_configured', __( 'R2 credentials not configured.', 'r2-wordpress-backup' ) );
		}
		$bucket = $config['bucket'];
		$path = '/' . $bucket . '/' . self::encode_key( $remote_key );
		$res = $this->request( 'GET', $path, '', '', $config );
		if ( $res['code'] !== 200 ) {
			return new WP_Error( 'r2wb_download_failed', sprintf( __( 'Download failed (HTTP %d).', 'r2-wordpress-backup' ), $res['code'] ) );
		}
		if ( file_put_contents( $local_path, $res['body'] ) === false ) {
			return new WP_Error( 'r2wb_write_failed', __( 'Failed to write file.', 'r2-wordpress-backup' ) );
		}
		return true;
	}

	/**
	 * Delete an object from R2.
	 *
	 * @param string $remote_key Key in bucket.
	 * @return true|WP_Error
	 */
	public function delete( $remote_key ) {
		if ( strpos( $remote_key, $this->prefix ) !== 0 ) {
			return new WP_Error( 'r2wb_invalid_key', __( 'Invalid backup key.', 'r2-wordpress-backup' ) );
		}
		$config = $this->get_config();
		if ( $config === null ) {
			return new WP_Error( 'r2wb_not_configured', __( 'R2 credentials not configured.', 'r2-wordpress-backup' ) );
		}
		$bucket = $config['bucket'];
		$path = '/' . $bucket . '/' . self::encode_key( $remote_key );
		$res = $this->request( 'DELETE', $path, '', '', $config );
		if ( $res['code'] === 204 || $res['code'] === 200 ) {
			return true;
		}
		return new WP_Error( 'r2wb_delete_failed', sprintf( __( 'Delete failed (HTTP %d).', 'r2-wordpress-backup' ), $res['code'] ) );
	}

	/**
	 * Get backup prefix for this site (for Backup_Engine).
	 *
	 * @return string
	 */
	public function get_prefix() {
		return $this->prefix;
	}

	/**
	 * Encode key for URI path (each segment).
	 *
	 * @param string $key Object key.
	 * @return string
	 */
	private static function encode_key( $key ) {
		$parts = explode( '/', $key );
		return implode( '/', array_map( 'rawurlencode', $parts ) );
	}
}
