<?php

namespace MongoSchemist;

use MongoDB\BSON\Binary;
use MongoDB\BSON\DBPointer;
use MongoDB\BSON\Decimal128;
use MongoDB\BSON\Int64;
use MongoDB\BSON\Javascript;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\BSON\Timestamp;
use MongoDB\BSON\Undefined;
use MongoDB\BSON\UTCDateTimeInterface;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;

class FieldType {

	// All available types
	const TYPE_NUMBER                    = 1;
	const TYPE_STRING                    = 2;
	const TYPE_OBJECT                    = 3;
	const TYPE_ARRAY                     = 4;
	const TYPE_BINARY_DATA_GENERIC       = 50;
	const TYPE_BINARY_DATA_FUNCTION      = 51;
	const TYPE_BINARY_DATA_OLD_BINARY    = 52;
	const TYPE_BINARY_DATA_OLD_UUID      = 53;
	const TYPE_BINARY_DATA_UUID          = 54;
	const TYPE_BINARY_DATA_MD5           = 55;
	const TYPE_BINARY_DATA_ENCRYPTED     = 56;
	const TYPE_BINARY_DATA_USED_DEFINED  = 57;
	const TYPE_UNDEFINED                 = 6;
	const TYPE_OBJECT_ID                 = 7;
	const TYPE_BOOLEAN                   = 8;
	const TYPE_DATE                      = 9;
	const TYPE_NULL                      = 10;
	const TYPE_REGEX                     = 11;
	const TYPE_DB_POINTER                = 12;
	const TYPE_JAVASCRIPT                = 13;
	const TYPE_TIMESTAMP                 = 14;
	const TYPE_LONG                      = 15;
	const TYPE_DECIMAL                   = 16;
	const TYPE_UNKNOWN                   = 17;

	// The equivalent type names
	const TYPE_NAMES = [
		self::TYPE_NUMBER                    => 'Number',
		self::TYPE_STRING                    => 'String',
		self::TYPE_OBJECT                    => 'Object',
		self::TYPE_ARRAY                     => 'Array',
		self::TYPE_BINARY_DATA_GENERIC       => 'Binary Data-Generic',
		self::TYPE_BINARY_DATA_FUNCTION      => 'Binary Data-Function',
		self::TYPE_BINARY_DATA_OLD_BINARY    => 'Binary Data-Old Binary',
		self::TYPE_BINARY_DATA_OLD_UUID      => 'Binary Data-Old UUID',
		self::TYPE_BINARY_DATA_UUID          => 'Binary Data-UUID',
		self::TYPE_BINARY_DATA_MD5           => 'Binary Data-MD5',
		self::TYPE_BINARY_DATA_ENCRYPTED     => 'Binary Data-Encrypted',
		self::TYPE_BINARY_DATA_USED_DEFINED  => 'Binary Data-User Defined',
		self::TYPE_UNDEFINED                 => 'Undefined',
		self::TYPE_OBJECT_ID                 => 'Object Id',
		self::TYPE_BOOLEAN                   => 'Boolean',
		self::TYPE_DATE                      => 'Date',
		self::TYPE_NULL                      => 'Null',
		self::TYPE_REGEX                     => 'Regex',
		self::TYPE_DB_POINTER                => 'DB Pointer',
		self::TYPE_JAVASCRIPT                => 'JavaScript',
		self::TYPE_TIMESTAMP                 => 'Timestamp',
		self::TYPE_LONG                      => 'Long',
		self::TYPE_DECIMAL                   => 'Decimal',
		self::TYPE_UNKNOWN                   => 'Unknown'
	];

	/**
	 * Constructor (singleton).
	 */
	private function __construct() { }

	/**
	 * Returns the name of the type.
	 *
	 * @param integer $type
	 * @return string
	 */
	public static function getTypeName($type) {
		return self::TYPE_NAMES[$type];
	}

	/**
	 * Resolves a value to a BSON type.
	 *
	 * @param mixed $val
	 * @return integer
	 */
	public static function resolveType($val) {
		if ($val === null) {
			return self::TYPE_NULL;
		} else if ($val instanceof ObjectId) {
			return self::TYPE_OBJECT_ID;
		} else if ($val instanceof BSONDocument) {
			return self::TYPE_OBJECT;
		} else if ($val instanceof BSONArray) {
			return self::TYPE_ARRAY;
		} else if ($val instanceof Binary) {
			if ($val->getType() == Binary::TYPE_GENERIC) {
				return self::TYPE_BINARY_DATA_GENERIC;
			} else if ($val->getType() == Binary::TYPE_FUNCTION) {
				return self::TYPE_BINARY_DATA_FUNCTION;
			} else if ($val->getType() == Binary::TYPE_OLD_BINARY) {
				return self::TYPE_BINARY_DATA_OLD_BINARY;
			} else if ($val->getType() == Binary::TYPE_OLD_UUID) {
				return self::TYPE_BINARY_DATA_OLD_UUID;
			} else if ($val->getType() == Binary::TYPE_UUID) {
				return self::TYPE_BINARY_DATA_UUID;
			} else if ($val->getType() == Binary::TYPE_MD5) {
				return self::TYPE_BINARY_DATA_MD5;
			} else if ($val->getType() == Binary::TYPE_ENCRYPTED) {
				return self::TYPE_BINARY_DATA_ENCRYPTED;
			}
			return self::TYPE_BINARY_DATA_USER_DEFINED;
		} else if ($val instanceof UTCDateTimeInterface) {
			return self::TYPE_DATE;
		} else if ($val instanceof Timestamp) {
			return self::TYPE_TIMESTAMP;
		} else if ($val instanceof Regex) {
			return self::TYPE_REGEX;
		} else if ($val instanceof DBPointer) {
			return self::TYPE_DB_POINTER;
		} else if ($val instanceof Javascript) {
			return self::TYPE_JAVASCRIPT;
		} else if ($val instanceof Int64) {
			return self::TYPE_LONG;
		} else if ($val instanceof Decimal128) {
			return self::TYPE_DECIMAL;
		} else if ($val instanceof Undefined) {
			return self::TYPE_UNDEFINED;
		} else if (is_bool($val)) {
			return self::TYPE_BOOLEAN;
		} else if (is_string($val)) {
			return self::TYPE_STRING;
		} else if (is_numeric($val)) {
			return self::TYPE_NUMBER;
		}
		// Could not resolve type
		return self::TYPE_UNKNOWN;
	}

}

?>
