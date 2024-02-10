<?php
/**
 * Plugin Name:     Site Import for Google Business Profile (Google My Business)
 * Plugin URI:      https://tycoonmedia.net
 * Description:     Site Import for GBP will import your Google My Business posts, images, reviews and other data into your WordPress website
 * Author:          Koen Reus
 * Author URI:      https://koenreus.com
 * Text Domain:     site-import-for-gbp
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Site_Import_For_Gbp
 */

use Koen12344\SiteImportForGbp\Plugin;

if (version_compare(PHP_VERSION, '7.2', '<')) {
	exit(sprintf('Site Import for GBP requires PHP 7.2 or higher. Your WordPress site is using PHP %s.', PHP_VERSION));
}

require __DIR__.'/vendor/autoload.php';

register_activation_hook(__FILE__, ['\Koen12344\SiteImportForGbp\Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['\Koen12344\SiteImportForGbp\Plugin', 'deactivate']);

$site_import_for_gbp = new Plugin(__FILE__);
add_action('after_setup_theme', [$site_import_for_gbp, 'init']);
