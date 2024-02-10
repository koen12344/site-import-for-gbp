<?php

namespace Koen12344\SiteImportForGbp\Configuration;

use Koen12344\SiteImportForGbp\DependencyInjection\Container;
use Koen12344\SiteImportForGbp\DependencyInjection\ContainerConfigurationInterface;
use Koen12344\SiteImportForGbp\Logger\ImportLogger;

class LoggerConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['service.import_logger'] = $container->service(function(Container $container){
			return new ImportLogger($container['options']);
		});
	}
}
