# The Cure

> Each time I play a song it seems more real.
>
> *Robert Smith*

## General flow of using The Cure

 - Create a `Gignite\TheCure\Mapper\Container` that is the DI
   container for all mappers and the objects they create
 - Get a mapper object from `Container` using `::use()`

``` php
<?php
use Gignite\TheCure\Mapper\Container;
$container = new Container('Mongo');
$container->mapper('Profile'); // => Mappers\Mongo\Profile
$container->mapper('Media');   // => Mappers\Mongo\Media
?>
```

### Creating

 - Create a new model
 - Validate the model
 - Pass the model to `Gignite\TheCure\Mappers\Mapper::save()`

``` php
<?php
$image = new Model_Media_Image;

if ($image->create($owner, compact('filename')))
{
	$container->mapper('Media')->save($image);
}
?>
```

### Updating

 - Use `Gignite\TheCure\Mappers\Mapper::find_one()`
 - Operate on the model
 - Validate the model
 - Pass the model to `Gignite\TheCure\Mappers\Mapper::save()`

``` php
<?php
$image = $container->mapper('Media')->find_one('Image', $id); // => Models\Media\Image
$image->caption('A new caption.');

if ($image->validation()->check())
{
	$container->mapper('Media')->save($image);
}
?>
```

### Finding

 - Use `Gignite\TheCure\Mappers\Mapper::find_one()` to find a
   single `Model`
 - Use `Gignite\TheCure\Mappers\Mapper::find()` to find a
   `Collection` of models

The update example includes a `::find_one()` call which has
two arguments. Optionally you can simply pass in an ID like
so:

``` php
<?php
$container->mapper('Profile')->find_one($id); // => Models\Profile
$container->mapper('Profile')->find_one('Artist', $id); // => Models\Profile\Artist
?>
```

`::find()` has a similar API to `::find_one()` the differences
being all of `::find()`s arguments are optional, and the
`$where` argument must be an array. If you supply no arguments
you will get a `Collection` representing an entire collection,
table, etc.

``` php
<?php
$container->mapper('Profile')->find();
// => Gignite\TheCure\Collections\Model of Models\Profile

$container->mapper('Profile')->find('Artist');
// => Gignite\TheCure\Collections\Model of Models\Profile\Artist

$container->mapper('Profile')->find(array(
	'plan' => 'free',
));
// => Gignite\TheCure\Collections\Model of Models\Profile

$container->mapper('Profile')->find('Artist', array(
	'plan' => 'free',
));
// => Gignite\TheCure\Collections\Model of Models\Profile\Artist
?>
```

### Working with collections

A `Gignite\TheCure\Collections\Collection` is a container,
cursor and iterable that represents more than one document,
row, etc. It does not initialise any models however until that
row is iterated over, at that point an `IdentityMap` is used
to ensure the model isn't already in the ecosystem.

``` php
<?php
$collection = $mapper->mapper('Profile')->find();

foreach ($collection as $_model)
{
	echo "{$_model->name()}\n";
}
?>
```

### Deleting

 - Use `Gignite\TheCure\Mappers\Mapper::delete()` to delete a
   model, collection or by a new query.

``` php
<?php
$profile_mapper = $container->mapper('Profile');

// Using a Model
$profile_mapper->delete($profile_mapper->find_one($id));

// Using a Collection
$profile_mapper->delete($profile_mapper->find());

// Using an ID
$profile_mapper->delete($id);

// Using a query (unlimited)
$profile_mapper->delete(array('type' => 'artist'));
?>
```

## The relationship between a Model and it's Mappers

This is by convention only and it is completely possible to
not follow it.

A Model describes the business (or domain) logic for a
particular area of expertise. For example a Model might
describe a user of an application. This would likely be called
`Models\User`. The model would have methods for registering an
account, updating it's email address, etc.

In order to store `Models\User` in a Mongo database we need a
mapper. A mapper knows how to store a model's data into what
ever medium it wants. A mongo mapper for a user model would,
by convention, be called `Mappers\Mongo\User`.

If you wanted to test `Models\User` you would also need another
mapper. This mapper would be a mock and would be used to test
the data before and after an operation on your model. By
convention this would then be called `Mappers\Mock\User`.

This convention explains the API choice of
`Container::__construct()`, `Container::mapper()`,
`Mapper::find()` and `Mapper::find_one()`.

### Gignite\TheCure\Mapper\Container::__construct($type)

The argument passed here is added onto the `Mappers\` prefix,
so for example `new Container('Mongo')` creates a prefix
for all mappers called `Mappers\Mongo\`.

### Gignite\TheCure\Mapper\Container::mapper($mapper)

The argument passed to ::mapper() indicates the domain area
being mapped so for example `$container->mapper('Profile')`
will instantiate a single `Mappers\Mongo\Profile` and register
it with the container so that subsequent calls return the same
instance.

### Gignite\TheCure\Mappers\Mapper::find($suffix, $id) and ::find_one($suffix, $id)

The first argument passed to `::find_one()` is a suffix for
the model class name which is by default a derivative of the
mapper class name. Take this example:

``` php
<?php
use Gignite\TheCure\Mapper\Container;
$container = new Container('Mongo');

// Using Mappers\Mongo\Profile to find Models\Profile\Artist
$model = $container->mapper('Profile')->find_one('Artist', $id);

// Using Mappers\Mongo\Profile to find Models\Profile
$model = $container->mapper('Profile')->find_one($id);
?>
```

