<?php

namespace Koen12344\SiteImportForGbp;

use DateTime;
use Koen12344\SiteImportForGbp\API\ProxyAuthenticationAPI;
use SIFG\Vendor\Firebase\JWT\JWK;
use SIFG\Vendor\Firebase\JWT\JWT;


class GoogleUserManager {

	/**
	 * @var ProxyAuthenticationAPI
	 */
	private $auth_api;
	/**
	 * @var \WP_Http
	 */
	private $transport;

	public function get_public_keys() {
		$transient = get_transient('pgmb_public_keys');
		if($transient){
			return $transient;
		}

		$response = $this->transport->get('https://www.googleapis.com/oauth2/v3/certs');
		/*
		 * $response['headers']->data['expires'] = "Wed, 21 Apr 2021 19:24:14 GMT"
		 * $response['headers']->data['cache-control'] = "public, max-age=25081, must-revalidate, no-transform";
		 */
		$expires = new DateTime($response['headers']['expires']);
		$now = new DateTime();
		$expires_in_seconds = $expires->getTimestamp() - $now->getTimestamp();

		$keys = json_decode($response['body'], true);
		set_transient('pgmb_public_keys', $keys, $expires_in_seconds - 20); //Subtract 20 seconds just to be safe

		return $keys;
	}

	public function __construct(ProxyAuthenticationAPI $auth_api, \WP_Http $transport){
		$this->auth_api = $auth_api;
		$this->transport = $transport;
	}

	private function clear_tokens($account_id, $account){
		//This will actually revoke all tokens on all websites connected to the account which is not what we want
//		try {
//			$this->auth_api->revoke_refresh_token($account['refresh_token']);
//		}catch(\Exception $e){
//			error_log(sprintf('Failed to revoke access token for account ID %s: %s', $account['email'], $e->getMessage()));
//		}
		$this->auth_api->clear_access_token_cache($account_id);
	}

	public function delete_account($account_id){
		$accounts = get_option('sifg_accounts');
		if(!is_array($accounts) || !array_key_exists($account_id, $accounts)){
			return;
		}

		$this->clear_tokens($account_id, $accounts[$account_id]);

		unset($accounts[$account_id]);

		update_option('sifg_accounts', $accounts);
	}

	public function delete_all_accounts(){
		$accounts = get_option('sifg_accounts');
		if(!is_array($accounts)){
			return;
		}

		foreach($accounts as $account_id => $account){
			$this->clear_tokens($account_id, $account);
		}


		delete_option('sifg_accounts');
	}

	public function add_account($tokens){
		$keys = $this->get_public_keys();

		JWT::$leeway = 60;

		$account_data = JWT::decode( $tokens->id_token, JWK::parseKeySet( (array) $keys ) );


		$accounts = get_option('sifg_accounts');
		if(!$accounts){$accounts = [];}

		$accounts[$account_data->sub] = [
			'name'          => $account_data->name,
			'email'         => sanitize_email($account_data->email),
			'owner'         => get_current_user_id(),
			'refresh_token' => $tokens->refresh_token
		];

		update_option('sifg_accounts', $accounts);

		$this->auth_api->set_access_token($account_data->sub, $tokens->access_token, $tokens->expires_in - 20);
		/*
		 * stdClass Object
			(
			    [iss] => https://accounts.google.com
			    [azp] => 12345.apps.googleusercontent.com
			    [aud] => 12345.apps.googleusercontent.com
			    [sub] => 123456789
			    [hd] => koenreus.com
			    [email] => ik@koenreus.com
			    [email_verified] => 1
			    [at_hash] => 123456
			    [name] => Koen Reus
			    [picture] => https://lh3.googleusercontent.com/a-/123456
			    [given_name] => Koen
			    [family_name] => Reus
			    [locale] => en-GB
			    [iat] => 1619010057
			    [exp] => 1619013657
			)
		 */
		return $account_data->sub;
	}

	public function get_accounts(){
		$accounts = get_option('sifg_accounts');
		if(!is_array($accounts)){
			return false;
		}

		return $accounts;
	}

	public function get_account($account_id){
		$accounts = $this->get_accounts();
		if(empty($accounts[$account_id])){ return false;}
		return $accounts[$account_id];
	}
}
