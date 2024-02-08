<?php

namespace Koen12344\SiteImportForGbp\RestAPI;

use Koen12344\SiteImportForGbp\API\CachedGoogleMyBusiness;

use Koen12344\SiteImportForGbp\GoogleUserManager;
use WP_REST_Request;

class GetGroupLocationsEndpoint implements EndpointInterface {

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
		return [
			'group_id' => [
				'required' => true,
				'type'      => 'string'
			],
			'refresh' => [
				'sanitize_callback' => 'rest_sanitize_boolean',
				'required' => false,
			],
			'nextPageToken' => [
				'required' => false,
				'type'      => 'string'
			]
		];
	}

	public function respond( WP_REST_Request $request) {
		$user_id = key($this->user_manager->get_accounts());
		$group_id = $request->get_param('group_id');

		$refresh = $request->get_param('refresh') ?? false;

		$nextPageToken = !empty($request->get_param('nextPageToken')) ? $request->get_param('nextPageToken') : null;

		$readMask = 'name,storeCode,title,storefrontAddress,metadata,serviceArea';

		try {
			$this->api->set_user_id($user_id);
			$locations = $this->api->list_locations($group_id, 10, $nextPageToken, null, null, $readMask, $refresh);
		}catch(\Exception $e){
			return new \WP_Error('rest.groups.respond', sprintf(__('Could not retrieve accounts or location groups from Google Business Profile', 'site-import-for-gbp'), $e->getMessage()));
		}

		return new \WP_REST_Response($locations);
	}

	public function validate( WP_REST_Request $request ): bool {
		return current_user_can('manage_options');
	}

	public function get_methods(): array {
		return [ \WP_REST_Server::READABLE ];
	}

	public function get_path(): string {
		return '/account/groups/locations/';
	}
}
