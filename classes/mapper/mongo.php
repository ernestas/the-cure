<?php

abstract class Mapper_Mongo extends Mapper implements MapperConnection {

	protected $connection;

	public function connection(Connection $connection = NULL)
	{
		if ($connection === NULL)
		{
			return $this->connection;
		}
		
		$this->connection = $connection;
	}

	protected function collection()
	{
		return $this->connection()->get()->selectCollection(
			$this->collection_name());
	}

	protected function query_options()
	{
		return $this->config('query_options', array());
	}

	public function find($suffix = NULL, array $where = NULL)
	{
		$collection = $this->collection();

		return $this->create_collection(
			$suffix,
			$where,
			function ($where) use ($collection)
			{
				return $collection->find($where);
			});
	}

	public function find_one($suffix = NULL, $where = NULL)
	{
		$collection = $this->collection();

		return $this->create_model(
			$suffix,
			$where,
			function ($where) use ($collection)
			{
				return (object) $collection->findOne($where);
			});
	}
	
	public function save(Model $model)
	{
		$collection = $this->collection();
		$options = $this->query_options();

		$this->save_model(
			$model,
			function ($object) use ($collection, $options)
			{
				$array = (array) $object;

				if (isset($object->_id))
				{
					$collection->update(
						array('_id' => $object->_id),
						$array,
						$options);
				}
				else
				{
					$collection->insert($array, $options);
				}

				return (object) $array;
			});
	}

	public function delete($model)
	{
		$collection = $this->collection();
		$options = $this->query_options();

		$this->delete_model(
			$model,
			function ($where) use ($collection, $options)
			{
				$collection->remove($where, $options);
			});
	}

}