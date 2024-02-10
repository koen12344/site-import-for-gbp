<?php


namespace Koen12344\SiteImportForGbp\API;

use UnexpectedValueException;
use WP_Error;

class GoogleMyBusiness {
	protected $access_token;

	protected $transport;

	public function __construct(\WP_Http $transport){
		$this->transport = $transport;
	}

	public function set_access_token($token){
		$this->access_token = $token;
	}

	protected function do_request($url, $query_args = [], $method = 'GET', $body = []){
		$url = add_query_arg($query_args, $url);
		$response = $this->transport->request($url, [
			'headers'	=> [
				'Content-Type' => 'application/json',
				'Authorization' => "Bearer {$this->access_token}"
			],
			'method'    => $method,
			'body'      => $body ? wp_json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : null,
			'timeout'   => 20
		]);
		return $this->handle_response($response);
	}

	protected function handle_response($response){
		if ($response instanceof WP_Error) {
			throw new UnexpectedValueException($response->get_error_message());
		}

		$data = json_decode($response['body']);
		if(!$data){
			throw new UnexpectedValueException(__('Could not parse JSON response from Google API.', 'site-import-for-gbp'));
		}

		if(!isset($data->error)){
			return $data;
		}elseif(is_object($data->error)) {
			throw new GoogleAPIError( $data );
		}else{
			throw new UnexpectedValueException((string)$data->error);
		}
	}

	public function list_accounts($parentAccount = '', $pageSize = 20, $pageToken = '', $filter = ''){
		return $this->do_request('https://mybusinessaccountmanagement.googleapis.com/v1/accounts', [
			'parentAccount' => $parentAccount,
			'pageSize'      => $pageSize,
			'pageToken'     => $pageToken,
			'filter'        => $filter,
		]);
	}

	public function list_locations($parent, $pageSize = 100, $pageToken = '', $filter = '', $orderBy = '', $readMask = ''){
		return $this->do_request("https://mybusinessbusinessinformation.googleapis.com/v1/{$parent}/locations", [
			'pageSize'      => $pageSize,
			'pageToken'     => $pageToken,
			'filter'        => $filter,
			'orderBy'       => $orderBy,
			'readMask'      => $readMask,
		]);
	}

	public function get_location($name, $readMask = ''){
		return $this->do_request("https://mybusinessbusinessinformation.googleapis.com/v1/{$name}", [
			'readMask'      => $readMask,
		]);
	}

	public function get_post($name){
		return $this->do_request("https://mybusiness.googleapis.com/v4/{$name}");
	}

	public function get_posts($name, $pageSize = 100, $pageToken = ''){
		return $this->do_request("https://mybusiness.googleapis.com/v4/{$name}/localPosts", [
			'pageSize'      => $pageSize,
			'pageToken'     => $pageToken,
		]);
	}

	public function list_media($name, $pageSize, $pageToken = ''){
		return $this->do_request("https://mybusiness.googleapis.com/v4/{$name}/media", [
			'pageSize'      => $pageSize,
			'pageToken'     => $pageToken,
		]);
	}

	public function get_account($name){
		return $this->do_request("https://mybusinessaccountmanagement.googleapis.com/v1/{$name}");
	}

	public function revoke_token($refresh_token){
		return $this->transport->get("https://accounts.google.com/o/oauth2/revoke?token={$refresh_token}");
	}


}
