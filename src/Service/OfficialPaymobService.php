<?php
declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Service;

use Exception;
class OfficialPaymobService {


	public $debug_order;
	public $file;

	public function __construct( $debug_order = false, $file = null ) {
		$this->debug_order = $debug_order;
		$this->file        = $file;
	}

	public function HttpRequest( $apiPath, $method, $header = array(), $data = array() ) {
		if ( ! in_array( 'curl', get_loaded_extensions() ) ) {
			throw new Exception( 'Curl extension is not loaded on your server, please check with server admin. Then try again!' );
		}

		ini_set( 'precision', 14 );
		ini_set( 'serialize_precision', -1 );
		$curl = curl_init();

		curl_setopt( $curl, CURLOPT_URL, $apiPath );
		if ( 'GET' == $method ) {
			curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'GET' );
		} else {
			curl_setopt( $curl, CURLOPT_POST, true );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( $data ) );
		}
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

		$response = curl_exec( $curl );

		if ( false === $response ) {
			throw new Exception( 'Curl error: ' . curl_error( $curl ) );
		}
		curl_close( $curl );

		return json_decode( $response, false );
	}

	public function authToken( $conf ) {
		$this->matchCountries( $conf );
		$this->addLogs( $this->debug_order, $this->file, ' Authenticate Paymob configuration' );
		$apiUrl = $this->getApiUrl( $this->getCountryCode( $conf['secKey'] ) );

		$tokenRes = $this->HttpRequest( $apiUrl . 'api/auth/tokens', 'POST', array( 'Content-Type: application/json' ), array( 'api_key' => $conf['apiKey'] ) );
		$this->addLogs( $this->debug_order, $this->file, ' In api/auth/tokens Response: ' . json_encode( $tokenRes ) );

		if ( isset( $tokenRes->token ) ) {
			$hmacRes     = $this->getHmac( $tokenRes->token, $apiUrl );
			$integIDsRes = $this->getIntegrationIDs( $tokenRes->token, $apiUrl, $this->matchMode( $conf ) );
			$data        = array(
				'hmac'           => $hmacRes,
				'integrationIDs' => $integIDsRes,
			);
			return $data;
		} else {
			throw new Exception( 'Cannot get Token from PayMob account' );
		}
	}

	public function getHmac( $token, $apiUrl ) {
		$hmacRes = $this->HttpRequest( $apiUrl . 'api/auth/hmac_secret/get_hmac', 'GET', array( 'Content-Type: application/json', 'Authorization: Bearer ' . $token ) );
		$this->addLogs( $this->debug_order, $this->file, ' In api/auth/hmac_secret/get_hmac Response: ' . json_encode( $hmacRes ) );
		if ( isset( $hmacRes->hmac_secret ) ) {
			return $hmacRes->hmac_secret;
		} else {
			throw new Exception( 'Cannot get HMAC from PayMob account' );
		}
	}

	public function getIntegrationIDs( $token, $apiUrl, $isTest = false ) {
		$intRes = $this->HttpRequest( $apiUrl . 'api/ecommerce/integrations?is_plugin=true&is_next=yes&page_size=500&is_deprecated=false&is_standalone=false', 'GET', array( 'Content-Type: application/json', 'Authorization: Bearer ' . $token ) );
		$this->addLogs( $this->debug_order, $this->file, ' In api/ecommerce/integrations Response: ' . json_encode( $intRes ) );

		if ( ! empty( $intRes ) ) {
			$IntegrationIDs = array();
			foreach ( $intRes->results as $key => $integration ) {
				$type = $integration->gateway_type;
				if ( 'VPC' == $type ) {
					$type = 'Card';
				} elseif ( 'CAGG' == $type ) {
					$type = 'Aman';
				} elseif ( 'UIG' == $type ) {
					$type = 'Wallet';
				}
				if ( false == $integration->is_standalone && $integration->is_live == $isTest ) {

					$IntegrationIDs[ $integration->id ] = array(
						'id'           => $integration->id,
						'type'         => $type,
						'gateway_type' => $integration->gateway_type,
						'name'         => empty( $integration->installments ) ? $integration->integration_name : 'bank-installments',
						'currency'     => $integration->currency,
					);
				}
			}

			return $IntegrationIDs;
		} else {
			throw new Exception( 'Cannot get available integration IDs from PayMob account' );
		}
	}

	public function createIntention( $secKey, $data, $orderId ) {
		$flash  = $this->getApiUrl( $this->getCountryCode( $secKey ) );
		$header = array( 'Content-Type: application/json', 'Authorization: Token ' . $secKey );
		$this->addLogs( $this->debug_order, $this->file, print_r( $data, true ) );
		$intention = $this->HttpRequest( $flash . 'v1/intention/', 'POST', $header, $data );
		$note_i    = 'Intention response for order # ' . $orderId;
		$this->addLogs( $this->debug_order, $this->file, $note_i, print_r( $intention, true ) );
		if ( empty( $intention->payment_keys ) ) {
			$this->addLogs( $this->debug_order, $this->file, $note_i, $intention );
		}
		$status = array(
			'cs'      => null,
			'success' => false,
		);

		if ( isset( $intention->detail ) ) {
			$status['message'] = $intention->detail;
			return $status;
		}

		if ( isset( $intention->amount ) ) {
			$status['message'] = $intention->amount[0];
			return $status;
		}
		if ( isset( $intention->billing_data ) ) {
			$status['message'] = 'Ops, there is missing billing information!';
			return $status;
		}

		if ( isset( $intention->integrations ) ) {
			$status['message'] = $intention->integrations[0];
			return $status;
		}

		if ( isset( $intention->client_secret ) ) {
			$status['success']     = true;
			$status['cs']          = $intention->client_secret;
			$status['intentionId'] = $intention->id;
			$status['centsAmount'] = $intention->intention_detail->amount;
		} else {
			$status['message'] = ( isset( $intention->code ) ) ? $intention->code : 'Something went wrong';
		}
		$this->addLogs( $this->debug_order, $this->file, $note_i, json_encode( $status ) );
		return $status;
	}
	/**
	 * Get a list of Paymob Gateways, their Logos, and names.
	 *
	 * @return array of Paymob data
	 */
	public function getPaymobGateways( $secKey, $path ) {
		// get gateways data
		$flash  = $this->getApiUrl( $this->getCountryCode( $secKey ) );
		$header = array( 'Content-Type: application/json', 'Authorization: Token ' . $secKey );

		$getways = $this->HttpRequest( $flash . 'api/ecommerce/gateways', 'GET', $header );
		$this->addLogs( $this->debug_order, $this->file, 'In api/ecommerce/gateways Response: ', json_encode( $getways ) );
		// Handle invalid or empty responses
		if ( is_null( $getways ) || ! isset( $getways->result ) ) {
			$this->addLogs( $this->debug_order, $this->file, 'In api/ecommerce/gateways Response: Cannot get Gateways Data from PayMob account.' );
			throw new Exception( 'Cannot get Gateways Data from PayMob account' );
		}

		// Process the gateways data if available
		$gateways = json_decode( json_encode( $getways->result, true ), true );
		return $this->extractGatewaysData( $gateways, $path );
	}

	public static function extractGatewaysData( $gateways, $path ) {
		$gatewaysData = array();

		// Check if $gateways is an array or object before processing
		if ( ! is_array( $gateways ) && ! is_object( $gateways ) ) {
			return $gatewaysData; // Return empty data if $gateways is not valid
		}

		foreach ( $gateways as $gateway ) {
			$logoPath = $path . strtolower( $gateway['code'] ) . '.png';
			// Skip downloading the logo if the logo URL is empty
			if ( ! empty( $gateway['logo'] ) ) {
				if ( ! file_exists( $logoPath ) ) {
					$ch = curl_init();
					curl_setopt( $ch, CURLOPT_HEADER, 0 );
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
					curl_setopt( $ch, CURLOPT_URL, $gateway['logo'] );
					$data = curl_exec( $ch );
					curl_close( $ch );
					file_put_contents( $logoPath, $data );
				}
			} else {
				// If logo URL is empty, use a placeholder or skip the download
				$logoPath = ''; // Leave it empty if no logo is available
			}

			$gatewaysData[ strtolower( $gateway['code'] ) ] = array(
				'title' => $gateway['label'],
				'desc'  => $gateway['description'],
				'logo'  => $gateway['logo'],
			);
		}

		return $gatewaysData;
	}
	public function registerFramework( $secKey, $data ) {
		$flash       = $this->getApiUrl( $this->getCountryCode( $secKey ) );
		$header      = array( 'Content-Type: application/json', 'Authorization: Token ' . $secKey );
		$registerRes = $this->HttpRequest( $flash . 'api/ecommerce/plugins', 'POST', $header, $data );
		$this->addLogs( $this->debug_order, $this->file, ' In api/ecommerce/plugins: ' . json_encode( $registerRes ) );
		return $registerRes;
	}
	public function refundPayment( $secKey, $data ) {
		$flash  = $this->getApiUrl( $this->getCountryCode( $secKey ) );
		$header = array( 'Content-Type: application/json', 'Authorization: Token ' . $secKey );
		$this->addLogs( $this->debug_order, $this->file, print_r( $data, true ) );
		$refundRes = $this->HttpRequest( $flash . 'api/acceptance/void_refund/refund', 'POST', $header, $data );
		$this->addLogs( $this->debug_order, $this->file, ' In api/acceptance/void_refund/refund: ' . json_encode( $refundRes ) );

		if ( isset( $refundRes->detail ) ) {
			$status['message'] = $refundRes->detail;
			return $status;
		}
		if ( isset( $refundRes->message ) ) {
			$status['message'] = $refundRes->message;
			return $status;
		}
		if ( isset( $refundRes->success ) ) {
			$status['success']   = true;
			$status['refund_id'] = $refundRes->id;
		} else {
			$status['success'] = false;
			$status['message'] = 'Something went wrong';
		}
		return $status;
	}
	public function matchMode( $conf ) {
		$pubKeyMode = $this->getMode( $conf['pubKey'] );
		$secKeyMode = $this->getMode( $conf['secKey'] );

		if ( $secKeyMode != $pubKeyMode ) {
			throw new Exception( 'Public and Secret Keys does not belong to the ( live/test ) mode' );
		}
		return ( 'live' == $pubKeyMode );
	}

	public function matchCountries( $conf ) {
		$pubKey = $this->getCountryCode( $conf['pubKey'] );
		$secKey = $this->getCountryCode( $conf['secKey'] );
		if ( $pubKey != $secKey ) {
			throw new Exception( 'Public and Secret Keys does not belong to the same country' );
		}
		return true;
	}

	public function getMode( $code ) {
		return substr( $code, 7, 4 );
	}

	public static function getCountryCode( $code ) {
		return substr( $code, 0, 3 );
	}

	public static function getIntentionId( $merchantIntentionId ) {
		return substr( $merchantIntentionId, 0, -11 );
	}

	public static function verifyHmac( $key, $data, $intention = null, $hmac = null ) {
		if ( isset( $hmac ) ) {
			return self::verifyAcceptHmac( $key, $data, $hmac );
		} else {
			return self::verifyFlashHmac( $key, $data, $intention );
		}
	}

	public static function verifyFlashHmac( $key, $data, $intention = null ) {

		if ( empty( $intention ) ) {
			// callback GET
			$str  = $data['amount_cents']
				. $data['created_at']
				. $data['currency']
				. $data['error_occured']
				. $data['has_parent_transaction']
				. $data['id']
				. $data['integration_id']
				. $data['is_3d_secure']
				. $data['is_auth']
				. $data['is_capture']
				. $data['is_refunded']
				. $data['is_standalone_payment']
				. $data['is_voided']
				. $data['order']
				. $data['owner']
				. $data['pending']
				. $data['source_data_pan']
				. $data['source_data_sub_type']
				. $data['source_data_type']
				. $data['success'];
			$hash = hash_hmac( 'sha512', $str, $key );
		} else {
			// webhook POST
			$amount = ( $intention['amount'] / $intention['cents'] );
			if ( is_float( $amount ) ) {
				$amountArr = explode( '.', $amount );
				if ( strlen( $amountArr[1] ) == 1 ) {
					$amount = $amount . '0';
				}
			} else {
				$amount = $amount . '.00';
			}
			$str  = $amount . $intention['id'];
			$hash = hash_hmac( 'sha512', $str, $key, false );
		}

		$hmac = $data['hmac'];

		return ( $hmac === $hash );
	}

	public static function verifyAcceptHmac( $key, $json_data, $hmac ) {
		$data                           = $json_data['obj'];
		$data['order']                  = $data['order']['id'];
		$data['is_3d_secure']           = ( true === $data['is_3d_secure'] ) ? 'true' : 'false';
		$data['is_auth']                = ( true === $data['is_auth'] ) ? 'true' : 'false';
		$data['is_capture']             = ( true === $data['is_capture'] ) ? 'true' : 'false';
		$data['is_refunded']            = ( true === $data['is_refunded'] ) ? 'true' : 'false';
		$data['is_standalone_payment']  = ( true === $data['is_standalone_payment'] ) ? 'true' : 'false';
		$data['is_voided']              = ( true === $data['is_voided'] ) ? 'true' : 'false';
		$data['success']                = ( true === $data['success'] ) ? 'true' : 'false';
		$data['error_occured']          = ( true === $data['error_occured'] ) ? 'true' : 'false';
		$data['has_parent_transaction'] = ( true === $data['has_parent_transaction'] ) ? 'true' : 'false';
		$data['pending']                = ( true === $data['pending'] ) ? 'true' : 'false';
		$data['source_data_pan']        = $data['source_data']['pan'];
		$data['source_data_type']       = $data['source_data']['type'];
		$data['source_data_sub_type']   = $data['source_data']['sub_type'];

		$str  = '';
		$str  = $data['amount_cents'] .
			$data['created_at'] .
			$data['currency'] .
			$data['error_occured'] .
			$data['has_parent_transaction'] .
			$data['id'] .
			$data['integration_id'] .
			$data['is_3d_secure'] .
			$data['is_auth'] .
			$data['is_capture'] .
			$data['is_refunded'] .
			$data['is_standalone_payment'] .
			$data['is_voided'] .
			$data['order'] .
			$data['owner'] .
			$data['pending'] .
			$data['source_data_pan'] .
			$data['source_data_sub_type'] .
			$data['source_data_type'] .
			$data['success'];
		$hash = hash_hmac( 'sha512', $str, $key );
		return $hash === $hmac;
	}

	public static function getApiUrl( $countryCode ) {
		$domain = 'paymob.com/';
		if ( 'are' == $countryCode || 'uae' == $countryCode ) {
			return 'https://uae.' . $domain;
		} elseif ( 'egy' == $countryCode ) {
			return 'https://accept.' . $domain;
		} elseif ( 'pak' == $countryCode ) {
			return 'https://pakistan.' . $domain;
		} elseif ( 'ksa' == $countryCode || 'sau' == $countryCode ) {
			return 'https://ksa.' . $domain;
		} elseif ( 'omn' == $countryCode ) {
			return 'https://oman.' . $domain;
		} else {
			throw new Exception( 'Another country' );
		}
	}

	public static function getTimeZone( $country ) {
		switch ( $country ) {
			case 'omn':
				return 'Asia/Muscat';
			case 'pak':
				return 'Asia/Karachi';
			case 'ksa':
			case 'sau':
				return 'Asia/Riyadh';
			case 'are':
			case 'uae':
				return 'Asia/Dubai';
			case 'egy':
			default:
				return 'Africa/Cairo';
		}
	}

	/**
	 * Filter the GLOBAL variables
	 *
	 * @param string $name The field name the need to be filter.
	 * @param string $global value could be (GET, POST, REQUEST, COOKIE, SERVER).
	 *
	 * @return string|null
	 */
	public static function filterVar( $name, $global = 'GET' ) {
		if ( isset( $GLOBALS[ '_' . $global ][ $name ] ) ) {
			if ( is_array( $GLOBALS[ '_' . $global ][ $name ] ) ) {
				return $GLOBALS[ '_' . $global ][ $name ];
			}
			return htmlspecialchars( $GLOBALS[ '_' . $global ][ $name ], ENT_QUOTES );
		}
		return null;
	}

	public static function sanitizeVar( $type = 'GET' ) {
		return $GLOBALS[ '_' . $type ];
	}

	public static function addLogs( $debug, $file, $note, $data = false ) {
		if ( is_bool( $data ) ) {
			( '1' === $debug ) ? error_log( PHP_EOL . gmdate( 'd.m.Y h:i:s' ) . ' - ' . $note, 3, $file ) : false;
		} else {
			( '1' === $debug ) ? error_log( PHP_EOL . gmdate( 'd.m.Y h:i:s' ) . ' - ' . $note . ' -- ' . json_encode( $data ), 3, $file ) : false;
		}
	}
}
