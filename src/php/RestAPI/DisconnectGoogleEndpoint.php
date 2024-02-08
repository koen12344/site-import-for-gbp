<?php

namespace Koen12344\SiteImportForGbp\RestAPI;

use Koen12344\SiteImportForGbp\GoogleUserManager;
use WP_REST_Request;

class DisconnectGoogleEndpoint implements EndpointInterface {

	/**
	 * @var GoogleUserManager
	 */
	private $user_manager;

	public function __construct(GoogleUserManager $user_manager){

		$this->user_manager = $user_manager;
	}
	public function get_arguments(): array {
		return [];
	}

	public function respond( WP_REST_Request $request ) {
		$user_id = key($this->user_manager->get_accounts());

		$this->user_manager->delete_account($user_id);

		return new \WP_REST_Response(true);
	}

	public function validate( WP_REST_Request $request ): bool {
		return current_user_can('manage_options');
	}

	public function get_methods(): array {
		return [ \WP_REST_Server::DELETABLE];
	}

	public function get_path(): string {
		return '/account/';
	}
}
