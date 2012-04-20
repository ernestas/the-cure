<?php

abstract class Mapper implements MapperActions {

	protected $connection;

	protected $identities;

	protected $config;

	public function connection(Connection $connection = NULL)
	{
		if ($connection === NULL)
		{
			return $this->connection;
		}
		
		$this->connection = $connection;
	}

	public function identities(IdentityMap $identities = NULL)
	{
		if ($identities === NULL)
		{
			return $this->identities;
		}
		
		$this->identities = $identities;
	}

	public function config($config = NULL, $default = NULL)
	{
		if ($config === NULL)
		{
			return $this->config;
		}
		elseif (is_array($config))
		{
			$this->config = $config;
		}
		else
		{
			return Arr::get($this->config, $config, $default);
		}
	}

	protected function domain_name()
	{
		$class = get_class($this);
		$domain = str_replace('Mapper_', '', $class);
		$domain = substr($domain, strpos($domain, '_') + 1);
		return $domain;
	}
	
	protected function collection_name()
	{
		$collection = strtolower($this->domain_name());
		return $collection;
	}

	protected function model_class($suffix = NULL)
	{
		$model = "Model_{$this->domain_name()}";

		if ($suffix !== NULL)
		{
			$model .= "_{$suffix}";
		}

		return $model;
	}

	protected function is_valid_model(Model $model)
	{
		$class = $this->model_class();
		return $model instanceOf $class;
	}

	protected function assert_valid_model(Model $model)
	{
		if ( ! $this->is_valid_model($model))
		{
			throw new UnexpectedValueException(
				get_class($model).' should descend from '.$this->model_class());
		}
	}
	
	protected function create_collection($suffix, $where, $callback)
	{
		if ($where === NULL)
		{
			if ($suffix === NULL OR is_string($suffix))
			{
				$where = array();
			}
			elseif (is_array($suffix))
			{
				$where = $suffix;
				$suffix = NULL;
			}
			else
			{
				throw new InvalidArgumentException;
			}
		}

		$cursor = call_user_func($callback, $where);
		$class = $this->model_class($suffix);
		return new Collection_Model($cursor, $this->identities(), $class);
	}

	/**
	 * [!!] We probably always need to check to see if Model
	 *      actually exists even if pulled from identities.
	 */
	protected function create_model($suffix, $id, $callback)
	{
		if ($id === NULL)
		{
			$id = $suffix;
			$suffix = NULL;
		}
		
		$class = $this->model_class($suffix);

		if ($model = $this->identities()->get($class, $id))
		{
			// We got it
		}
		else
		{
			$object = call_user_func($callback, $id);

			$model = new $class;
			$model->__object($object);
		}

		return $model;
	}

	protected function save_model(Model $model, $callback)
	{
		$this->assert_valid_model($model);

		$object = $model->__object();
		$object = call_user_func($callback, $object);
		$model->__object($object);

		if ( ! $this->identities()->has($model))
		{
			$this->identities()->set($model);
		}
	}

	protected function delete_model($model, $callback)
	{
		if ($model instanceOf Model)
		{
			$remove = array('_id' => $model->__object()->_id);
		}
		elseif ($model instanceOf Collection)
		{
			foreach ($model as $_id => $_model)
			{
				$this->delete($_id);
			}

			return;
		}
		else
		{
			$remove = $model;
		}
		
		call_user_func($callback, $remove);

		if ($this->identities()->has($model))
		{
			$this->identities()->delete($model);
		}
	}

}