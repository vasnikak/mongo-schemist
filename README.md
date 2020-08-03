# Mongo Schemist

A schema analyzer for MongoDB 

## Author and license

Vasileios Nikakis

This library is [MIT-licenced](LICENSE.txt).

## Synopsis and motivation

Schema extraction from a MongoDB collection is a well-known problem.

In the past many libraries have attempted to address the issue, with the most popular one being [Variety](https://github.com/variety/variety).

Due to the lack of:

* a library implementation for PHP and 
* missing features in other libraries as e.g. exporting the MongoDB collection's schema in a JSON format

`Mongo Schemist` was created.

The library is on its initial version and more features will be added with time.

## Installation

Through composer:

    $ composer require vasnikak/mongo-schemist

## Example

Let's assume a MongoDB collection `persons` inside the DB `test` that contains the following data:

	{
		"_id" : ObjectId("5f279b549cd3ba1b70f39df2"),
		"name" : "John Doe",
		"age" : 25,
		"address" : "The Street #20"
	}
	{
		"_id" : ObjectId("5f279b939cd3ba1b70f39df3"),
		"name" : "Ann Doe",
		"age" : "30y old",
		"profession" : "Physicist"
	}
	{
		"_id" : ObjectId("5f279bbe9cd3ba1b70f39df4"),
		"name" : "Nick Doe",
		"age" : 40,
		"address" : {
			"street" : "The Street",
			"number" : 40
		}
	}
	{
		"_id" : ObjectId("5f279c039cd3ba1b70f39df5"),
		"name" : "John Smit",
		"age" : 37,
		"address" : "A Street #50",
		"shifts" : [
			"08:00-16:00",
			"17:00-20:00"
		]
	}

We can use `Mongo Schemist` to extract the schema information of the collection:

	<?php

	require_once 'vendor/autoload.php';

	use MongoSchemist\MongoDBConnection;
	use MongoSchemist\SchemaAnalyzer;

	const CONN_STRING = "mongodb://localhost:27017";

	$mongo = MongoSchemist\MongoDBConnection::getClient(CONN_STRING);
	MongoDBConnection::testConnection($mongo);
	$schemaAnalyzer = new SchemaAnalyzer($mongo);
	$collectionSchema = $schemaAnalyzer->extractCollectionSchema("test", "persons");
	echo $collectionSchema->getTableString();

	?>

The result will be the following:

	+--------------------------------------------------------------+
	| keys           | types               | occurences | percents |
	|----------------|---------------------|------------|----------|
	| _id            | Object Id           |          4 |    100.0 |
	| name           | String              |          4 |    100.0 |
	| age            | Number(3),String(1) |          4 |    100.0 |
	| address        | String(2),Object(1) |          3 |     75.0 |
	| address.street | String              |          1 |     25.0 |
	| address.number | Number              |          1 |     25.0 |
	| profession     | String              |          1 |     25.0 |
	| shifts         | Array               |          1 |     25.0 |
	+--------------------------------------------------------------+
