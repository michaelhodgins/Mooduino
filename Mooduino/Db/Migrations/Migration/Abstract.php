<?php
abstract class Mooduino_Db_Migrations_Migration_Abstract implements Mooduino_Db_Migrations_Migration {

	protected $name = '';
	protected $timestamp = 0;
	
	public function getName() {
		return $this->name;
	}
	
	public function getTimestamp() {
		return $this->timestamp;
	}
}
