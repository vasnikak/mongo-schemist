<?php

namespace MongoSchemist\Tests;

use MongoSchemist\SchemaAnalyzer;

use Helmich\MongoMock\MockCollection;
use PHPUnit\Framework\TestCase;

final class MongoSchemistTest extends TestCase {
	
	const COLLECTION_DATA = [
		[
			'db' => 'db',
			'collection' => 'collection_01',
			'input' => __DIR__ . '/test_data/collection_data_01.json',
			'output' => __DIR__ . '/test_data/collection_schema_01.txt'
		],
		[
			'db' => 'db',
			'collection' => 'collection_02',
			'input' => __DIR__ . '/test_data/collection_data_02.json',
			'output' => __DIR__ . '/test_data/collection_schema_02.txt'
		]
	];

	private $schemaAnalyzer;

	private static function readFileAsString($path) {
		$content = @file_get_contents($path);
		if ($content === FALSE)
			throw new \Exception("Could not read contents of file {$path}");
		return $content;
	}

	public function __construct() {
		parent::__construct();
		$this->schemaAnalyzer = new SchemaAnalyzer(null);
	}

	public function tests() {
		foreach (self::COLLECTION_DATA as $c) {
			$data = json_decode($this->readFileAsString($c['input']), true);
			$collection = new MockCollection();
			$collection->insertMany($data);
			$schemaAnalyzer = new SchemaAnalyzer(null);
			$collectionSchema = $schemaAnalyzer->extractCollectionSchema("test", "products", $collection);
			$tableStr = $collectionSchema->getTableString();
			$correctOutput = $this->readFileAsString($c['output']);
			$this->assertEquals($tableStr, $correctOutput);
		}
	}

}


?>
