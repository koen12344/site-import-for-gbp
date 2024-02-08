<?php

namespace Koen12344\SiteImportForGbp\Configuration;

use Koen12344\SiteImportForGbp\DependencyInjection\Container;
use Koen12344\SiteImportForGbp\DependencyInjection\ContainerConfigurationInterface;
use Koen12344\SiteImportForGbp\EventManagement\EventManager;
use Koen12344\SiteImportForGbp\Subscribers\AdminPageSubscriber;
use Koen12344\SiteImportForGbp\Subscribers\AuthenticationAdminPostSubscriber;
use Koen12344\SiteImportForGbp\Subscribers\RestApiSubscriber;

class EventManagementConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['service.event_manager'] = $container->service(function(Container $container) : EventManager{
			return new EventManager();
		});

		$container['subscribers'] = $container->service(function(Container $container){
			return [
				new AdminPageSubscriber($container['page.admin'], $container['plugin_dashicon']),
				new AuthenticationAdminPostSubscriber($container['proxy_auth_api'], $container['user_manager']),
				new RestApiSubscriber($container['plugin_rest_namespace'], $container['rest_endpoints']),
			];
		});
	}
}
