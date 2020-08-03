<?php

namespace MongoSchemist;

use MongoDB\Client as MongoDBClient;
use MongoSchemist\Exception\MongoDBConnectionException;

class MongoDBConnection {

	/**
	 * Constructor (singleton).
	 */
	private function __constuct() { }

	/**
	 * Creates and returns a client for a MongoDB instance.
	 *
	 * @param string $connString    MongoDB connection string
	 * @param array  $uriOptions    Additional connection string options
     * @param array  $driverOptions Driver-specific options
     * @return MongoDB\Client The client for the MongoDB instance
	 * @throws MongoDBConnectionException In case the connection could not be established
	 */
	public static function getClient($connString, $uriOptions = [], $driverOptions = []) {
		try {
			return new MongoDBClient($connString);
		}
		catch (\Exception $e) {
			throw new MongoDBConnectionException($e->getMessage());
		}
	}

	/**
	 * Tests if the connection is established with the MongoDB instance.
	 *
	 * @param MongoDB\Client $mongoClient A MongoDB client instance
	 * @throws MongoDBConnectionException In case the connection could not be established
	 */
	public static function testConnection($mongoClient) {
		try {
			$mongoClient->listDatabases();
		}
		catch (\Exception $e) {
			throw new MongoDBConnectionException($e->getMessage());
		}
	}

}

?>
