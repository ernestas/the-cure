<?php

class ModelMagicTest extends PHPUnit_Framework_TestCase {

	protected $container;

	protected function container()
	{
		if ($this->container === NULL)
		{
			$container = new MapperContainer('Array');

			$jim = new Model_User_Magic;
			$jim->__container($container);
			$jim->__object((object) array(
				'name' => 'Jim',
			));
			$container->mapper('User')->save($jim);

			$luke = new Model_User_Magic;
			$luke->__container($container);
			$luke->__object((object) array(
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
		return $container->mapper('User')->find_one('Magic', array(
			'name' => $expectedName,
		));
	}

	public function providerTestMagicCall()
	{
		$expectedName = 'Luke';
		$luke = $this->user($expectedName);

		return array(
			array($luke, $expectedName),
		);
	}

	/**
	 * @dataProvider  providerTestMagicCall
	 */
	public function testMagicCall($model, $expectedName)
	{
		$this->assertSame($expectedName, $model->name());
	}

	public function providerModelWithMockableRelation()
	{
		$args = array(new Model_User);

		return array(
			array(
				new Model_User_MockableRelation,
				'relation',
				'find',
				array(),
			),
			array(
				new Model_User_MockableRelation,
				'add_relation',
				'add',
				$args,
			),
			array(
				new Model_User_MockableRelation,
				'remove_relation',
				'remove',
				$args,
			),
		);
	}

	/**
	 * @dataProvider  providerModelWithMockableRelation
	 */
	public function testMagicCallRelationship($model, $method, $expected, $args)
	{
		$mock = new Relationship_Mock('relation');

		Model_User_MockableRelation::$relation = function () use ($mock)
		{
			return $mock;
		};

		call_user_func_array(array($model, $method), $args);
		$fields = $model::fields();
		$this->assertSame($expected, $mock->method_called());
	}

}
