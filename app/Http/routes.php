<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->welcome();
});

$app->get('api/v1/images/{filename}', function ($filename)
{
  $path = storage_path() . '/pics/' . $filename;
  return response()->download($path);
});

$app->group(['prefix' => 'api/v1','namespace' => 'App\Http\Controllers'], function($app)
{
  // donatur routes
  $app->post('login','DonaturController@loginDonatur');
  $app->get('donatur','DonaturController@index');
  $app->get('donatur/{id}','DonaturController@getDonatur');
  $app->post('donatur','DonaturController@createDonatur');
  $app->put('donatur/{id}','DonaturController@updateDonatur');
  $app->delete('donatur/{id}','DonaturController@deleteDonatur');
  $app->delete('donaturdeleteall','DonaturController@deleteAllDonatur');

  // mustahiq routes
  $app->get('mustahiq','MustahiqController@index');
  $app->get('mustahiq/{id}','MustahiqController@getMustahiq');
  $app->get('mustahiq/kategori/{id}','MustahiqController@getMustahiqByKategori');
  $app->post('mustahiq','MustahiqController@createMustahiq');
  $app->put('mustahiq/{id}','MustahiqController@updateMustahiq');
  $app->delete('mustahiq/{id}','MustahiqController@deleteMustahiq');
  $app->delete('mustahiqdeleteall','MustahiqController@deleteAllMustahiq');
});
