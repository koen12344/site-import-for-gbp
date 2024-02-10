<?php

namespace Koen12344\SiteImportForGbp\Subscribers;

use Koen12344\SiteImportForGbp\Admin\AdminPage;
use Koen12344\SiteImportForGbp\EventManagement\EventManager;
use Koen12344\SiteImportForGbp\EventManagement\EventManagerAwareSubscriberInterface;

class AdminPageSubscriber implements EventManagerAwareSubscriberInterface {

	/**
	 * @var EventManager
	 */

	private $event_manager;
	/**
	 * @var AdminPage
	 */
	private $admin_page;
	/**
	 * @var mixed
	 */
	private $dashicon;

	public function __construct($admin_page, $dashicon) {
		$this->admin_page = $admin_page;
		$this->dashicon = $dashicon;
	}
	public function set_event_manager( EventManager $event_manager ) {
		$this->event_manager = $event_manager;
	}

	public static function get_subscribed_hooks(): array {
		return [
			'admin_menu' => 'add_admin_page',
			'admin_enqueue_scripts' => 'register_js_assets',
		];
	}

	public function add_admin_page(){
		$page_hook = add_menu_page(
			$this->admin_page->get_page_title(),
			$this->admin_page->get_menu_title(),
			$this->admin_page->get_capability(),
			$this->admin_page->get_menu_slug(),
			[$this->admin_page, 'render_page'],
			$this->dashicon
		);

		//todo: temporary messy solution to add the plugin as an importer
		register_importer(
			$this->admin_page->get_menu_slug(),
			__('Site import for Google Business Profile', 'site-import-for-gbp'),
			__('Import Posts, Reviews and Images from Google Business Profile', 'site-import-for-gbp'),
			[$this->admin_page, 'render_page']
		);

		$this->event_manager->add_callback("admin_print_scripts-{$page_hook}", [$this->admin_page, 'load_js_assets']);
	}

	function register_js_assets(){
		$this->admin_page->register_js_assets();

		//todo: this as well
		global $pagenow;

		if ($pagenow === 'admin.php' && isset($_GET['import']) && $_GET['import'] === 'site-import-for-gbp') {
			$this->admin_page->load_js_assets();
		}
	}
}
