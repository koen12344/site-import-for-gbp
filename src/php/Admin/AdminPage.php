<?php

namespace Koen12344\SiteImportForGbp\Admin;

use Koen12344\SiteImportForGbp\GoogleUserManager;
use Koen12344\SiteImportForGbp\Options;

class AdminPage {
	private $plugin_path;
	private $plugin_url;
	/**
	 * @var Options
	 */
	private $options;
	/**
	 * @var GoogleUserManager
	 */
	private $user_manager;

	public function __construct(Options $options, GoogleUserManager $user_manager, $plugin_path, $plugin_url){
		$this->plugin_path    = $plugin_path;
		$this->plugin_url = $plugin_url;
		$this->options = $options;
		$this->user_manager = $user_manager;
	}

	public function get_page_title(): string {
		return 	esc_html__('Site Import for GBP', 'site-import-for-gbp');
	}

	public function get_menu_title(): string {
		return esc_html__('Site Import for GBP', 'site-import-for-gbp');
	}

	public function get_capability(): string {
		return 'manage_options';
	}

	public function get_menu_slug(): string {
		return 'site-import-for-gbp';
	}

	public function render_page(){
		echo '<div id="sifg-admin-page"></div>';
	}

	public function register_js_assets(){
		$script_assets = require( $this->plugin_path . 'build/index.asset.php');

		wp_register_script('sifg-admin-script', $this->plugin_url . 'build/index.js', $script_assets['dependencies'], $script_assets['version'], true);



		wp_localize_script('sifg-admin-script', 'sifg_localize_admin', [
			'nonce'                 => wp_create_nonce('wp_rest'),
			'plugin_url'            => $this->plugin_url,
			'support_url'           => esc_url(admin_url('admin.php?page=site-import-for-gbp-contact')),
			'auth_url'              => esc_url(admin_url('admin-post.php?action=sifg_auth_redirect')),
			'is_google_configured'  => !empty($this->user_manager->get_accounts()),
			'is_multisite'          => is_multisite(),
		]);
		wp_set_script_translations('sifg-admin-script', 'site-import-for-gbp', $this->plugin_path . 'languages');

	}

	public function load_js_assets(){
		wp_enqueue_script('sifg-admin-script');
		wp_enqueue_style( 'sifg-admin-style', $this->plugin_url . 'build/style-index.css', array( 'wp-components' ) );
	}
}
