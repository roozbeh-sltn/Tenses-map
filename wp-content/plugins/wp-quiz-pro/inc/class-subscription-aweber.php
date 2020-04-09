<?php
/**
 * Aweber Subscription
 */
class WP_Quiz_Pro_Subscription_Aweber {

	/**
	 * Aweber Credentials
	 * @var mixed
	 */
	public $credentials;

	/**
	 * API Key
	 * @var string
	 */
	public $api_key;

	/**
	 * Credential option key
	 * @return string
	 */
	private $key = 'wp_quiz_pro_default_settings';

	public function init() {

		if ( ! class_exists( 'AWeberAPI' ) ) {
			require_once wp_quiz_pro()->plugin_dir() . 'vendor/aweber_api/aweber.php';
		}

		$credentials = $this->get_credentials();

		if ( empty( $credentials['consumer_key'] ) || empty( $credentials['consumer_secret'] ) ) {
			throw new Exception( 'Aweber is not connected.' );
		}

		if ( empty( $credentials['account_id'] ) ) {
			throw new Exception( 'The Aweber Account ID is not set.' );
		}

		$api = new AWeberAPI( $credentials['consumer_key'], $credentials['consumer_secret'] );

		return $api;
	}

	public function get_credentials() {

		if ( ! empty( $this->credentials ) ) {
			return $this->credentials;
		}

		$credentials = array_filter( $credentials );

		if ( empty( $credentials ) ) {
			$credentials = get_option( $this->key );
			$credentials = isset( $credentials['aweber'] ) ? $credentials['aweber'] : null;
		}

		$this->credentials = empty( $credentials ) ? null : $credentials;

		return $this->credentials;
	}

	public function connect( $api_key = '' ) {

		// if the auth code is empty, show the error
		if ( empty( $api_key ) ) {
			throw new Exception( esc_html__( 'Unable to connect to Aweber. The Authorization Code is empty.', 'wp-quiz-pro' ) );
		}

		if ( ! class_exists( 'AWeberAPI' ) ) {
			require_once wp_quiz_pro()->plugin_dir() . 'vendor/aweber_api/aweber.php';
		}

		list( $consumer_key, $consumer_secret, $access_key, $access_secret ) = AWeberAPI::getDataFromAweberID( $api_key );

		if ( empty( $consumer_key ) || empty( $consumer_secret ) || empty( $access_key ) || empty( $access_secret ) ) {
			throw new Exception( esc_html__( 'Unable to connect your Aweber Account. The Authorization Code is incorrect.', 'wp-quiz-pro' ) );
		}

		$aweber = new AWeberAPI( $consumer_key, $consumer_secret );
		$account = $aweber->getAccount( $access_key, $access_secret );

		$credentials = array(
			'consumer_key' => $consumer_key,
			'consumer_secret' => $consumer_secret,
			'access_key' => $access_key,
			'access_secret' => $access_secret,
			'account_id' => $account->id,
			'listid' => '',
		);

		$settings = get_option( $this->key );
		$settings['aweber'] = $credentials;

		update_option( $this->key, $settings );

		return $credentials;
	}

	public function get_account() {

		$aweber = $this->init();
		$credentials = $this->get_credentials();

		if ( empty( $credentials['access_key'] ) || empty( $credentials['access_secret'] ) ) {
			throw new Exception( '[init]: Aweber is not connected.' );
		}

		return $aweber->getAccount( $credentials['access_key'], $credentials['access_secret'] );
	}

	public function subscribe( $name, $email, $list_id ) {

		try {
			$account = $this->get_account();

			$list_url = "/accounts/{$account->id}/lists/{$list_id}/subscribers";
			$list = $account->loadFromUrl( $list_url );

			$params = array(
				'name' => $name,
				'email' => $email,
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'ad_tracking' => 'mythemeshop',
			);

			$list->create( $params );

			return array( 'status' => 'subscribed' );
		} catch ( Exception $e ) {

			// already waiting confirmation:
			// "Subscriber already subscribed and has not confirmed."
			if ( strpos( $e->getMessage(), 'has not confirmed' ) ) {
				return array( 'status' => 'pending' );
			}

			// already waiting confirmation:
			// "Subscriber already subscribed."
			if ( strpos( $e->getMessage(), 'already subscribed' ) ) {
				return array( 'status' => 'pending' );
			}

			throw new Exception( '[subscribe]: ' . $e->getMessage() );
		}
	}
}
