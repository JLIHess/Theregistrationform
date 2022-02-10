<?php

/**
 * Class Core_Registry
 */
final class Core_Registry
{
	private $data = array();

	public function get($key) {
		return (isset($this->data[$key]) ? $this->data[$key] : NULL);
	}

	public function set($key, $value) {
		$this->data[$key] = $value;
	}

	public function has($key) {
    	return isset($this->data[$key]);
  	}

}
