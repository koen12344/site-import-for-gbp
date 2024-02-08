<?php

namespace Koen12344\SiteImportForGbp\EventManagement;

interface EventManagerAwareSubscriberInterface extends SubscriberInterface {
	/**
	 * Set the WordPress event manager for the subscriber.
	 *
	 * @param EventManager $event_manager
	 */
	public function set_event_manager(EventManager $event_manager);
}
