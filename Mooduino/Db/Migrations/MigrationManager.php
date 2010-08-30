<?php
class Mooduino_Db_Migrations_MigrationManager {

	/**
	 * @var Mooduino_Db_Migrations_MigrationManager
	 */
	private static $instance = null;

	/**
	 * Constructs the MigrationManager. This is private
	 * as MigrationManager is a singleton.
	 */
	private function __constructor() {

	}
	
	/**
	 * Returns the MigrationManager singleton.
	 * @return Mooduino_Db_Migrations_MigrationManager
	 */
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new Mooduino_Db_Migrations_MigrationManager();
		}
		return self::$instance;
	}
}

