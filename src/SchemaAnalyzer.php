<?php

namespace MongoSchemist;

class SchemaAnalyzer {

	/** @var integer */
	const MAX_DEPTH = 100;

	/** @var MongoDB\Client */
	private $mongoClient;

	/**
	 * Constructor.
	 *
	 * @param MongoDB\Client MongoDB Client
	 */
	public function __construct($mongoClient) {
		$this->mongoClient = $mongoClient;
	}

	/**
	 * Extracts the schema information from a collection.
	 *
	 * @param string $dbName          DB name
	 * @param string $collection      Collection name
	 * @param MongoDB\Collection|null The collection object, if available
	 * @param integer $maxDepth       Maximum depth of each document
	 * @return MongoSchemist\CollectionSchema
	 * @throws MongoDB\Exception\InvalidArgumentException In case of parameter errors
	 */
	public function extractCollectionSchema($dbName, $collectionName, $collection = null, $maxDepth = self::MAX_DEPTH) {
		if ($collection === null) {
			$collection = $this->mongoClient->selectCollection($dbName, $collectionName);
		}
		$cursor = $collection->find();
		$collectionSchema = new CollectionSchema($dbName, $collectionName, $maxDepth);
		$recordNum = 0;
		foreach ($cursor as $document) {
			$collectionSchema->evaluateDocument($document);
			$recordNum++;
		}
		$collectionSchema
			->sortByCountDesc()
			->setRecordNum($recordNum);
		return $collectionSchema;
	}

}

?>
