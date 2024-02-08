<?php


namespace Koen12344\SiteImportForGbp\API;


use Exception;

use UnexpectedValueException;

class ProxyAuthenticationAPI {

	const AUTH_API_URL = 'https://app.posttogmb.com/';

	/**
	 * @var \WP_Http
	 */
	private $http;
	private $plugin_version;
	private $plugin_slug;

	public function __construct(\WP_Http $http, $plugin_version, $plugin_slug){
		$this->http = $http;
		$this->plugin_version = $plugin_version;
		$this->plugin_slug = $plugin_slug;
	}

	protected function default_args(): array {
		return [
			'plugin_name'       => $this->plugin_slug,
			'plugin_version'    => $this->plugin_version,
		];
	}

	/**
	 * @throws Exception
	 */
	protected function do_request($url, $args, $method = 'POST'){
		$response = $this->http->post($url, [
			'body'  => wp_parse_args($args, $this->default_args()),
			'timeout' => 20,
			'method'  => $method
		]);

		if($response instanceof \WP_Error){
			throw new Exception($response->get_error_message());
		}

		$response_code = wp_remote_retrieve_response_code($response);
		if($response_code !== 200 && $response_code !== 201){
			throw new Exception($response['body']);
		}

		$data = json_decode($response['body']);
		if(!$data){
			throw new UnexpectedValueException(__('Could not parse JSON response from authentication server.', 'site-import-for-gbp'));
		}

		if(!isset($data->error)){
			return $data;
		}elseif(is_object($data->error)) {
			throw new GoogleAPIError( $data );
		}else{
			throw new UnexpectedValueException((string)$data->error);
		}
	}


	/**
	 * @throws Exception
	 */
	public function get_authentication_url($return_url, $nonce){
		return $this->do_request( self::AUTH_API_URL . 'getlink', [
			'request_key'   => $nonce,
			'post_url'      => $return_url,
		]);
	}

	public function get_tokens_from_code($code){
		return $this->do_request(self::AUTH_API_URL.'gettoken', [
			'code'  => $code,
		]);
	}

	public function access_token_from_refresh_token($refresh_token){
		if(empty($refresh_token)) { throw new \InvalidArgumentException('Refresh token is empty. Is the Google account associated to the selected location(s) still connected to this website?'); }
		return $this->do_request(self::AUTH_API_URL.'refreshtoken',[
			'refresh_token' => $refresh_token,
		]);
	}

	public function revoke_refresh_token($refresh_token){
		return $this->do_request(self::AUTH_API_URL.'revoketoken',[
			'refresh_token' => $refresh_token,
		], 'DELETE');
	}

	public function get_access_token($user_id){
		$token = get_transient("sifg_access_token-{$user_id}");
		if($token){
			return $token;
		}

		$new_token = $this->access_token_from_refresh_token($this->get_refresh_token($user_id));

		$this->set_access_token($user_id, $new_token->access_token, $new_token->expires_in - 20);

		return $new_token->access_token;
	}

	public function set_access_token($user_id, $access_token, $expiry){
		set_transient("sifg_access_token-{$user_id}", $access_token, $expiry);
	}

	public function clear_access_token_cache($user_id){
		delete_transient("sifg_access_token-{$user_id}");
	}

	private function get_refresh_token($user_id){
		$accounts = get_option('sifg_accounts');

		return $accounts[$user_id]['refresh_token'];
	}

}
