<?php

namespace Koen12344\SiteImportForGbp\Configuration;

use Koen12344\SiteImportForGbp\DependencyInjection\Container;
use Koen12344\SiteImportForGbp\DependencyInjection\ContainerConfigurationInterface;
use Koen12344\SiteImportForGbp\Options;

class OptionsConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['options'] = $container->service(function (Container $container){
			return new Options();
		});
	}
}
