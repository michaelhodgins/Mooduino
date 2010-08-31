<?php
abstract class Mooduino_Db_Migrations_Migration_Abstract implements Mooduino_Db_Migrations_Migration {

	protected $name = '';
	
	public function getName() {
		return $this->name;
	}
	
}
