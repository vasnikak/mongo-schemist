<?php

namespace MongoSchemist\Util;

class Util {

	/**
	 * Constructor (singleton).
	 */
	private function __construct() { }

	/**
	 * Returns a string with the array's data in a table template.
	 *
	 * @param array $rows
	 * @return string
	 */
	public static function getTableString($rows) {
		// Find the character length for each field
		$colCount = isset($rows[0]) ? count($rows[0]) : 0;
		$fieldLen = array_fill(0, $colCount, 0);
		foreach ($rows as $row) {
			for ($i = 0; $i < $colCount; $i++) {
				// Two additional characters in front, end
				$len = mb_strlen($row[$i][0]);
				if ($len > $fieldLen[$i]) {
					$fieldLen[$i] = $len;
				}
			}
		}

		// Find the total character length of each row
		$totalRowLen = 3*$colCount - 1;
		foreach ($fieldLen as $len) {
			$totalRowLen += $len;
		}

		// Create the first and the last line
		$first = $last = '+' . str_repeat('-', $totalRowLen) . '+';

		// Helper function
		$printLR = function($item, $charNum, $extraChar = ' ') {
			$len = mb_strlen($item[0]);
			return $extraChar . (($item[1] == 'L') ? 
				$item[0] . str_repeat($extraChar, $charNum - $len) : 
				str_repeat($extraChar, $charNum - $len) . $item[0]) . $extraChar;
		};

		// Create the table
		$table = $first . PHP_EOL;
		// Headers
		if (isset($rows[0])) {
			$table .= '|';
			for ($j = 0; $j < $colCount; $j++) {
				$table .= $printLR($rows[0][$j], $fieldLen[$j]) . '|';
			}
			$table .= PHP_EOL;
			$table .= '|';
			for ($j = 0; $j < $colCount; $j++) {
				$table .= $printLR([str_repeat('-', $fieldLen[$j] - 1), 'L'], $fieldLen[$j], '-') . '|';
			}
			$table .= PHP_EOL;
		}
		// Body
		if (isset($rows[1])) {
			$rowsCount = count($rows);
			for ($i = 1; $i < $rowsCount; $i++) {
				$table .= '|';
				for ($j = 0; $j < $colCount; $j++) {
					$table .= $printLR($rows[$i][$j], $fieldLen[$j]) . '|';
				}
				$table .= PHP_EOL;
			}
		}
		$table .= $last . PHP_EOL;
		return $table;
	}

}

?>
