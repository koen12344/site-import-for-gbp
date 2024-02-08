<?php

namespace Koen12344\SiteImportForGbp;

class Options {
	public function get_option_name($name): string {
		return "psfg-{$name}";
	}

	public function get($name, $default = null){
		$option = get_option($this->get_option_name($name), $default);

		if(is_array($default) && !is_array($option)){
			$option = (array)$option;
		}

		return $option;
	}

	public function has($name): bool {
		return $this->get($name) !== null;
	}

	public function remove($name){
		delete_option($this->get_option_name($name));
	}

	public function set($name, $value){
		update_option($this->get_option_name($name), $value);
	}
}
