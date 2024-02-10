<?php

namespace Koen12344\SiteImportForGbp\RestAPI;

use Koen12344\SiteImportForGbp\Logger\ImportLogger;
use WP_REST_Request;

class GetImportLogEndpoint implements EndpointInterface {

	/**
	 * @var ImportLogger
	 */
	private $logger;

	public function __construct(ImportLogger $logger){

		$this->logger = $logger;
	}
	public function get_arguments(): array {
		return [];
	}

	public function respond( WP_REST_Request $request ) {
		return new \WP_REST_Response(['log' => esc_html($this->logger->read())]);
	}

	public function validate( WP_REST_Request $request ): bool {
		return current_user_can('manage_options');
	}

	public function get_methods(): array {
		return [\WP_REST_Server::READABLE];
	}

	public function get_path(): string {
		return '/import/log/';
	}
}
