<?php
namespace TheCure\Specs;
/**
 * Test has one relationship
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
 * @group  relationships.hasone
 */
use TheCure\Object;
use TheCure\Models;
use TheCure\Container;
use TheCure\Relationships\HasOne;

class RelationshipHasOne extends \PHPUnit_Framework_TestCase {

	protected function relationship()
	{
		$config = array(
			'mapper_suffix' => 'User',
			'model_suffix' => 'Admin',
		);
		return new HasOne('best_friend', $config);
	}

	protected function container()
	{
		return new Container('Mock');
	}

	public function testItShouldFindARelatedModel()
	{
		$container = $this->container();
		$model = new Models\User\Admin;
		$model->__object(new Object(array(
			'best_friend' => 0,
		)));
		$container->mapper('User')->save($model);
		$collection = $this->relationship()->find($container, $model);
		$this->assertInstanceOf('TheCure\Models\User\Admin', $collection);
	}

	public function testItShouldSaveRelationWhenSetting()
	{
		$container = $this->container();

		$model = new Models\User\Admin;

		$relationObject = new Object(array('name' => 'Luke'));
		$relation = new Models\User\Admin;
		$relation->__object($relationObject);

		$this->relationship()->set($container, $model, $relation);
		$this->assertTrue(isset($relationObject->_id));
	
		return array($container, $model, $relation);
	}

	/**
	 * @depends testItShouldSaveRelationWhenSetting
	 */
	public function testItShouldSetRelationOnObject($args)
	{
		list($container, $model, $relation) = $args;

		$modelObject = $model->__object();
		$relationObject = $relation->__object();
		$relationshipName = $this->relationship()->name();

		$this->assertSame(
			$relationObject->_id, 
			$modelObject->{$relationshipName});

		return array($container, $model, $relation);
	}

	/**
	 * @depends testItShouldSetRelationOnObject
	 */
	public function testItShouldDeleteRelationOnObject($args)
	{
		list($container, $model, $relation) = $args;

		$relationship = $this->relationship();
		$relationship->delete($container, $model);

		$modelObject = $model->__object();
		$this->assertTrue(empty($modelObject->{$relationship->name()}));
	}

	/**
	 * @expectedException  TheCure\Relation\FieldNotFoundException
	 */
	public function testItShouldThrowExceptionWhenRelationFieldNotExists()
	{
		$this->relationship()->delete($this->container(), new Models\User\Admin);
	}

}

