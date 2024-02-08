<?php

namespace Koen12344\SiteImportForGbp\Configuration;

use Koen12344\SiteImportForGbp\Admin\AdminPage;
use Koen12344\SiteImportForGbp\DependencyInjection\Container;
use Koen12344\SiteImportForGbp\DependencyInjection\ContainerConfigurationInterface;
use Koen12344\SiteImportForGbp\GoogleUserManager;

class AdminConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['page.admin'] = $container->service(function(Container $container){
			return new AdminPage($container['options'], $container['user_manager'], $container['plugin_path'], $container['plugin_url']);
		});

		$container['user_manager'] = $container->service(function(Container $container){
			return new GoogleUserManager($container['proxy_auth_api'], $container['wordpress.http_transport']);
		});
	}
}
