<?php
/**
 * An abstract mapper
 *
 *     $mapper->find($id);
 *     $mapper->find(array('name' => 'Luke'));
 *     $mapper->find('Admin', array('name' => 'Luke'));
 *
 * @package     TheCure
 * @category    Mappers
 * @copyright   Gignite, 2012
 */
namespace Gignite\TheCure\Mappers;

use Gignite\TheCure\Factory;
use Gignite\TheCure\IdentityMap;

use Gignite\TheCure\Container;

use Gignite\TheCure\Mapper\Actions as MapperActions;
use Gignite\TheCure\Mapper\ContainerSetGet;
use Gignite\TheCure\Mapper\FactorySetGet;
use Gignite\TheCure\Mapper\IdentitiesSetGet;
use Gignite\TheCure\Mapper\ConfigSetGet;

use Gignite\TheCure\Models\Model;
use Gignite\TheCure\Models\Magic as MagicModel;

use Gignite\TheCure\Collections\Collection;
use Gignite\TheCure\Collections\Model as ModelCollection;

abstract class Mapper
	implements MapperActions, FactorySetGet, IdentitiesSetGet,
		ConfigSetGet, ContainerSetGet {

	protected $container;
	protected $identities;
	protected $factory;
	protected $config;

	/**
	 * @param   Container|null  $container
	 * @return  mixed
	 */
	public function container(Container $container = NULL)
	{
		if ($container === NULL)
		{
			return $this->container;
		}

		$this->container = $container;
	}

	/**
	 * @param IdentityMap|null $identities
	 * @return mixed
	 */
	public function identities(IdentityMap $identities = NULL)
	{
		if ($identities === NULL)
		{
			return $this->identities;
		}

		$this->identities = $identities;
	}

	/**
	 * @param $config
	 * @return mixed
	 */
	public function config($config)
	{
		if (is_array($config))
		{
			$this->config = $config;
		}
		elseif ($this->config AND isset($this->config[$config]))
		{
			return $this->config[$config];
		}
	}

	/**
	 * @param  Factory|null $factory
	 * @return mixed
	 */
	public function factory(Factory $factory = NULL)
	{
		if ($factory === NULL)
		{
			return $this->factory;
		}

		$this->factory = $factory;
	}

	/**
	 * @return string
	 */
	protected function collection_name()
	{
		$collection = strtolower($this->factory()->domain($this));
		return $collection;
	}

	/**
	 * @param  null $suffix
	 * @return mixed
	 */
	protected function model_class($suffix = NULL)
	{
		return $this->factory()->model($this, $suffix);
	}
	
	public function model($suffix = NULL, array $args = array())
	{
		if (is_array($suffix))
		{
			$args = $suffix;
			$suffix = NULL;
		}

		$class = $this->model_class($suffix);
		$reflection = new \ReflectionClass($class);
		$model = $reflection->newInstanceArgs($args);

		if ($model instanceOf MagicModel)
		{
			$model->__container($this->container());
		}

		return $model;
	}

	/**
	 * @param  $suffix
	 * @param  $where
	 * @param  $callback
	 * @return Collections\Model
	 * @throws \InvalidArgumentException
	 */
	protected function create_collection($where, $suffix, $callback)
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
				throw new \InvalidArgumentException;
			}
		}

		$cursor = call_user_func($callback, $where);
		$class = $this->model_class($suffix);
		$collection = new ModelCollection($cursor, $this->identities(), $class);

		if (new $class instanceOf MagicModel)
		{
			$collection->container($this->container());
		}

		return $collection;
	}

	/**
	 * [!!] We probably always need to check to see if Model
	 *      actually exists even if pulled from identities.
	 *
	 * @param  $suffix
	 * @param  $where
	 * @param  $callback
	 * @return mixed
	 */
	protected function create_model($where, $suffix, $callback)
	{
		if ($where === NULL)
		{
			if ($suffix === NULL OR is_string($suffix))
			{
				$where = array();
			}
			else
			{
				$where = $suffix;
				$suffix = NULL;
			}
		}

		if ( ! is_array($where))
		{
			$where = array('_id' => $where);
		}

		$object = call_user_func($callback, $where);

		if ( ! isset($object->_id))
		{
			return;
		}

		$identities = $this->identities();
		$class = $this->model_class($suffix);

		if ( ! $model = $identities->get($class, $object->_id))
		{
			$model = $this->model($suffix);

			if ($object)
			{
				$model->__object($object);
			}

			$identities->set($model);
		}

		return $model;
	}

	/**
	 * @param Model $model
	 * @param       $callback
	 */
	protected function save_model(Model $model, $callback)
	{
		$object = $model->__object();
		$object = call_user_func($callback, $object);
		$model->__object($object);

		if ($model instanceOf MagicModel
			AND $container = $this->container())
		{
			$model->__container($container);
		}

		if ( ! $this->identities()->has($model))
		{
			$this->identities()->set($model);
		}
	}

	/**
	 * @param  $model
	 * @param  $callback
	 * @return mixed
	 */
	protected function delete_model($model, $callback)
	{
		if ($model instanceOf Model)
		{
			$id = $model->__object()->_id;
			$remove = array('_id' => $id);
		}
		elseif ($model instanceOf Collection)
		{
			foreach ($model as $_model)
			{
				$this->delete_model($_model, $callback);
			}
			return;
		}
		else
		{
			$this->delete_model($this->find($model), $callback);
			return;
		}

		call_user_func($callback, $remove);

		if ($this->identities()->has($model))
		{
			$this->identities()->delete($model);
		}
	}

}