<?php

namespace Koen12344\SiteImportForGbp\Logger;

class ImportLogger {

	const LOG_KEY = 'sifg_import_log';
	public function add($message){
		$log = $this->read();
		$timestamp = "[".current_time('mysql')."] ";
		$message = $timestamp.esc_html($message)."\n";
		$log = $message.$log;

		$this->save($log);
	}

	private function save($log){
		return set_transient(self::LOG_KEY, $log, 12 * HOUR_IN_SECONDS);
	}

	public function clear(){
		return delete_transient(self::LOG_KEY);
	}
	public function read(){
		return get_transient(self::LOG_KEY);
	}
}
