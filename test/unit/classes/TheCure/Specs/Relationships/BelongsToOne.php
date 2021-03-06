<?php
namespace TheCure\Specs;
/**
 * Test belongs to one relationship
 *
 * @package     TheCure
 * @category    Relationship
 * @category    Spec
 * @category    Test
 * @copyright   Gignite, 2012
 * @license     MIT
 * 
 * @group  specs
 * @group  relationships
 * @group  relationships.belongstoone
 */
use TheCure\Object;
use TheCure\ObjectAccessor;
use TheCure\Models;
use TheCure\Container;
use TheCure\Relationships\BelongsToOne;

class RelationshipBelongsToOne extends \PHPUnit_Framework_TestCase {

	protected function relationship()
	{
		$config = array(
			'mapper_suffix' => 'User',
			'model_suffix'  => 'Admin',
			'foreign'       => 'best_friend',
		);
		return new BelongsToOne('best_friender', $config);
	}

	protected function container()
	{
		return new Container('Mock');
	}

	public function testItShouldFindARelatedModel()
	{
		$container = $this->container();
		$model = new Models\User\Admin;
		$accessor = new ObjectAccessor;
		$accessor->set($model, array('best_friend' => 0));
		$container->mapper('User')->save($model);
		$collection = $this->relationship()->find($container, $model);
		$this->assertInstanceOf('TheCure\Models\User\Admin', $collection);
	}

}

