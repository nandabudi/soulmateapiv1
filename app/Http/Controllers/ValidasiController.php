<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Client;

class ValidasiController extends Controller{

  public function validasiMustahiq($id){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $status = 'failed';
    if(count($id) > 0){
      $nodes = $client->getNode($id);
      $properties = array();
      if(count($nodes) > 0){
        $donaturId = $nodes->getProperty('donaturId');
        $nodeDonatur = $client->getNode($nodes->getProperty('donaturId'));
        $gcmId = $nodeDonatur->getProperty('gcmId');
        $status = 'success';
        $nodes->setProperty('isApproved', 'YES')
        ->save();
        $imagePathMustahiq = $nodes->getProperty('imagePath');
        $namaMustahiq = $nodes->getProperty('nama');
        $datenow = date('Y-m-d H:i:s');
        NotifikasiController::createNotifikasiNode($donaturId,$id,$namaMustahiq,$datenow,$imagePathMustahiq,-1,'validasimustahiq');
        GCMController::gcmPushNotifikasi('validasimustahiq',$gcmId);
      }else{
        $status = 'failed, return value is empty check your mustahiq id';
      }
    }else{
      $status = 'failed, mustahiq id is empty';
    }
    return response()->json(array('status' => $status));
  }
  
  public function unvalidasiMustahiq($id){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $status = 'failed';
    if(count($id) > 0){
      $nodes = $client->getNode($id);
      $properties = array();
      if(count($nodes) > 0){
        $donaturId = $nodes->getProperty('donaturId');
        $nodeDonatur = $client->getNode($nodes->getProperty('donaturId'));
        $gcmId = $nodeDonatur->getProperty('gcmId');
        $status = 'success';
        $nodes->setProperty('isApproved', 'DELETE')
        ->save();
      }else{
        $status = 'failed, return value is empty check your mustahiq id';
      }
    }else{
      $status = 'failed, mustahiq id is empty';
    }
    return response()->json(array('status' => $status));
  }

  public function validasiDonasi($id){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $status = 'failed';
    if(count($id) > 0){
      $nodes = $client->getRelationship($id);
      $properties = array();
      if(count($nodes) > 0){
        $donaturId = $nodes->getProperty('donaturId');
        $nodeDonatur = $client->getNode($donaturId);
        $gcmId = $nodeDonatur->getProperty('gcmId');
        $status = 'success';
        $nodes->setProperty('isValidate', 1)
        ->save();

        $mustahiqId = $nodes->getProperty('mustahiqId');
        $nodeMustahiq = $client->getNode($mustahiqId);
        $imagePathMustahiq = $nodeMustahiq->getProperty('imagePath');
        $namaMustahiq = $nodeMustahiq->getProperty('nama');
        $nominal = $nodeMustahiq->getProperty('nominal');
        $datenow = date('Y-m-d H:i:s');
        NotifikasiController::createNotifikasiNode($donaturId,$mustahiqId,$namaMustahiq,$datenow,$imagePathMustahiq,$nominal,'validasidonasi');
        GCMController::gcmPushNotifikasi('validasidonasi',$gcmId);
      }else{
        $status = 'failed, return value is empty check your donasi id';
      }
    }else{
      $status = 'failed, donasi id is empty';
    }
    return response()->json(array('status' => $status));
  }
  
  public function unvalidasiDonasi($id){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $status = 'failed';
    if(count($id) > 0){
      $nodes = $client->getRelationship($id);
      $properties = array();
      if(count($nodes) > 0){
        $donaturId = $nodes->getProperty('donaturId');
        $nodeDonatur = $client->getNode($donaturId);
        $gcmId = $nodeDonatur->getProperty('gcmId');
        $status = 'success';
        $nodes->setProperty('isValidate', 2)
        ->save();
      }else{
        $status = 'failed, return value is empty check your donasi id';
      }
    }else{
      $status = 'failed, donasi id is empty';
    }
    return response()->json(array('status' => $status));
  }

}


?>
