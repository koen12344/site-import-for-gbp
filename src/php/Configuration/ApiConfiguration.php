<?php

namespace Koen12344\SiteImportForGbp\Configuration;

use Koen12344\SiteImportForGbp\API\CachedGoogleMyBusiness;
use Koen12344\SiteImportForGbp\API\ProxyAuthenticationAPI;
use Koen12344\SiteImportForGbp\DependencyInjection\Container;
use Koen12344\SiteImportForGbp\DependencyInjection\ContainerConfigurationInterface;

class ApiConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['wordpress.http_transport'] = _wp_http_get_object();

		$container['proxy_auth_api'] = $container->service(function(Container $container){
			return new ProxyAuthenticationAPI($container['wordpress.http_transport'], $container['plugin_version'], $container['plugin_domain']);
		});

		$container['google_my_business_api'] = $container->service(function(Container $container){
			return new CachedGoogleMyBusiness($container['wordpress.http_transport'], $container['proxy_auth_api']);
		});
	}
}
