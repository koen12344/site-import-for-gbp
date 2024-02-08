<?php

namespace Koen12344\SiteImportForGbp\RestAPI;

use Koen12344\SiteImportForGbp\BackgroundProcessing\BackgroundProcess;
use WP_REST_Request;

class DispatchImportEndpoint implements EndpointInterface {

	/**
	 * @var BackgroundProcess
	 */
	private $background_process;

	public function __construct(BackgroundProcess $background_process){

		$this->background_process = $background_process;
	}
	public function get_arguments(): array {
		return [

		];
	}

	public function respond( WP_REST_Request $request ) {

		$item = [
			'action' => 'import',
			'location' => $request->get_param('location')['name'],
			'selection' => $request->get_param('selectedOptions'),
		];

		$this->background_process->push_to_queue($item);

		$this->background_process->save()->dispatch();


		return new \WP_REST_Response(true);
	}

	public function validate( WP_REST_Request $request ): bool {
		return current_user_can('manage_options');
	}

	public function get_methods(): array {
		return [\WP_REST_Server::CREATABLE];
	}

	public function get_path(): string {
		return '/import/dispatch/';
	}
}
