<?php
/**
 * An interface for a mapper that uses a factory
 *
 * @package     TheCure
 * @category    Mapper
 * @copyright   Gignite, 2012
 * @license     MIT
 */
namespace TheCure\Mapper;

use TheCure\Factory;

interface FactorySetGet {
	
	/**
	 * Get/set the factory.
	 *
	 * @param   Factory  $factory If setting
	 * @return  Factory  If getting
	 */
	public function factory(Factory $factory = NULL);

}