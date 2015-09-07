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

$app->get('api/v1/images/{kind}/{filename}', function ($kind,$filename)
{
  $path = storage_path() . '/pics/'.$kind.'/'. $filename;
  return response()->download($path);
});

$app->group(['prefix' => 'api/v1','namespace' => 'App\Http\Controllers'], function($app)
{
  // auth routes
  $app->post('login','AuthController@loginDonatur');
  $app->post('logout','AuthController@logoutDonatur');

  // donatur routes
  $app->get('donatur','DonaturController@getAllDonatur');
  $app->get('donatur/{id}','DonaturController@getDonatur');
  $app->post('coba','DonaturController@coba');
  $app->post('donatur','DonaturController@createDonatur');
  $app->put('donatur/{id}','DonaturController@updateDonatur');
  $app->delete('donatur/{id}','DonaturController@deleteDonatur');
  $app->delete('donaturdeleteall','DonaturController@deleteAllDonatur');

  // mustahiq routes
  $app->get('mustahiq','MustahiqController@getAllMustahiq');
  $app->get('mustahiq/{id}','MustahiqController@getMustahiq');
  $app->get('mustahiq/kategori/{id}','MustahiqController@getMustahiqByKategori');
  $app->post('mustahiq','MustahiqController@createMustahiq');
  $app->put('mustahiq/{id}','MustahiqController@updateMustahiq');
  $app->delete('mustahiq/{id}','MustahiqController@deleteMustahiq');
  $app->delete('mustahiqdeleteall','MustahiqController@deleteAllMustahiq');

  // donasi routes
  $app->post('donasi','DonasiController@createDonasi');
  $app->get('donasi','DonasiController@getAllDonasi');
  $app->get('donasi/{id}','DonasiController@getDonasi');

  // validasi routes
  $app->post('validasi/mustahiq/{id}','ValidasiController@validasiMustahiq');
  $app->post('validasi/donasi/{id}','ValidasiController@validasiDonasi');

});
