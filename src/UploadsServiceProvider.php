<?php

namespace Davidcb\Uploads;

use Illuminate\Support\ServiceProvider;

class UploadsServiceProvider extends ServiceProvider {

	public function boot()
	{
		$this->publishes([
			__DIR__ . '/config/crop.php' => config_path('crop.php'),
			__DIR__ . '/config/upload.php' => config_path('upload.php'),
		]);
		$this->loadMigrationsFrom(__DIR__ . '/database/migrations');
		$this->loadViewsFrom(__DIR__ . '/resources/views', 'uploads');
		$this->loadRoutesFrom(__DIR__ . '/routes/web.php');
	}

	public function register()
	{
		//
	}

}