<?php


namespace Koen12344\SiteImportForGbp\API;


class CachedGoogleMyBusiness extends ProxyGMBAPI {


	private $user_id;

	private $auth_api;

	public function __construct( \WP_Http $transport, ProxyAuthenticationAPI $auth_api) {
		parent::__construct( $transport );

		$this->auth_api = $auth_api;
	}

	public function set_user_id($user_id){
		$this->user_id = $user_id;
		parent::set_access_token($this->auth_api->get_access_token($user_id));
	}



	public function list_accounts( $flush = false, $pageSize = 20, $pageToken = '', $filter = '', $parentAccount = '' ) {
		$transient_name = "sifg_list_accounts-{$this->user_id}-" . md5(serialize([ $parentAccount, $pageSize, $pageToken, $filter ]));
		if(!$flush && $cached = get_transient($transient_name)){
			return $cached;
		}

		$request = parent::list_accounts( $parentAccount, $pageSize, $pageToken, $filter );
		set_transient($transient_name, $request, WEEK_IN_SECONDS);
		return $request;
	}


	public function list_locations( $parent, $pageSize = 100, $pageToken = '', $filter = '', $orderBy = '', $readMask = '', $flush = false ) {
		$transient_name = "sifg_list_locations-{$this->user_id}-" . md5(serialize([$parent, $pageSize, $pageToken, $filter, $orderBy, $readMask]));
		if(!$flush && $cached = get_transient($transient_name)){
			return $cached;
		}

		$request = parent::list_locations( $parent, $pageSize, $pageToken, $filter, $orderBy, $readMask );
		set_transient($transient_name, $request, WEEK_IN_SECONDS);
		return $request;
	}

	public function get_location( $name, $readMask = '', $flush = false ) {
		$transient_name = "sifg_location-".md5(serialize([$name, $readMask]));
		if(!$flush && $cached = get_transient($transient_name)){
			return $cached;
		}
		$request = parent::get_location( $name, $readMask );
		set_transient($transient_name, $request, WEEK_IN_SECONDS);
		return $request;
	}

	public function get_account($name, $flush = false){
		$transient_name = 'sifg_account-'.md5($name);
		if(!$flush && $cached = get_transient($transient_name)){
			return $cached;
		}
		$request = parent::get_account($name);
		set_transient($transient_name, $request, WEEK_IN_SECONDS);
		return $request;
	}


}
