<?php
namespace TheCure\Specs;
/**
 * Test the magic provided by an enhanced base domain class
 *
 * @package     TheCure
 * @category    Model
 * @category    Spec
 * @category    Test
 * @copyright   Gignite, 2012
 * @license     MIT
 * 
 * @group  specs
 * @group  models
 * @group  models.magic
 */
use TheCure\Object;
use TheCure\ObjectAccessor;
use TheCure\Models;
use TheCure\Relationships;
use TheCure\Container;
use TheCure\Field;

class ModelMagic extends \PHPUnit_Framework_TestCase {

	protected $container;

	protected function container()
	{
		if ($this->container === NULL)
		{
			$container = new Container('Mock');
			$accessor = new ObjectAccessor;

			$jim = $container->mapper('User')->model('Magic');
			$accessor->set($jim, array('name' => 'Jim'));
			$container->mapper('User')->save($jim);

			$luke = $container->mapper('User')->model('Magic');
			$accessor->set($luke, array(
				'name'    => 'Luke',
				'friends' => array($jim->__object()->_id),
			));
			$container->mapper('User')->save($luke);

			$this->container = $container;
		}
		
		return $this->container;
	}

	protected function user($expectedName)
	{
		$container = $this->container();
		return $container->mapper('User')->find_one(
			array('name' => $expectedName),
			'Magic');
	}

	public function providerTestMagicCall()
	{
		$expectedName = 'Luke';
		$luke = $this->user($expectedName);

		return array(
			array($luke, $expectedName, array()),
			array($luke, $expectedName = 'Bob', array($expectedName)),
		);
	}

	/**
	 * @dataProvider  providerTestMagicCall
	 */
	public function testMagicCall($model, $expectedName, $args)
	{
		if ($args)
		{
			call_user_func_array(array($model, 'name'), $args);
		}

		$this->assertSame($expectedName, $model->name());
	}

	public function testItShouldUseDefaultValueWhenNoValueSet()
	{
		$this->assertSame(1, $this->user('Jim')->age());
	}

	public function providerModelWithMockableRelation()
	{
		$args = array(new Models\User);

		return array(
			array(
				new Models\MockableAttribute,
				'relation',
				'find',
				array(),
			),
			array(
				new Models\MockableAttribute,
				'add_relation',
				'add',
				$args,
			),
			array(
				new Models\MockableAttribute,
				'remove_relation',
				'remove',
				$args,
			),
			array(
				new Models\MockableAttribute,
				'delete_relation',
				'delete',
				$args,
			),
			array(
				new Models\MockableAttribute,
				'relation',
				'set',
				$args,
			),
		);
	}

	/**
	 * @dataProvider  providerModelWithMockableRelation
	 */
	public function testMagicCallRelationship($model, $method, $expected, $args)
	{
		$mock = new Relationships\Mock('relation');

		Models\MockableAttribute::$attribute = function () use ($mock)
		{
			return $mock;
		};

		call_user_func_array(array($model, $method), $args);
		$this->assertSame($expected, $mock->method_called());
	}

	/**
	 * @expectedException  BadMethodCallException
	 */
	public function testItShouldThrowBadMethodCallException()
	{
		$model = new Models\User\Magic;
		$model->unknown();
	}

	public function provideAccessorMethods()
	{
		return array(
			array('name',     'name', 'Luke'),
			array('location', 'town', 'Braintree'),
		);
	}

	/**
	 * @dataProvider  provideAccessorMethods
	 */
	public function testItShouldUseAccessorMethodName($name, $alias, $value)
	{
		$model = new Models\User\Magic;
		$accessor = new ObjectAccessor;
		$accessor->set($model, array($name => $value));
		$this->assertSame($value, $model->{$alias}());
	}

	public function testItShouldExpandFieldValueIfCallable()
	{
		$model = new Models\MockableAttribute;
		$model::$attribute = function ()
		{
			return new Field('calculate', array(
				'value' => function ()
				{
					return 1 + 1;
				},
			));
		};
		$this->assertSame(2, $model->calculate());
	}

}

