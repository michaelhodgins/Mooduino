<?php

require_once 'Mooduino/Db/Migrations/MigrationProvider/Interface.php';
require_once 'Mooduino/Db/Migrations/Migration.php';
require_once 'Mooduino/Db/Migrations/Migration/Abstract.php';
require_once 'Mooduino/Db/Migrations/MigrationManager.php';

class Mooduino_Db_Migrations_MigrationProvider extends Zend_Tool_Framework_Provider_Abstract implements Mooduino_Db_Migrations_MigrationProvider_Interface {

	public function getName() {
		return 'Migration';
	}

	public function generate($name, $env='development') {
		$this->_registry->getResponse()->appendContent(sprintf('Generating migration %s.', $name));
	}

	public function redo($step=1, $env='development') {

	}

	public function current($env='development') {

	}

	public function update($to='latest', $env='development') {

	}

	public function show($revision='all', $env='development') {

	}
}

