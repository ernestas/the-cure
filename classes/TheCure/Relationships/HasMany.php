<?php
/**
 * A relationship between one and many models
 *
 * @package     TheCure
 * @category    Relationship
 * @category    Attribute
 * @copyright   Gignite, 2012
 * @license     MIT
 */
namespace TheCure\Relationships;

use TheCure\Container;
use TheCure\ObjectAccessor;
use TheCure\Relation;
use TheCure\Models\Model;

class HasMany extends Has implements Relation\Add, Relation\Remove, Relation\Contains {

	protected function where($object)
	{
		return array(
			'_id' => array('$in' => $object->{$this->name()}),
		);
	}

	/**
	 * Find a Collection of relations.
	 * 
	 * @param   Container
	 * @param   Model
	 * @return  Collection
	 */
	public function find(Container $container, Model $model)
	{
		$accessor = new ObjectAccessor;
		$object = $accessor->get($model);
		$where = $this->where($object);
		return $this->mapper($container)->find(
			$where,
			$this->model_suffix());
	}

	/**
	 * Determine if a Model is contained within a relation.
	 * 
	 * @param   Container
	 * @param   Model
	 * @param   Model
	 * @return  boolean
	 */
	public function contains(
		Container $container,
		Model $model,
		Model $relation)
	{
		$accessor = new ObjectAccessor;
		
		$object = $accessor->get($model);
		$relations = $object->{$this->name()};

		if ( ! is_array($relations))
		{
			return FALSE;
		}

		return in_array($accessor->get($relation)->_id, $relations);
	}

	/**
	 * Add a $relation to a $model's collection of relations.
	 * 
	 * @param   Container
	 * @param   Model
	 * @param   Model
	 * @return  mixed
	 */
	public function add(Container $container, Model $model, Model $relation)
	{
		$accessor = new ObjectAccessor;
		$relation_object = $accessor->get($relation);

		if ( ! isset($relation_object->_id))
		{
			// If not saved we save the model first
			$this->mapper($container)->save($relation);
		}

		$object = $accessor->get($model);

		if (isset($object->{$this->name()}))
		{
			$relations = $object->{$this->name()};
		}
		else
		{
			$relations = array();
		}

		$relations[] = $accessor->get($relation)->_id;
		$object->{$this->name()} = $relations;
	}

	/**
	 * Remove one Relation from a Collection of relations.
	 * 
	 * @param   Container
	 * @param   Model
	 * @param   Model
	 * @return  void
	 */
	public function remove(Container $container, Model $model, Model $relation)
	{
		$accessor = new ObjectAccessor;
		$model_object = $accessor->get($model);

		if (isset($model_object->{$this->name()}))
		{
			$ids = $model_object->{$this->name()};
		
			foreach ($ids as $_k => $_id)
			{
				if ($_id == $accessor->get($relation)->_id)
				{
					$relations = $model_object->{$this->name()};
					unset($relations[$_k]);
					$model_object->{$this->name()} = $relations;
					return;
				}
			}
			
			throw new Relation\NotFoundException;
		}

		throw new Relation\FieldNotFoundException;
	}

}