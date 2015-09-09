<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class HelperController extends Controller{

  public static $host = 'localhost';
  public static $port = 7474;
  public static $userNeo4j = 'neo4j';
  public static $passNeo4j = 'soulmate';
  public static $labelDonatur = 'Donatur';
  public static $labelMustahiq = 'Mustahiq';
  public static $labelNotifikasi = 'Notifikasi';
  public static $labelRekomendasi = 'RECOMMENDED_BY';
  public static $labelDonasi = 'DONASI';
  public static $uriImage = 'http://soulmateapi.cloudapp.net/api/v1/images/';
  public static $imageSaveDonatur = '/storage/pics/donatur/';
  public static $imageSaveDonasi = '/storage/pics/donasi/';
  public static $imageSaveMustahiq = '/storage/pics/mustahiq/';
  public static $imageSaveSampah = '/storage/pics/sampah/';
  public static $apiKeyGCM = 'AIzaSyAI-sMnf4OHsKy0iDBEskoO1uI8BpROknA';


  public static function getHost(){
    return self::$host;
  }

  public static function getPort(){
    return self::$port;
  }

  public static function getUserNeo4j(){
    return self::$userNeo4j;
  }

  public static function getPassNeo4j(){
    return self::$passNeo4j;
  }

  public static function getLabelDonatur(){
    return self::$labelDonatur;
  }

  public static function getLabelMustahiq(){
    return self::$labelMustahiq;
  }
  
  public static function getLabelNotifikasi(){
    return self::$labelNotifikasi;
  }

  public static function getUriImage(){
    return self::$uriImage;
  }

  public static function getApiKeyGCM(){
    return self::$apiKeyGCM;
  }

  public static function getImageSave($from){
    if($from == 'donatur'){
      return self::$imageSaveDonatur;
    }else if($from == 'donasi'){
      return self::$imageSaveDonasi;
    }else if ($from == 'mustahiq'){
      return self::$imageSaveMustahiq;
    }else{
      return self::$imageSaveSampah;
    }
  }



  public static function saveImage($image,$from){
    $filename  = rand().'-'. time() . '.jpg' ;
    $imageSave = base_path().self::getImageSave($from);
    $imagePath = self::getUriImage().$filename;
    $binary=base64_decode($image);
    header('Content-Type: bitmap; charset=utf-8');
    $file = fopen($imageSave.$filename, 'wb');
    fwrite($file, $binary);
    fclose($file);
  }

  public static function saveImageWithReturn($image,$from){
    $filename  = rand().'-'. time() . '.jpg' ;
    $imageSave = base_path().self::getImageSave($from);
    if(!($from == 'donatur' || $from == 'donasi' || $from == 'mustahiq')){
      $from = 'sampah';
    }
    $imagePath = self::getUriImage().$from.'/'.$filename;
    $binary=base64_decode($image);
    header('Content-Type: bitmap; charset=utf-8');
    $file = fopen($imageSave.$filename, 'wb');
    fwrite($file, $binary);
    fclose($file);

    return $imagePath;
  }

}
 ?>
