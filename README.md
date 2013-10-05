# InkSluggable (Eloquent-Sluggable)

Easy creation of slugs for Eloquent and EloquentTranslatable models in Laravel 4.


## Background: What is a slug?

A slug is a simplified version of a string, typically URL-friendly.  The act of "slugging" a string usually
involves converting it to one case, and removing any non-URL-friendly characters (spaces, accented letters, ampersands,
etc.).  The resulting string can then be used as an indentifier for a particular resource.

For example, I have a blog with posts.  I could refer to each post via the ID:

	http://example.com/post/1
	http://example.com/post/2

... but that's not particularly friendly (especially for [SEO](http://en.wikipedia.org/wiki/Search_engine_optimization)).
You probably would prefer to use the post's title in the URL, but that becomes a problem if your post is
titled "My Dinner With André & François", because this is pretty ugly too:

	http://example.com/post/My+Dinner+With+Andr%C3%A9+%26+Fran%C3%A7ois

The solution is to create a slug for the title and use that instead.  You might want to use Laravel's
built-in `Str::slug()` method to convert that title into something friendlier:

	http://example.com/post/my-dinner-with-andre-francois

A URL like that will make users happier (readable, easier to type, etc.).

For more information, you might want to read [this](http://en.wikipedia.org/wiki/Slug_(web_publishing)#Slug)
description on Wikipedia.

Slugs tend to be unique as well.  So if I wrote another post with the same title, I'd want to distinguish
between them somehow, typically with an incremental counter added to the end of the slug:

	http://example.com/post/my-dinner-with-andre-francois
	http://example.com/post/my-dinner-with-andre-francois-1
	http://example.com/post/my-dinner-with-andre-francois-2

This keeps URLs unique.

The **Eloquent-Sluggable** package for Laravel 4 will handle all of this for you automatically, with
minimal configuration at the start.


## Installation

First, you'll need to add the package to the `require` attribute of your `composer.json` file:

```json
{
    "require": {
        "ink/ink-sluggable": "dev-master"
    },
}
```

Aftwards, run `composer update` from your command line.

Then, add `'Ink\InkSluggable\InkSluggableServiceProvider',` to the list of service providers in `app/config/app.php`
and add `'Sluggable' => 'Ink\InkSluggable\Facades\Sluggable'` to the list of class aliases in `app/config/app.php`.


## Updating your Eloquent Models

Define a public property `$sluggable` with the definitions (see [#Configuration] below for details):

```php
class Post extends Eloquent
{

	public static $sluggable = array(
		'buildFrom' => 'title',
		'saveTo'    => 'slug',
	    'method'    => null,
	    'unique'    => false,
	    'onUpdate'  => false,
	);

}
```

That's it ... your model is now "sluggable"!


## Using the Class

Saving a model is easy:

```php
$post = new Post(array(
	'title'    => 'My Awesome Blog Post'
));

$post->save();
```

And so is retrieving the slug:

```php
echo $post->slug;
```



## Configuration

Configuration was designed to be as flexible as possible.

Here is an example configuration, with all the default settings shown:

```php
	public static $sluggable = array(
		'buildFrom' => 'title',
		'saveTo'    => 'slug',
	    'method'    => null,
	    'unique'    => false,
	    'onUpdate'  => false,
	);
```

`buildFrom` is the field or array of fields from which to build the slug. Each `$model->field` is contactenated (with space separation) to build the sluggable string.  This can be model attribues (i.e. fields in the database) or custom getters.  So, for example, this works:

```php
class Person extends Eloquent {

	public static $sluggable = array(
		'buildFrom' => 'fullname'
	);

	public function getFullnameAttribute() {
		return $this->firstname . ' ' . $this->lastname;
	}

}
```

If `buildFrom` is empty, false or null, then the value of `$model->__toString()` is used.

`saveTo` is the attribute field in your model where the slug is stored.  By default, this is "slug".  You need to create this column in your table when defining your schema:

```php
Schema::create('posts', function($table)
{
	$table->increments('id');
	$table->string('title');
	$table->string('body');
	$table->string('slug');
	$table->timestamps();
});
```

`method` defines the method used to turn the sluggable string into a slug.  There are three possible options for this configuration:

1. When `method` is null (the default setting), the package uses Laravel's `Str::slug()` method to create the slug.

2. When `method` is a callable, then that function or class method is used.  The function/method should expect two parameters: the string to process, and a separator string.  For example, to duplicate the default behaviour, you could do:

```php
	'method' => array('Illuminate\Support\Str','slug'),
```

3. You can also define `method` as a closure (again, expecting two parameters):

```php
	'method' => function( $string ) {
		return strtolower( preg_replace('/[^a-z]+/i', '-', $string) );
	},
```

Any other values for `method` will throw an exception.

`unique` is a boolean defining whether slugs should be unique among all models of the given type.  For example, if you have two blog posts and both are called "My Blog Post", then they will both sluggify to "my-blog-post" (when using Sluggable's default settings).  This could be a problem, e.g. if you use the slug in URLs.

By turning `unique` on, then the second Post model will sluggify to "my-blog-post-1".  If there is a third post with the same title, it will sluggify to "my-blog-post-2" and so on.  Each subsequent model will get an incremental value appended to the end of the slug, ensuring uniqueness.

`onUpdate` is a boolean.  If it is `false` (the default value), then slugs will not be updated if a model is resaved (e.g. if you change the title of your blog post, the slug will remain the same) or the slug value has already been set.  You can set it to `true` (or manually change the $model->slug value in your own code) if you want to override this behaviour.


## Bugs and Suggestions

Please use Github for bugs, comments, suggestions.  Pull requests are preferred!


## Copyright and License

Eloquent-Sluggable was written by Colin Viebrock (Modified by Orkhan Maharramli) and released under the MIT License. See the LICENSE file for details.

Copyright 2013 Colin Viebrock
