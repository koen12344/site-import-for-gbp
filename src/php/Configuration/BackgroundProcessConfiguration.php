<?php

namespace Koen12344\SiteImportForGbp\Configuration;

use Koen12344\SiteImportForGbp\BackgroundProcessing\BackgroundProcess;
use Koen12344\SiteImportForGbp\DependencyInjection\Container;
use Koen12344\SiteImportForGbp\DependencyInjection\ContainerConfigurationInterface;

class BackgroundProcessConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['service.import_process'] = $container->service(function(Container $container){
			return new BackgroundProcess($container['user_manager'], $container['google_my_business_api']);
		});
	}
}
