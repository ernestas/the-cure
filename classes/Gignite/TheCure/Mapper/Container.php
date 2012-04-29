<?php
/**
 * A dependency injection container
 *
 * If you are following the conventions of The Cure you will
 * likely only ever initialise this object in your
 * application. Use this object to get your Mapper instances.
 * 
 *     $container = new MapperContainer('Mongo');
 *     $container->mapper('User'); // => Mapper_Mongo_User
 *
 * @package     TheCure
 * @category    Container
 * @copyright   Gignite, 2012
 */
namespace Gignite\TheCure\Mapper;

use Gignite\TheCure\IdentityMap;
use Gignite\TheCure\Factory;

class Container {

	protected $config;

	protected $factory;

	protected $connection;

	protected $identities;

	protected $mappers;
	
	protected $type;
	
	/**
	 * Create a new instance of MapperContainer by providing
	 * a base class.
	 *
	 *     // Mappers will be prefixed with Mapper_Mongo_
	 *     new MapperContainer('Mongo') 
	 *
	 *     // Mappers will be prefixed with Mapper_Diff_One_
	 *     new MapperContainer('Diff_One') 
	 * 
	 * @param   string  a base type of mapper
	 * @return  void
	 */
	public function __construct($type)
	{
		$this->type = $type;
	}

	protected function type()
	{
		return $this->type;
	}

	protected function factory()
	{
		if ($this->factory === NULL)
		{
			$this->factory = new Factory($this->config('factory'));
		}

		return $this->factory;
	}

	public function config($config = NULL)
	{
		if (is_array($config))
		{
			$this->config = $config;
			return;
		}

		if ($this->config === NULL
			AND class_exists('Kohana')
			AND isset(\Kohana::$config))
		{
			$this->config = \Kohana::$config->load('thecure');
		}

		if (isset($this->config[$config]))
		{
			return $this->config[$config];
		}

		return $this->config;
	}

	protected function mapper_config()
	{
		$mappers = $this->config('mappers');
		$key = strtolower($this->type());

		if (isset($mappers[$key]))
		{
			return $mappers[$key];
		}
	}

	protected function connection()
	{
		if ($this->connection === NULL)
		{
			$class = $this->factory()->connection($this->type());
			$this->connection = new $class($this->mapper_config());
		}

		return $this->connection;
	}

	protected function identities()
	{
		if ($this->identities === NULL)
		{
			$this->identities = new IdentityMap;
		}

		return $this->identities;
	}

	/**
	 * Get a mapper instance. Once a mapper of a certain type
	 * has been instantiated that object will continue to be
	 * returned.
	 *
	 *     $container = new MapperContainer('Mongo');
	 *     $container->mapper('User'); // => Mapper_Mongo_User
	 *
	 * @param   string  the class
	 * @return  Mapper
	 */
	public function mapper($suffix)
	{
		$class = $this->factory()->mapper($this->type(), $suffix);

		if ( ! isset($this->mappers[$class]))
		{
			$mapper = new $class;

			if ($mapper instanceOf ConnectionSetGet)
			{
				$mapper->connection($this->connection());
			}
			
			if ($mapper instanceOf IdentitiesSetGet)
			{
				$mapper->identities($this->identities());
			}
			
			if ($mapper instanceOf ConfigSetGet)
			{
				$mapper->config($this->mapper_config());
			}

			if ($mapper instanceOf FactorySetGet)
			{
				$mapper->factory($this->factory());
			}

			$this->mappers[$class] = $mapper;
		}

		return $this->mappers[$class];
	}

}