<?php

namespace Koen12344\SiteImportForGbp\RestAPI;

use Koen12344\SiteImportForGbp\BackgroundProcessing\BackgroundProcess;

use WP_REST_Request;

class CancelImportEndpoint  implements EndpointInterface {
	/**
	 * @var BackgroundProcess
	 */
	private $process;

	public function __construct(BackgroundProcess $process){

		$this->process = $process;
	}
	public function get_arguments(): array {
		return [];
	}

	public function respond( WP_REST_Request $request ) {
		if($this->process->is_processing()){
			$this->process->cancel();
		}
		return new \WP_REST_Response(true);
	}

	public function validate( WP_REST_Request $request ): bool {
		return current_user_can('manage_options');
	}

	public function get_methods(): array {
		return [\WP_REST_Server::READABLE];
	}

	public function get_path(): string {
		return '/import/confirm/';
	}
}
