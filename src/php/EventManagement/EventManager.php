<?php

namespace Koen12344\SiteImportForGbp\EventManagement;

class EventManager {

	public function add_callback($hook_name, $callback, $priority = 10, $accepted_args = 1){
		add_filter($hook_name, $callback, $priority, $accepted_args);
	}

	public function add_subscriber(SubscriberInterface $subscriber){
		if($subscriber instanceof EventManagerAwareSubscriberInterface){
			$subscriber->set_event_manager($this);
		}

		foreach($subscriber->get_subscribed_hooks()  as $hook_name => $parameters){
			$this->add_subscriber_callback($subscriber, $hook_name, $parameters);
		}
	}

	public function execute(){
		$args = func_get_args();
		return call_user_func_array('do_action', $args);
	}

	public function filter(){
		$args = func_get_args();
		return call_user_func_array('apply_filters', $args);
	}

	public function get_current_hook(){
		return current_filter();
	}

	public function has_callback($hook_name, $callback = false){
		return has_filter($hook_name, $callback);
	}

	public function remove_callback($hook_name, $callback, $priority = 10){
		return remove_filter($hook_name, $callback, $priority);
	}

	public function remove_subscriber(SubscriberInterface $subscriber){
		foreach($subscriber->get_subscribed_hooks() as $hook_name => $parameters){
			$this->remove_subscriber_callback($subscriber, $hook_name, $parameters);
		}
	}

	private function add_subscriber_callback( SubscriberInterface $subscriber, $hook_name, $parameters ) {
		if(is_string($parameters)){
			$this->add_callback($hook_name, [$subscriber, $parameters]);
		}elseif(is_array($parameters) && isset($parameters[0])){
			$this->add_callback($hook_name, [$subscriber, $parameters[0]], $parameters[1] ?? 10, $parameters[2] ?? 1 );
		}
	}

	private function remove_subscriber_callback( SubscriberInterface $subscriber, $hook_name, $parameters ) {
		if(is_string($parameters)){
			$this->remove_callback($hook_name, [$subscriber, $parameters]);
		}elseif(is_array($parameters) && isset($parameters[0])){
			$this->remove_callback($hook_name, [$subscriber, $parameters[0]], $parameters[1] ?? 10 );
		}
	}
}
