<?php
class Mooduino_Db_Migrations_MigrationManager {

	/**
	 * @var Mooduino_Db_Migrations_MigrationManager
	 */
	private static $instance = null;

	private function __constructor() {

	}

	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new Mooduino_Db_Migrations_MigrationManager();
		}
		return self::$instance;
	}
}

