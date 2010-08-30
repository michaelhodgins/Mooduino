<?php

require_once 'Mooduino/Db/Migrations/ProviderInterface.php';
require_once 'Mooduino/Db/Migrations/Migration.php';
require_once 'Mooduino/Db/Migrations/Migration/Abstract.php';
require_once 'Mooduino/Db/Migrations/MigrationManager.php';

class Mooduino_Db_Migrations_MigrationProvider implements Mooduino_Db_Migrations_ProviderInterface, Zend_Tool_Framework_Provider_Interface {

	public function getName() {
		return 'Migration';
	}

	public function generate($name, $env='development') {
		printf('Hello %s!', $name);
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

