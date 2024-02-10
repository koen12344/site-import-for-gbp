<?php

namespace Koen12344\SiteImportForGbp\RestAPI;

use Koen12344\SiteImportForGbp\BackgroundProcessing\BackgroundProcess;
use Koen12344\SiteImportForGbp\Logger\ImportLogger;
use WP_REST_Request;
use WP_REST_Response;

class ImportStatusEndpoint implements EndpointInterface {
	/**
	 * @var BackgroundProcess
	 */
	private $background_process;
	/**
	 * @var ImportLogger
	 */
	private $logger;

	public function __construct(BackgroundProcess $background_process, ImportLogger $logger){

		$this->background_process = $background_process;
		$this->logger = $logger;
	}
	public function get_arguments(): array {
		return [

		];
	}

	public function respond( WP_REST_Request $request ) {

		return new WP_REST_Response(['importing' => $this->background_process->is_active(), 'unreviewed_log' => !empty($this->logger->read())]);
	}

	public function validate( WP_REST_Request $request ): bool {
		return current_user_can('manage_options');
	}

	public function get_methods(): array {
		return [\WP_REST_Server::READABLE];
	}

	public function get_path(): string {
		return '/import/status/';
	}
}
