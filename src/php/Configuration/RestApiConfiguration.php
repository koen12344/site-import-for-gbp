<?php

namespace Koen12344\SiteImportForGbp\Configuration;

use Koen12344\SiteImportForGbp\DependencyInjection\Container;
use Koen12344\SiteImportForGbp\DependencyInjection\ContainerConfigurationInterface;
use Koen12344\SiteImportForGbp\RestAPI\ConfirmImportEndpoint;
use Koen12344\SiteImportForGbp\RestAPI\DisconnectGoogleEndpoint;
use Koen12344\SiteImportForGbp\RestAPI\DispatchImportEndpoint;
use Koen12344\SiteImportForGbp\RestAPI\GetGroupLocationsEndpoint;
use Koen12344\SiteImportForGbp\RestAPI\GetGroupsEndpoint;
use Koen12344\SiteImportForGbp\RestAPI\GetImportLogEndpoint;
use Koen12344\SiteImportForGbp\RestAPI\ImportStatusEndpoint;

class RestApiConfiguration implements ContainerConfigurationInterface {

	public function modify( Container $container ) {
		$container['rest_endpoints'] = $container->service(function(Container $container){
			return [
				'get_groups_endpoint' => new GetGroupsEndPoint($container['google_my_business_api'], $container['user_manager']),
				'get_group_locations_endpoint' => new GetGroupLocationsEndpoint($container['google_my_business_api'], $container['user_manager']),
				'disconnect_google_endpoint' => new DisconnectGoogleEndpoint($container['user_manager']),
				'dispatch_import_endpoint'   => new DispatchImportEndpoint($container['service.import_process']),
				'import_status_endpoint'   => new ImportStatusEndpoint($container['service.import_process'], $container['service.import_logger']),
				'import_log_endpoint'       => new GetImportLogEndpoint($container['service.import_logger']),
				'confirm_import_endpoint'   => new ConfirmImportEndpoint($container['service.import_logger']),
				];
		});
	}
}
