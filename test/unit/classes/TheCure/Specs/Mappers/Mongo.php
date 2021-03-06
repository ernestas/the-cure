<?php
namespace TheCure\Specs;
/**
 * Test MongoDB mapper
 *
 * @package     TheCure
 * @category    Mapper
 * @category    Spec
 * @category    Test
 * @copyright   Gignite, 2012
 * @license     MIT
 * 
 * @group  specs
 * @group  mappers
 * @group  mappers.mongo
 */
use TheCure\Factory;
use TheCure\IdentityMap;
use TheCure\Object;
use TheCure\Mappers;
use TheCure\Connections\Mongo as MongoConnection;

class MapperMongoTest extends MapperTest {

	protected static $mapper;

	protected static function config()
	{
		return array(
			'server'     => 'mongodb://127.0.0.1',
			'db'         => 'test',
			'collection' => 'user',
		);
	}

	protected static function db()
	{
		$config = static::config();
		$connection = new \Mongo(\Arr::get($config, 'server'));
		return $connection->selectDB(\Arr::get($config, 'db'));
	}

	protected static function collection($db)
	{
		$config = static::config();
		return $db->selectCollection(\Arr::get($config, 'collection'));
	}

	protected static function prepareData()
	{
		if (class_exists('Mongo'))
		{
			$db = static::db();
			$db->drop();
			$collection = static::collection(static::db());
			$data = array('name' => 'Luke');
			$collection->insert($data);
			return new Object($data);
		}
	}

	protected static function mapper()
	{
		if (static::$mapper === NULL)
		{
			$mapper = new Mappers\Mongo\User;
			$mapper->connection(new MongoConnection(static::config()));
			$mapper->identities(new IdentityMap);
			$mapper->factory(
				new Factory(\Kohana::$config->load('the-cure.factory')));
			$mapper->config(array('query_options' => array('safe' => TRUE)));
			static::$mapper = $mapper;
		}

		return static::$mapper;
	}

}