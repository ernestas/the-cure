<?php
namespace TheCure\Specs;
/**
 * Test MySQL mapper
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
 * @group  mappers.mysql
 */
use TheCure\Factories\Factory;

use TheCure\Maps\IdentityMap;

use TheCure\TransferObjects\TransferObject;

use TheCure\Mappers;

use TheCure\Connections\MysqlConnection;

class MapperMysqlTest extends MapperTest {

	protected static $mapper;

	protected static function config()
	{
		return array(
			'server'   => '127.0.0.1',
			'db'       => 'test',
			'table'    => 'user',
			'username' => 'username',
			'password' => 'password',
		);
	}

	protected static function db()
	{
		$config = static::config();

		return new \mysqli(
			\Arr::get($config, 'server'),
			\Arr::get($config, 'username'),
			\Arr::get($config, 'password'),
			\Arr::get($config, 'db')
		);
	}

	protected static function prepareData($mapper)
	{
		if (class_exists('mysqli'))
		{
			$config = static::config();
			$db = static::db();
			$db->query('INSERT INTO '.\Arr::get($config, 'table').
				' (name) VALUES(\'Luke\')');
			$id = $db->insert_id;
			return new TransferObject(array('_id' => $id, 'name' => 'Luke'));
		}
	}

	protected static function mapper()
	{
		$mapper = new Mappers\Mysql\UserMapper;
		$mapper->connection(new MysqlConnection(static::config()));
		$mapper->identities(new IdentityMap);
		$mapper->factory(
			new Factory(\Kohana::$config->load('the-cure.factory')));
		$mapper->config(array('queryOptions' => array('safe' => TRUE)));

		return $mapper;
	}

}
