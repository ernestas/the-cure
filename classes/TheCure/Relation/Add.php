<?php
/**
 * Add relation interface
 *
 * @package     TheCure
 * @category    Relation
 * @category    Relationships
 * @copyright   Gignite, 2012
 */
namespace TheCure\Relation;

use TheCure\Container;
use TheCure\Models\Model;

interface Add {

	/**
	 * Add a $relation to a $model's collection of relations.
	 * 
	 * @param   Container
	 * @param   Model
	 * @param   Model
	 * @return  mixed
	 */
	public function add(Container $container, Model $model, Model $relation);

}