<?php

namespace Koen12344\SiteImportForGbp\RestAPI;

use Koen12344\SiteImportForGbp\API\CachedGoogleMyBusiness;
use Koen12344\SiteImportForGbp\GoogleUserManager;
use WP_REST_Request;

class GetGroupsEndpoint implements EndpointInterface {

	/**
	 * @var CachedGoogleMyBusiness
	 */
	private $api;
	/**
	 * @var GoogleUserManager
	 */
	private $user_manager;

	public function __construct(CachedGoogleMyBusiness $api, GoogleUserManager $user_manager){

		$this->api = $api;
		$this->user_manager = $user_manager;
	}

	public function get_arguments(): array {
		return [];
	}

	public function respond( WP_REST_Request $request ) {
		$user_id = key($this->user_manager->get_accounts());
		try {
			$this->api->set_user_id($user_id);
			$groups = $this->api->list_accounts();
		}catch(\Exception $e){
			return new \WP_Error('rest.groups.respond', sprintf(__('Could not retrieve accounts or location groups from Google Business Profile', 'site-import-for-gbp'), $e->getMessage()));
		}

		$groups = array_map(function($item){
			return [
				'label' => $item->accountName,
				'value' => $item->name,
			];
		}, $groups->accounts);

		return new \WP_REST_Response($groups);
	}

	public function validate( WP_REST_Request $request ): bool {
		return current_user_can('manage_options');
	}

	public function get_methods(): array {
		return [ \WP_REST_Server::READABLE ];
	}

	public function get_path(): string {
		return '/account/groups/';
	}
}
