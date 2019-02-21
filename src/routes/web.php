<?php

Route::group(['namespace' => 'Davidcb\Uploads\Http\Controllers'], function() {
	Route::get('sortImages', 'ImageController@sort')->name('images.sort');
	Route::get('sortFiles', 'FileController@sort')->name('files.sort');

	Route::get('placeholder/{type}', 'ImageController@placeholder')->name('placeholder');
	Route::get('image/{folder}/{url}/{filter?}', 'ImageController@view')->name('image')->where('folder', '.*');
	Route::get('svg/{folder}/{url}', 'ImageController@viewSvg')->name('svg')->where('folder', '.*');
	Route::post('crop/{folder}/{url}/{type}', 'ImageController@crop')->name('crop');
	Route::post('deleteImageStorage/{folder}/{url}', 'ImageController@deleteStorage')->name('deleteImageStorage');
	Route::get('deleteImageModel/{image}', 'ImageController@deleteModel')->name('deleteImageModel');

	Route::post('upload', 'FileController@upload')->name('upload');
	Route::get('deleteFileModel/{file}', 'FileController@deleteModel')->name('deleteFileModel');
	Route::post('deleteFileStorage/{folder}/{url}', 'FileController@deleteStorage')->name('deleteFileStorage');
	Route::get('download/{folder}/{url}/{title?}', 'FileController@download')->name('download');
});