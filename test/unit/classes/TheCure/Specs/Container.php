<?php
namespace TheCure\Specs;
/**
 * Test the cure's main dependency container
 *
 * @package     TheCure
 * @category    Container
 * @category    Spec
 * @category    Test
 * @copyright   Gignite, 2012
 * @license     MIT
 * 
 * @group  specs
 * @group  container
 */
use TheCure\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase {

	public function testMapper()
	{
		$container = new Container('Mock');
		$this->assertInstanceOf(
			'TheCure\Mappers\Mock\User',
			$container->mapper('User'));
	}

	public function testMapperConnection()
	{
		$container = new Container('ConnectionTest');
		$this->assertInstanceOf(
			'TheCure\Mappers\ConnectionTest\User',
			$container->mapper('User'));
	}

	public function testItShouldSetAndGetConfig()
	{
		$container = new Container('Mock');
		$expectedConfig = array();
		$container->config($expectedConfig);
		$this->assertSame($expectedConfig, $container->config());
	}

	public function testItShouldUseFactoryIfNoMapperConfigFound()
	{
		$container = new Container('ConnectionTest');
		$container->config(array(
			'factory' => array(
				'prefixes' => array(
					'connection' => 'TheCure\Connections',
					'mapper'     => 'TheCure\Mappers',
				),
				'separator' => '\\',
			),
		));
		$mapper = $container->mapper('User');
		$this->assertInstanceOf('TheCure\Mappers\ConnectionTest\User', $mapper);
	}

	public function testItShouldLoadDefaultConfigIfNoKohanaAndNoConfigSet()
	{
		$config = \Kohana::$config;
		\Kohana::$config = NULL;
		$container = new Container('ConnectionTest');
		$this->assertSame(
			require(APPPATH.'/../../config/the-cure.php'),
			$container->config());
		\Kohana::$config = $config;
	}

}