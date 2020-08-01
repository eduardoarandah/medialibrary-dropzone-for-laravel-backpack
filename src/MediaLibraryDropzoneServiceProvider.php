<?php

namespace EduardoArandaH\MediaLibraryDropzone;

use Illuminate\Support\ServiceProvider;

class MediaLibraryDropzoneServiceProvider extends ServiceProvider
{
  /**
   * Perform post-registration booting of services.
   *
   * @return void
   */
  public function boot()
  {
    $this->loadViewsFrom(realpath(__DIR__ . '/resources/views'), 'medialibrary-dropzone');
  }
}
