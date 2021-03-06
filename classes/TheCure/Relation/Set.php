<?php
/**
 * Set relation interface
 *
 * @package     TheCure
 * @category    Relation
 * @category    Relationship
 * @category    Attribute
 * @copyright   Gignite, 2012
 * @license     MIT
 */
namespace TheCure\Relation;

use TheCure\Container;
use TheCure\Models\Model;

interface Set {

	/**
	 * Set the one and only relation.
	 * 
	 * @param   Container
	 * @param   Model
	 * @param   Model
	 * @return  void
	 */
	public function set(Container $container, Model $model, Model $relation);

}