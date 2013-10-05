<?php namespace Ink\InkSluggable;

use Illuminate\Support\ServiceProvider;

class InkSluggableServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('ink/ink-sluggable');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->registerSluggable();
        $this->registerEvents();
	}

    /**
     * Register the Sluggable class
     *
     * @return void
     */
    public function registerSluggable()
    {
        $this->app['sluggable'] = $this->app->share(function($app)
        {
            return new Sluggable();
        });
    }

    /**
     * Register the listener events
     *
     * @return void
     */
    public function registerEvents()
    {
        $app = $this->app;

        $app['events']->listen('eloquent.saved*', function($model) use ($app)
        {
            $app['sluggable']->build($model);
        });
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('sluggable');
	}

}