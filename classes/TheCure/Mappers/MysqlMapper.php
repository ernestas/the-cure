<?php
/**
 * A MySQL mapper
 *
 *     $mapper->find(array('name' => 'Luke'));
 *     $mapper->find(array('name' => 'Luke'), 'Admin');
 *
 * @package     TheCure
 * @category    Mapper
 * @copyright   Gignite, 2012
 * @license     MIT
 */
namespace TheCure\Mappers;

use TheCure\TransferObjects\TransferObject;

use TheCure\Connections\Connection;

use TheCure\Mapper\ConnectionSetGet;

use TheCure\Mappers\Mapper;

use TheCure\Models\Model;

abstract class MysqlMapper extends Mapper implements ConnectionSetGet {

	protected $connection;

	/**
	 * Sets the connection property if one is passed in otherwise
	 * it returns the connection.
	 *
	 * @param  Connection|null $connection
	 * @return \myslqli
	 */
	public function connection(Connection $connection = NULL)
	{
		if ($connection === NULL)
		{
			return $this->connection->get();
		}

		$this->connection = $connection;
	}

	/**
	 * Prepares a SQL 'and where' clause from an array
	 *
	 * @static
	 * @param  array
	 * @return string
	 */
	public static function andWhere(array $where)
	{
		if ($where == NULL)
			return;

		$query = ' WHERE';
		foreach ($where as $key => $value)
		{
			$query .= ' '.$key.' = \''.$value.'\' AND';
		}

		return substr($query, 0, -4);
	}

	/**
	 * @param  null $suffix
	 * @param  array|null $where
	 * @return Model|Collection
	 */
	public function find(array $where = NULL, $suffix = NULL)
	{
		$connection = $this->connection();
		$collection = $this->collectionName();

		return $this->createCollection(
			$where,
			$suffix,
			function ($where) use ($connection, $collection)
			{
				$query = 'SELECT * FROM '.$collection;
				$query .= MysqlMapper::andWhere($where);

				if ($result = $connection->query($query))
				{
					$arr = array();
					while ($row = $result->fetch_assoc())
					{
						$arr[] = $row;
					}
					return new \ArrayIterator($arr);
				}

				return new \ArrayIterator(array());
			}
		);
	}

	/**
	 * @param  null $suffix
	 * @param  null $where
	 * @return mixed
	 */
	public function findOne($where = NULL, $suffix = NULL)
	{
		$connection = $this->connection();
		$collection = $this->collectionName();

		return $this->createModel(
			$where,
			$suffix,
			function ($where) use ($connection, $collection)
			{
				$query = 'SELECT * FROM '.$collection;
				$query .= MysqlMapper::andWhere($where);
				$query .= ' LIMIT 1';

				if ($row = $connection->query($query))
				{
					return new TransferObject($row->fetch_assoc());
				}

				return NULL;
			}
		);
	}

	/**
	 * Saves a models data to MySQL
	 *
	 * @param Model $model
	 */
	public function save(Model $model)
	{
		$connection = $this->connection();
		$collection = $this->collectionName();

		$this->saveModel(
			$model,
			function ($object) use ($connection, $collection)
			{
				$query = 'INSERT INTO '.$collection;

				$array = $object->asArray();
				$keys = ' (';
				$values = ' (';
				foreach ($array as $key => $value)
				{
					$keys .= $key.' ';
					$values .= '\''.$value.'\' ';
				}
				$keys .= ') ';
				$values .= ') ';

				$query .= $keys.'VALUES'.$values;
				$query .= 'ON DUPLICATE KEY UPDATE';

				foreach ($array as $key => $value)
				{
					$query .= ' '.$key.' = \''.$value.'\',';
				}

				$connection->query(substr($query, 0, -1));
				return new TransferObject($array);
			}
		);
	}

	/**
	 * @param $model
	 */
	public function delete($model)
	{
		$connection = $this->connection();
		$collection = $this->collectionName();

		$this->deleteModel(
			$model,
			function ($where) use ($connection, $collection)
			{
				$query = 'DELETE FROM '.$collection;
				if ($where['_id'] !== NULL)
				{
					$query .= MysqlMapper::andWhere($where);
				}
				else
				{
					$query .= ' LIMIT 1';
				}

				$connection->query($query);
			}
		);
	}

}
