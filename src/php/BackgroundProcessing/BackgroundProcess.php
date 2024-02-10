<?php

namespace Koen12344\SiteImportForGbp\BackgroundProcessing;

use DateTime;
use Koen12344\SiteImportForGbp\API\CachedGoogleMyBusiness;
use Koen12344\SiteImportForGbp\GoogleUserManager;
use Koen12344\SiteImportForGbp\Logger\ImportLogger;

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
	/**
	 * @var ImportLogger
	 */
	private $logger;

	public function __construct(GoogleUserManager $user_manager, CachedGoogleMyBusiness $api, ImportLogger $logger) {
		parent::__construct();
		$this->api = $api;
		$this->user_manager = $user_manager;
		$this->logger = $logger;
	}

	protected function task( $item ) {
		switch($item['action']){
			case 'import':
				return $this->start_import($item);
			case 'sideload_post_image':
				return $this->sideload_post_image($item);
			case 'import_posts':
				return $this->import_posts($item);
			case 'import_gallery':
				return $this->import_gallery($item);
			case 'sideload_gallery_image':
				return $this->sideload_gallery_image($item);
		}
		return false;
	}


	private function sideload_post_image($item){
		//		'post_id' => $post_id,
		//		'is_featured' => $key === 0,
		//		'image'        => $media

		$existing_media = get_posts([
			'meta_key' => '_sifg_name',
			'meta_value' => $item['image']->name,
			'post_type' => 'attachment',
			'posts_per_page' => 1,
		]);
		if ($existing_media) {
			$media_id = $existing_media[0]->ID;
		}else{
			//append '#.jpg' to the url to bypass media_sideload_image the extension check
			$media_id = media_sideload_image($item['image']->googleUrl.'#.jpg', $item['post_id'], null, 'id');
		}


		if(is_wp_error($media_id)){
			//translators: %1$s is the Google internal image ID, %2$s is the error message
			$this->logger->add(sprintf(__('Failed to import image %1$s: %2$s', 'site-import-for-gbp'), $item['image']->name, $media_id->get_error_message()));
			return false;
		}

		update_post_meta($media_id, '_sifg_name', $item['image']->name);

		if($item['is_featured']){
			set_post_thumbnail($item['post_id'], $media_id);
		}
		return false;
	}
	private function start_import($item){
		if(array_key_exists('posts', $item['selection'])){
			$this->push_to_queue([
				'action' => 'import_posts',
				'location' => $item['location'],
			]);
		}

		if(array_key_exists('images', $item['selection'])){
			$this->push_to_queue([
				'action' => 'import_gallery',
				'location' => $item['location'],
			]);
		}

		if(array_key_exists('reviews', $item['selection'])){
			$this->push_to_queue([
				'action' => 'import_reviews',
				'location' => $item['location'],
			]);
		}

		$this->save();

		return false;
	}

	private function import_posts($item){
		$user_id = key($this->user_manager->get_accounts());
		if(empty($user_id)){
			$this->logger->add(__('Undefined user Google user ID', 'site-import-for-gbp'));
			return false;
		}
		$this->api->set_user_id($user_id);

		try{
			$posts = $this->api->get_posts("accounts/{$user_id}/{$item['location']}", 50, !empty($item['nextPageToken']) ? $item['nextPageToken'] : null);
		}catch(\Throwable $e){
			//translators: %1$s is internal Google Location ID, %2$s is the error message
			$this->logger->add(sprintf(__('Failed to load posts for location %1$s: %2$s', 'site-import-for-gbp'), $item['location'], $e->getMessage()));
			return false;
		}

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

		if(empty($posts->localPosts) || !is_array($posts->localPosts)){
			$this->logger->add(__('Did not find any posts to import', 'site-import-for-gbp'));
			return false;
		}

		foreach($posts->localPosts as $post)
		{

			$date = new DateTime($post->createTime);

			$existing_posts = get_posts([
				'meta_key' => '_sifg_name',
				'meta_value' => $post->name,
				'post_type' => 'post',
				'posts_per_page' => 1,
			]);

			$post_id = 0;
			if ($existing_posts) {
				$post_id = $existing_posts[0]->ID;
			}

			$title = !empty($post->event->title) ? $post->event->title : mb_strimwidth($post->summary, 0, 60, '...');

			$post_id = wp_insert_post([
				'ID'           => $post_id,
				'post_title' => $title,
				'post_content' => !empty($post->summary) ? $post->summary : '',
				'post_status' => 'publish',
				'post_date_gmt' => $date->format('Y-m-d H:i:s'),
				'meta_input' => [
					'_sifg_name'          => $post->name,
					'_sifg_gbp_post_data' => $post,
				]
			]);



			if(!empty($post->media) && is_array($post->media)){
				foreach($post->media as $key => $media){
					if($media->mediaFormat !== 'PHOTO'){
						continue;
					}
					$this->push_to_queue([
						'action' => 'sideload_post_image',
						'post_id' => $post_id,
						'is_featured' => $key === 0,
						'image'        => $media
					]);
				}
				$this->save();
			}

			if($existing_posts){
				//translators: %1$d is WordPress post ID, %2$s is the error message
				$this->logger->add(sprintf(__('Updated already imported post %1$d: %2$s', 'site-import-for-gbp'), $post_id, $title));
			}else{
				//translators: %1$d is WordPress post ID, %2$s is the title of the imported post
				$this->logger->add(sprintf(__('Imported post %1$d: %2$s', 'site-import-for-gbp'), $post_id, $title));
			}

		}



		if(!empty($posts->nextPageToken)){
			$this->logger->add(__('-- 50 post limit reached --', 'site-import-for-gbp'));
			return false;
//			return array_merge($item, [
//				'nextPageToken' => $posts->nextPageToken,
//			]);
		}
		return false;
	}

	private function import_gallery($item){
		$user_id = key($this->user_manager->get_accounts());
		$this->api->set_user_id($user_id);

		try{
			$media = $this->api->list_media("accounts/{$user_id}/{$item['location']}", 100, !empty($item['nextPageToken']) ? $item['nextPageToken'] : null);
		}catch(\Throwable $e){
			//translators: %1$s is the Google internal location ID, %2$s is the error message
			$this->logger->add(sprintf(__('Failed to load Gallery images from %1$s: %2$s', 'site-import-for-gbp'), $item['location'], $e->getMessage()));
			return false;
		}

//stdClass Object
//		(
//			[name] => accounts/106802586615212834224/locations/10833174685256778669/media/AF1QipN9zi9mt1UjknwYLHBQ7Ri9F_eyaMvaCHB5KCsB
//		[sourceUrl] => https://lh3.googleusercontent.com/GlMyXTQ7slW1ys71w9brXbxqnq5x89mquXFIE54aVVeJNECmsXedxlSvp2kzxagslKqQ5KlpNPvgZYge_Q=s0
//    [mediaFormat] => PHOTO
//		[locationAssociation] => stdClass Object
//		(
//			[category] => ADDITIONAL // COVER // PROFILE
//        )
//
//    [googleUrl] => https://lh3.googleusercontent.com/oOJD7A7kVOZ3gH_AQbUx4e3Fp6qoff3jxKi9t76RCJMwWo59Y_gEgcIGgjFuPnjBGAotftBjUinYOTTO=s0
//    [thumbnailUrl] => https://lh3.googleusercontent.com/p/AF1QipN9zi9mt1UjknwYLHBQ7Ri9F_eyaMvaCHB5KCsB=s300
//    [createTime] => 2020-10-16T00:12:30Z
//		[dimensions] => stdClass Object
//		(
//			[widthPixels] => 885
//            [heightPixels] => 483
//        )
//
//)
		if(empty($media->mediaItems) || !is_array($media->mediaItems)){
			$this->logger->add(__('Did not find any gallery images to import', 'site-import-for-gbp'));
		}

		foreach($media->mediaItems as $media_item){
			if(empty($media_item->mediaFormat) || $media_item->mediaFormat !== 'PHOTO'){
				continue;
			}
			$this->push_to_queue([
				'action' => 'sideload_gallery_image',
				'media_item'        => $media_item
			]);

		}

		$this->save();

		if(!empty($posts->nextPageToken)){
			$this->logger->add(__('-- 100 image limit reached --', 'site-import-for-gbp'));
			return false;
//			return array_merge($item, [
//				'nextPageToken' => $posts->nextPageToken,
//			]);
		}

		return false;
	}

	private function sideload_gallery_image($item){
		//append '#.jpg' to the url to bypass media_sideload_image the extension check


		$existing_media = get_posts([
			'meta_key' => '_sifg_name',
			'meta_value' => $item['media_item']->name,
			'post_type' => 'attachment',
			'posts_per_page' => 1,
		]);
		if ($existing_media) {
			//translators: %s is the Google internal image ID
			$this->logger->add(sprintf(__('Skipping image %s, already imported before', 'site-import-for-gbp'), $item['media_item']->name));
			return false;
		}

		//append '#.jpg' to the url to bypass media_sideload_image the extension check
		$media_id = media_sideload_image((!empty($item['media_item']->sourceUrl) ? $item['media_item']->sourceUrl : $item['media_item']->googleUrl).'#.jpg', 0, null, 'id');

		if(is_wp_error($media_id)){
			//translators: %1$s is the Google internal image ID, %2$s is the error message
			$this->logger->add(sprintf(__('Failed to import image %1$s: %2$s', 'site-import-for-gbp'), $item['media_item']->name, $media_id->get_error_message()));
			return false;
		}

		update_post_meta($media_id, '_sifg_association', $item['media_item']->locationAssociation->category);
		update_post_meta($media_id, '_sifg_name', $item['media_item']->name);
		//translators: %s is Google Image ID
		$this->logger->add(sprintf(__('Imported gallery image %s into Media library', 'site-import-for-gbp'), $item['media_item']->name));

		return false;
	}

	private function import_reviews($item){
		return false;
	}
}
