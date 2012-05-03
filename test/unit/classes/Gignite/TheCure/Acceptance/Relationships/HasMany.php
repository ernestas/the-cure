<?php
namespace Gignite\TheCure\Acceptance\Relationships;

/**
 * @group  acceptance
 * @group  relationships
 * @group  relationships.onetomany
 */

use Gignite\TheCure\Mapper\Container;

class HasMany extends \PHPUnit_Framework_TestCase {

	public function provideContainers()
	{
		return array(
			array(new Container('Mock')),
			array(new Container('Mongo')),
		);
	}

	/**
	 * @dataProvider  provideContainers
	 */
	public function testItShouldWork($container)
	{
		$thread = $container->mapper('Forum\Thread')->model();
		$thread->title('Welcome thread');
		$thread->message('<p>Welcome to the forum!</p>');

		$post = $container->mapper('Forum\Post')->model();
		$post->message('<p>What a great welcome this is :D</p>');
		
		$thread->add_posts($post);

		$container->mapper('Forum\Thread')->save($thread);

		// Test OneToMany
		$this->assertSame($post, $thread->posts()->current());

		// // Test BelongsToOne
		$this->assertSame($thread, $post->thread());
	}

}

