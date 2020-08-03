<?php

namespace MongoSchemist;

use MongoSchemist\Util\Util;

class CollectionSchema {

	/** @var string */
	private $dbName;

	/** @var string */
	private $collectionName;

	/** @var integer */
	private $recordNum;

	/** @var FieldMetadata */
	private $root;

	/** @var integer */
	private $maxDepth;

	/**
	 * Constuctor.
	 *
	 * @param string $dbName     DB name
	 * @param string $collection Collection name
	 * @param integer $maxDepth  Maximum document depth
	 */
	public function __construct($dbName, $collectionName, $maxDepth = 1) {
		$this->dbName = $dbName;
		$this->collectionName = $collectionName;
		$this->root = new FieldMetadata(FieldMetadata::ROOT_NAME, 0);
		$this->maxDepth = $maxDepth;
	}

	/**
	 * @return string
	 */
	public function getDBName() {
		return $this->dbName;
	}

	/**
	 * @return string
	 */
	public function getCollectionName() {
		return $this->collectionName;
	}

	/**
	 * @return integer
	 */
	public function getRecordNum() {
		return $this->recordNum;
	}

	/**
	 * @param integer $recordNum
	 * @return self
	 */
	public function setRecordNum($recordNum) {
		$this->recordNum = $recordNum;
		return $this;
	}

	/**
	 * Evaluates a document.
	 *
	 * @param MongoDB\Model\BSONDocument $document
	 */
	public function evaluateDocument($document) {
		$this->root->evaluateValue($document, $this->maxDepth);
	}

	/**
	 * Sorts fields in a descending order based on the `count` field.
	 */
	public function sortByCountDesc() {
		$this->root->sortByCountDesc();
		return $this;
	}

	/**
	 * Returns the schema tree as an array.
	 *
	 * @return array
	 */
	public function toArray() {
		return $this->root->toArray();
	}

	/**
	 * Checks if the object is equal to another one.
	 *
	 * @param mixed $obj An object of any type
	 * @return bool
	 */
	public function equals($obj) {
		if (!($obj instanceof CollectionSchema)) {
			return false;
		}
		return $this->root->equals($obj->root);
	}

	/**
	 * Returns the schema information in a table format.
	 *
	 * @return string
	 */
	public function getTableString() {
		$fields = $this->root->getFieldsAsString();
		$rows = [];
		foreach ($fields as $field => $types) {
			$fieldTypes = [];
			$occurences = 0;
			foreach ($types as $type => $count) {
				if (count($types) == 1) {
					$fieldTypes[] = $type;
				} else {
					$fieldTypes[] = $type . '(' . $count . ')';
				}
				$occurences += $count;
			}
			$pc = (($this->recordNum > 0) ? round($occurences / $this->recordNum, 4) : 0.00) * 100;
			$rows[] = [
				[$field, 'L'],
				[implode(',', $fieldTypes), 'L'],
				[$occurences, 'R'],
				[sprintf('%.1f', $pc), 'R']
			];
		}
		usort($rows, function($a, $b) {
			return ($b[2][0] - $a[2][0]);
		});
		array_unshift($rows, [ ['keys', 'L'], ['types', 'L'], ['occurences', 'R'], ['percents', 'R'] ]);
		return Util::getTableString($rows);
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return 
			'DB: {$this->dbName}' . PHP_EOL . 
			'Collection: {$this->collectionName}' . PHP_EOL . 
			'Record number: {$this->recordNum}' . PHP_EOL . 
			$this->getTableString();
	}

}

?>
