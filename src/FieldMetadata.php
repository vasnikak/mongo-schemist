<?php

namespace MongoSchemist;

class FieldMetadata {

	/** @var string */
	const ROOT_NAME = '_root';

	/** @var string */
	private $name;

	/** @var integer */
	private $depth;

	/** @var array */
	private $typeIdx;

	/**
	 * Constructor.
	 *
	 * @param string  $name
	 * @param integer $depth
	 */
	public function __construct($name, $depth) {
		$this->name = $name;
		$this->depth = $depth;
		$this->typeIdx = array();
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return integer
	 */
	public function getDepth() {
		return $this->depth;
	}

	/**
	 * @return bool
	 */
	public function isRoot() {
		return ($this->depth == 0);
	}

	/**
	 * @return array
	 */
	public function getTypeIdx() {
		return $this->typeIdx;
	}

	/**
	 * Evaluates a value and adds its information into the field's metadata.
	 *
	 * @param mixed $val
	 * @param integer $maxDepth
	 */
	public function evaluateValue($val, $maxDepth) {
		$type = FieldType::resolveType($val);
		if (!isset($this->typeIdx[$type])) {
			$this->typeIdx[$type] = [
				'count' => 0,
				'children' => []
			];
		}
		$this->typeIdx[$type]['count']++;
		if ($this->depth <= $maxDepth) {
			if ($type == FieldType::TYPE_OBJECT) {
				foreach ($val as $fieldName => $childValue) {
					if (isset($this->typeIdx[$type]['children'][$fieldName])) {
						$child = $this->typeIdx[$type]['children'][$fieldName];
					} else {
						$child = new static($fieldName, $this->depth + 1);
						$this->typeIdx[$type]['children'][$fieldName] = $child;
					}
					$child->evaluateValue($childValue, $maxDepth);
				}
			} else if ($type == FieldType::TYPE_ARRAY) {
				foreach ($val as $childValue) {
					$nextIdx = strval(count($this->typeIdx[$type]['children']));
					$child = new static($nextIdx, $this->depth + 1);
					$child->evaluateValue($childValue, $maxDepth);
					$len = count($this->typeIdx[$type]['children']);
					$sameTypeIdx = -1;
					foreach ($this->typeIdx[$type]['children'] as $idx => $childObj) {
						if ($child->equals($childObj, false)) {
							$sameTypeIdx = $idx;
							break;
						}
					}
					if ($sameTypeIdx == -1) {
						$this->typeIdx[$type]['children'][$nextIdx] = $child;
					}
				}
			}
		}
	}

	/**
	 * Sorts each typeIdx in a descending order based on the `count` field.
	 */
	public function sortByCountDesc() {
		uasort($this->typeIdx, function($a, $b) {
			return ($b['count'] - $a['count']);
		});
		foreach ($this->typeIdx as $typeData) {
			foreach ($typeData['children'] as $child) {
				$child->sortByCountDesc();
			}
		}
	}

	/**
	 * Helper method that toArray method uses.
	 *
	 * @param &array $container
	 * @return array
	 */
	private function toArrayHelper(&$container) {
		$container[$this->name] = [];
		foreach ($this->typeIdx as $type => $typeData) {
			$typeName = FieldType::getTypeName($type);
			$container[$this->name][$typeName] = [
				'count' => $typeData['count']
			];
			if (!empty($typeData['children'])) {
				$container[$this->name][$typeName]['children'] = [];
				foreach ($typeData['children'] as $child) {
					$child->toArrayHelper($container[$this->name][$typeName]['children']);
				}
			}
		}
		return $container;
	}

	/**
	 * Returns the FieldMetadata tree as an array.
	 *
	 * @return array
	 */
	public function toArray() {
		$tree = [];
		$array = $this->toArrayHelper($tree);
		return isset($array[static::ROOT_NAME][FieldType::getTypeName(FieldType::TYPE_OBJECT)]['children']) ? 
			$array[static::ROOT_NAME][FieldType::getTypeName(FieldType::TYPE_OBJECT)]['children'] : [];
	}

	/**
	 * Checks if the object is equal to another one.
	 *
	 * @param mixed $obj  An object of any type
	 * @param bool $stict Strict type check
	 * @return bool
	 */
	public function equals($obj, $strict = true) {
		if (!($obj instanceof static)) {
			return false;
		}
		if ($strict && $this->name != $obj->name) {
			return false;
		}
		if ($this->depth != $obj->depth) {
			return false;
		}
		if (count($this->typeIdx) != count($obj->typeIdx)) {
			return false;
		}
		foreach ($this->typeIdx as $type => $typeData) {
			if (!isset($obj->typeIdx[$type])) {
				return false;
			}
			if ($strict && $typeData['count'] != $obj->typeIdx[$type]['count']) {
				return false;
			}
			if (count($typeData['children']) != count($obj->typeIdx[$type]['children'])) {
				return false;
			}
			foreach ($typeData['children'] as $fieldName => $child) {
				if (!isset($obj->typeIdx[$type]['children'][$fieldName])) {
					return false;
				}
				if (!$child->equals($obj->typeIdx[$type]['children'][$fieldName], $strict)) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Helper function for `getFieldsAsString` method.
	 *
	 * @param &array $fields
	 * @param self[] $path
	 */
	private function getFieldsAsStringHelper(&$fields, $path = []) {
		if (!$this->isRoot()) {
			$pathNames = [];
			foreach ($path as $ancestor) {
				if (!$ancestor->isRoot()) {
					$pathNames[] = $ancestor->getName();
				}
			}
			$pathNames[] = $this->getName();
			$types = [];
			foreach ($this->typeIdx as $type => $typeData) {
				$types[FieldType::getTypeName($type)] = $typeData['count'];
			}
			$fields[implode('.', $pathNames)] = $types;
		}
		foreach ($this->typeIdx as $type => $typeData) {
			if ($type != FieldType::TYPE_ARRAY) {
				foreach ($typeData['children'] as $child) {
					$child->getFieldsAsStringHelper($fields, array_merge($path, [$this]));
				}
			}
		}
	}

	/**
	 * Returns the fields in an array of strings.
	 *
	 * @return string[]
	 */
	public function getFieldsAsString() {
		$fields = [];
		$this->getFieldsAsStringHelper($fields);
		return $fields;
	}

}

?>
