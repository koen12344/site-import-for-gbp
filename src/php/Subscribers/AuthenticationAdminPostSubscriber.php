<?php

namespace Koen12344\SiteImportForGbp\Subscribers;

use Exception;
use Koen12344\SiteImportForGbp\API\ProxyAuthenticationAPI;
use Koen12344\SiteImportForGbp\EventManagement\SubscriberInterface;
use Koen12344\SiteImportForGbp\GoogleUserManager;
use SIFG\Vendor\Firebase\JWT\BeforeValidException;
use SIFG\Vendor\Firebase\JWT\ExpiredException;

class AuthenticationAdminPostSubscriber implements SubscriberInterface {


	/**
	 * @var ProxyAuthenticationAPI
	 */
	private $auth_api;
	/**
	 * @var GoogleUserManager
	 */
	private $user_manager;

	public function __construct(ProxyAuthenticationAPI $auth_api, GoogleUserManager $user_manager){

		$this->auth_api = $auth_api;
		$this->user_manager = $user_manager;
	}
	public static function get_subscribed_hooks(): array {
		return [
			'admin_post_sifg_auth_redirect'         => 'auth_redirect',
			'admin_post_pgmb_google_authorized'     => 'fetch_tokens',
		];
	}

	protected function wp_die_args(): array {
		return [
			'link_url'  => 	esc_url(admin_url('admin-post.php?action=sifg_auth_redirect')),
			'link_text' => __('Retry', 'site-import-for-gbp'),
		];
	}

	public function auth_redirect(){
		if(!current_user_can('manage_options')){
			wp_die(__('You do not have permission to add Google accounts', 'site-import-for-gbp'),'', $this->wp_die_args());
		}

		try{
			$response = $this->auth_api->get_authentication_url(esc_url(admin_url('admin-post.php')), wp_create_nonce('sifg_auth_redirect'));
		}catch(\Exception $e){
			wp_die(sprintf(__('Could not generate authentication URL: %s', 'site-import-for-gbp'), $e->getMessage()),'', $this->wp_die_args());
		}

		wp_redirect($response->url);
		exit;
	}

	public function fetch_tokens(){
		if(!wp_verify_nonce(sanitize_key($_REQUEST['state']), 'sifg_auth_redirect')){ wp_die(__('Invalid nonce', 'site-import-for-gbp'),'', $this->wp_die_args()); }

		if(!current_user_can('manage_options')){
			wp_die(__('You do not have permission to add Google accounts', 'site-import-for-gbp'),'', $this->wp_die_args());
		}

		if(empty($_REQUEST['code'])){ wp_die(__('Did not receive authentication code', 'site-import-for-gbp'),'', $this->wp_die_args()); }

		try{
			$tokens = $this->auth_api->get_tokens_from_code($_REQUEST['code']);
		}catch(Exception $e){
			wp_die(sprintf(__('Could not obtain access tokens: %s', 'site-import-for-gbp'), $e->getMessage()), '', $this->wp_die_args());
		}

		try {
			$this->user_manager->add_account($tokens);
		}catch ( ExpiredException $e){
		}catch( BeforeValidException $e){
			wp_die(sprintf(__('Could not verify Google access token: %s. Is the date & time on your server set correctly?', 'site-import-for-gbp'), $e->getMessage()),'', $this->wp_die_args());
		}catch( Exception $e){
			wp_die(sprintf(__('Could not verify Google access token: %s', 'site-import-for-gbp'), $e->getMessage()),'', $this->wp_die_args());
		}


		wp_safe_redirect(admin_url('admin.php?page=site-import-for-gbp'));
		exit;
	}

}
