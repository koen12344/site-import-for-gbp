<?php

namespace Koen12344\SiteImportForGbp\Subscribers;



use Koen12344\SiteImportForGbp\EventManagement\SubscriberInterface;
use Koen12344\SiteImportForGbp\RestAPI\EndpointInterface;

class RestApiSubscriber implements SubscriberInterface {
	/**
	 * @var array
	 */
	private $endpoints;
	private $namespace;

	/**
	 * @param $namespace
	 * @param EndpointInterface[] $endpoints
	 */
	public function __construct($namespace, array $endpoints = []){
		$this->endpoints = [];

		foreach($endpoints as $endpoint){
			$this->add_endpoint($endpoint);
		}

		$this->namespace = $namespace;
	}
	public static function get_subscribed_hooks(): array {
		return [
			'rest_api_init' => 'register_endpoints',
		];
	}

	public function add_endpoint(EndpointInterface $endpoint){
		$this->endpoints[] = $endpoint;
	}

	public function register_endpoints(){
		foreach($this->endpoints as $endpoint){
			$this->register_endpoint($endpoint);
		}
	}

	public function get_arguments(EndpointInterface $endpoint): array {
		return [
			'args' => $endpoint->get_arguments(),
			'callback' => [$endpoint, 'respond'],
			'methods' => $endpoint->get_methods(),
			'permission_callback' => [$endpoint, 'validate'],
		];
	}

	private function register_endpoint(EndpointInterface $endpoint){
		register_rest_route($this->namespace, $endpoint->get_path(), $this->get_arguments($endpoint));
	}
}
