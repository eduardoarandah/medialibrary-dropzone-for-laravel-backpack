# Easily attach images to your model and order them by dragging

Demo:

![dropzone-pictures-2](https://user-images.githubusercontent.com/4065733/89108058-3a80e800-d3fb-11ea-80e5-9a9fd3803aeb.gif)

## System requirements

Media library will use these tools to optimize converted images if they are present on your system:


```
sudo apt install jpegoptim optipng pngquant gifsicle
npm install -g svgo

```

## Install composer requirements

Install laravel media library, here's the basic commands:

You can find full documentation here:
https://docs.spatie.be/laravel-medialibrary/v8/installation-setup/

```
composer require "spatie/laravel-medialibrary:^8.0.0"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"
php artisan migrate
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="config"

```

## Prepare your model

To associate media with a model, the model must implement the following interface and trait:


```
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class YourModel extends Model implements HasMedia
{
    use InteractsWithMedia;
}
```

# Add conversions

Add conversions in your model, let's say, thumbnail and large


```
use Spatie\MediaLibrary\MediaCollections\Models\Media;

public function registerMediaConversions(Media $media = null) : void
{
  $this->addMediaConversion('thumbnail')
    ->width(600);

  $this->addMediaConversion('large')
    ->width(1120);
}
```


# Create your controller

You need a controller in your backend to process uploaded media

For example: app/Http/Controllers/MyModelPictureController.php

```
<?php

namespace App\Http\Controllers;

use App\MyModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MyModelPictureController extends Controller
{
  public function index(MyModel $mymodel)
  {
    $media = $mymodel->getMedia();
    $pictures = [];
    foreach ($media as $item) {
      $pictures[] = [
        'id' => $item->id,
        'url' => $item->getUrl(),
        'order' => $item->order_column ?? 0,
      ];
    }

    return $pictures;
  }

  public function store(Request $request, MyModel $mymodel)
  {
    $media = $mymodel->addMedia($request->file('file'))->toMediaCollection();
    return $media->getUrl();
  }

  public function destroy(MyModel $mymodel, Media $picture)
  {
    $picture->delete();
  }

  public function sort(Request $request)
  {
    $pictures = $request->input('pictures');
    foreach ($pictures as $picture) {
      $media = Media::findOrFail($picture['id']);
      $media->order_column = $picture['order'] ?? 0;
      $media->save();
    }
  }
}
```

# Add routes

Add the routes, replace yourmodel for your real model

```
// Uploading pictures to Model via dropzone
route::post('mymodel/{mymodel}/picture', 'MyModelPictureController@store')->name('mymodel.picture.store');
route::get('mymodel/{mymodel}/picture', 'MyModelPictureController@index')->name('mymodel.picture.index');
route::delete('mymodel/{mymodel}/picture/{picture}', 'MyModelPictureController@destroy')->name('mymodel.picture.destroy');
route::post('mymodel/{mymodel}/picture-sort', 'MyModelPictureController@sort')->name('mymodel.picture.sort'); 

```

## In your backpack crud controller add a method to crud update

Here's the field definition

``` 
protected function setupUpdateOperation()
{
	$this->setupCreateOperation();
	CRUD::addField([
		'name' => 'pictures',
		'type' => 'medialibrary-dropzone',
                'view_namespace' => 'medialibrary-dropzone::fields',
		'url' => "/mymodel/{$this->crud->getCurrentEntry()->id}/picture"
	]);
}
```


## You can't upload in new fields

Since images are attached to existing models, you can't upload to unsaved models. 

Consider adding a message in your create operation:


```
protected function setupCreateOperation()
{
	CRUD::setValidation(MyModelRequest::class);

	CRUD::field('pictures_pre')
		->type('custom_html')
		->value(
			'<h4 class="card text-center py-5">Save item first to add images</h4>'
		);
}

```


## Tips for your frontend

Get first image in your frontend like this:

<img class="w-full h-auto" src="{{ $mymodel->getFirstMediaUrl("default","thumbnail") }}" alt="Shoe" />

More information here:

https://docs.spatie.be/laravel-medialibrary/v8/basic-usage/retrieving-media/
