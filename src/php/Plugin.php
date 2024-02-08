<?php

namespace Koen12344\SiteImportForGbp;

use Koen12344\SiteImportForGbp\DependencyInjection\Container;

class Plugin {
	const DOMAIN = 'site-import-for-gbp';

	const VERSION = '0.1.0';

	const DASHICON = '';

	const REST_NAMESPACE = 'sifg/v1';

	private $loaded;

	private $container;

	private $background_process;

	public function __construct($file){
		$this->container = new Container([
			'plugin_basename'       => plugin_basename($file),
			'plugin_domain'         => self::DOMAIN,
			'plugin_path'           => plugin_dir_path($file),
			'plugin_relative_path'  => basename(plugin_dir_path($file)),
			'plugin_url'            => plugin_dir_url($file),
			'plugin_version'        => self::VERSION,
			'plugin_dashicon'       => self::DASHICON,
			'plugin_rest_namespace' => self::REST_NAMESPACE,
		]);
	}

	public static function activate(){

	}

	public static function deactivate(){

	}

	public function is_loaded(){
		return $this->loaded;
	}

	public function init(){
		if($this->is_loaded()){
			return;
		}

		$this->container->configure([
			Configuration\AdminConfiguration::class,
			Configuration\OptionsConfiguration::class,
			Configuration\EventManagementConfiguration::class,
			Configuration\ApiConfiguration::class,
			Configuration\RestApiConfiguration::class,
			Configuration\BackgroundProcessConfiguration::class
		]);

		foreach($this->container['subscribers'] as $subscriber){
			$this->container['service.event_manager']->add_subscriber($subscriber);
		}

		$this->background_process = $this->container['service.import_process'];

		$this->loaded = true;
	}


	public function get_container(): Container {
		return $this->container;
	}

	public function get_event_manager(){
		if(!$this->is_loaded()){
			return false;
		}

		return $this->container['service.event_manager'];
	}

	public function get_background_process(){
		return $this->background_process;
	}
}
