<?php

namespace Koen12344\SiteImportForGbp\BackgroundProcessing;

use DateTime;
use Koen12344\SiteImportForGbp\API\CachedGoogleMyBusiness;
use Koen12344\SiteImportForGbp\GoogleUserManager;

class BackgroundProcess extends \SIFG_Vendor_WP_Background_Process {

	protected $action ='sifg_import_process';
	/**
	 * @var CachedGoogleMyBusiness
	 */
	private $api;
	/**
	 * @var GoogleUserManager
	 */
	private $user_manager;

	public function __construct(GoogleUserManager $user_manager, CachedGoogleMyBusiness $api) {
		parent::__construct();
		$this->api = $api;
		$this->user_manager = $user_manager;
	}

	protected function task( $item ) {
		switch($item['action']){
			case 'import':
				return $this->start_import($item);
			case 'sideload_image':
				return $this->sideload_image($item);
		}
		return false;
	}


	private function sideload_image($item){
		//		'post_id' => $post_id,
		//		'is_featured' => $key === 0,
		//		'image'        => $media

		//append '#.jpg' to the url to bypass media_sideload_image the extension check
		$media_id = media_sideload_image($item['image']->googleUrl.'#.jpg', $item['post_id'], null, 'id');
		if(is_wp_error($media_id)){
			return false;
		}

		if($item['is_featured']){
			set_post_thumbnail($item['post_id'], $media_id);
		}
		return false;
	}
	private function start_import($item){
		$user_id = key($this->user_manager->get_accounts());
		$this->api->set_user_id($user_id);

		if(array_key_exists('posts', $item['selection'])){
			$posts = $this->api->get_posts("accounts/{$user_id}/{$item['location']}", 100, !empty($item['nextPageToken']) ? $item['nextPageToken'] : null);
/*
 * stdClass Object
(
    [name] => accounts/106802586615212834224/locations/10833174685256778669/localPosts/6468212393508372094
    [languageCode] => nl
    [summary] => New post: Auto-publish WooCommerce products to Google My Business - Google has removed the ability to create Product posts through the Google My Business API. This means you can no longer create “real” Product posts using the Post to Google My Business plugin.

Personally I think Google will be removing the product post type altogether in the near future, in favor of the new PRODUCT COLLECTIONS feature. But it’s also NOT YET POSSIBLE to populate that section using the API (I will definitely add that feature to the plugin once that becomes possible!).

THE SOLUTION

If you want to bring your latest products and services to the attention of  your (potential) customers, you can simply publish them as WHAT’S NEW or OFFER posts!

After all, the people that search for your business on Google won’t really be aware of the difference between the various Google My Business post types.

All they see is a picture of your shiny new product, with a “Shop now” button beneath it. IT WILL BE JUST AS EFFECTIVE!

HOW TO SET UP THE PLUGIN

(Auto-)publishing your latest WooCommerce products to Google My Business is easy!

 	* Make sure the Post to Google My Business plugin [https://tycoonmedia.net] is installed and activated on your WordPress website. The ability to create Product posts is available in the Pro version and up [https://tycoonmedia.net/#pricing].
 	* Enable the PRODUCTS post type in the plugin settings and save the changes.

 	* The Post to Google My Business panel & Auto Publish...
    [callToAction] => stdClass Object
        (
            [actionType] => SHOP
            [url] => https://tycoonmedia.net/blog/auto-publish-woocommerce-products-to-google-my-business/
        )

    [state] => LIVE
    [updateTime] => 2020-10-16T00:18:06.697Z
    [createTime] => 2020-08-03T14:42:17.724Z
    [searchUrl] => https://local.google.com/place?id=2167793724746878381&use=posts&lpsid=CIHM0ogKEICAgICSj-uXGw
    [media] => Array
        (
            [0] => stdClass Object
                (
                    [name] => accounts/106802586615212834224/locations/10833174685256778669/media/localPosts/AF1QipPHLw9k7KL8bIqflx7Mgqz8c5QPuYNJjbIFhWa8
                    [mediaFormat] => PHOTO
                    [googleUrl] => https://lh3.googleusercontent.com/p/AF1QipPHLw9k7KL8bIqflx7Mgqz8c5QPuYNJjbIFhWa8
                )

        )

    [topicType] => STANDARD
)

 */
			foreach($posts->localPosts as $post)
			{

				$date = new DateTime($post->createTime);

				$post_id = wp_insert_post([
					'post_content' => $post->summary,
					'post_status' => 'publish',
					'post_date_gmt' => $date->format('Y-m-d H:i:s'),

				]);
				if(!empty($post->media) && is_array($post->media)){
					foreach($post->media as $key => $media){
						if($media->mediaFormat !== 'PHOTO'){
							continue;
						}
						$this->push_to_queue([
							'action' => 'sideload_image',
							'post_id' => $post_id,
							'is_featured' => $key === 0,
							'image'        => $media
						]);
					}
					$this->save();
				}
			}



			if(!empty($posts->nextPageToken)){
				return array_merge($item, [
					'nextPageToken' => $posts->nextPageToken,
				]);
			}
		}

		return false;
	}
}
