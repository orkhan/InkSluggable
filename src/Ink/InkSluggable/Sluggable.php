<?php namespace Ink\InkSluggable;

use Illuminate\Database\Eloquent\Model;

class Sluggable {
	
	/**
	 * Separator.
	 *
	 * @var string
	 */
	private static $separator = '-';

    /**
     * Build slug for model
     *
     * @param  Model     $model The model
	 * @param  boolean   $force Force generation of a slug
	 *
     * @return boolean
     */
    public function build( Model $model )
    {
		// if the model isn't sluggable, then do nothing
		if ( !isset( $model::$sluggable ) )
		{
			return true;
		}

		// nicer variables for readability
		$buildFrom = $saveTo = $method = $unique = $onUpdate = null;
		extract( $model::$sluggable, EXTR_IF_EXISTS );
		
		

		// skip slug generation if the model exists or the slug field is already populated,
		// and on_update is false ... unless we are forcing things!
		if ( ($model->exists || !empty($model->{$saveTo}) ) && !$onUpdate )
		{
			return true;
		}

		// build the slug string
		$string = '';
		
		if ( is_string($buildFrom) )
		{
			$string = $model->{$buildFrom};
		}
		else if ( is_array($buildFrom) )
		{
			foreach($buildFrom as $field) $string .= $model->{$field} . ' ';
		}
		else
		{
			$string = $model->__toString();
		}

		$string = trim($string);

		// build slug using given slug style
		if ( is_null($method) )
		{
			$slug = \Str::slug( $string );
		}
		else if ( $method instanceof Closure )
		{
			$slug = $method($string, self::$separator);
		}
		else if ( is_callable($method) )
		{
			$slug = call_user_func($method, $string, self::$separator);
		}
		else
		{
			throw new \UnexpectedValueException("Sluggable method is not a callable, closure or null.");
		}

		// check for uniqueness?
		if ( $unique )
		{
			// find all models where the slug is similar to the generated slug
			$class = get_class($model);

			$collection = $class::where( $saveTo, 'LIKE', $slug.'%' )
				->orderBy( $saveTo, 'DESC' )
				->get();


			// extract the slug fields
			$list = $collection->lists( $saveTo, $model->getKeyName() );

			// if the current model exists in the list -- i.e. the existing slug is either
			// equal to or an incremented version of the new slug -- then the slug doesn't
			// need to change and we can just return
			if ( array_key_exists($model->getKey(), $list) )
			{
				return true;
			}

			// does the exact new slug exist?
			if ( in_array($slug, $list) )
			{
				// find the "highest" numbered version of the slug and increment it.
				$idx = substr($collection->first()->{$saveTo}, strlen($slug));
				$idx = ltrim($idx, self::$separator);
				$idx = intval($idx);
				$idx++;

				$slug .= self::$separator . $idx;
			}
		}

		// update the slug field
		$model->{$saveTo} = $slug;

		// done!
		return true;
    }
}
