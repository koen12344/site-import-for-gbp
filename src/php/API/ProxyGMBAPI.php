<?php

namespace Koen12344\SiteImportForGbp\API;



class ProxyGMBAPI extends GoogleMyBusiness {

	protected $proxy_api_url = 'https://app.posttogmb.com/passthrough';


//	protected function get_fs_authorization_header(){
//		$license = mbp_fs()->_get_license();
//		if(!$license){ return false; }
//
//		$nonce = date('Y-m-d');
//		$pk_hash = hash('sha512', $license->secret_key.'|'.$nonce);
//		return base64_encode($pk_hash.'|'.$nonce);
//	}

	protected function do_request( $url, $query_args = [], $method = 'GET', $body = [] ) {
		$url = add_query_arg($query_args, $url);
		$passthrough_url = add_query_arg([
			'url' => urlencode($url),
//			'license_id' => isset(mbp_fs()->_get_license()->id) ? (int)mbp_fs()->_get_license()->id : false,
//			'install_id' => isset(mbp_fs()->get_site()->id) ? (int)mbp_fs()->get_site()->id : false,
		], $this->proxy_api_url);

		$headers = [
			'Content-Type' => 'application/json',
			'X-GBP-Authorization' => "Bearer {$this->access_token}",
		];
//		$fs_auth_header = $this->get_fs_authorization_header();
//		if($fs_auth_header) { $headers['X-FS-Authorization'] = $fs_auth_header; }

		$response = $this->transport->request($passthrough_url, [
			'headers'	=> $headers,
			'method'    => $method,
			'body'      => $body ? json_encode($body) : null,
			'timeout'   => 20
		]);
		return $this->handle_response($response);
	}
}
