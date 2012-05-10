<?php
/**
 * Find relation interface
 *
 * @package     TheCure
 * @category    Relation
 * @category    Relationships
 * @copyright   Gignite, 2012
 */
namespace Gignite\TheCure\Relation;

use Gignite\TheCure\Container;
use Gignite\TheCure\Models\Model;

interface Find {

	/**
	 * Find a Collection of relations or a single relation.
	 * 
	 * @param   Container
	 * @param   Model
	 * @return  Collection|Model
	 */
	public function find(Container $container, Model $model);

}