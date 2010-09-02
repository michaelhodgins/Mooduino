<?php
abstract class Mooduino_Db_Migrations_Migration_Abstract implements Mooduino_Db_Migrations_Migration {

	private $name = '';
	private $timestamp = 0;
	
	public function __construct($name, $timestamp) {
		$this->name = $name;
		$this->timestamp = $timestamp;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getTimestamp() {
		return $this->timestamp;
	}
}
