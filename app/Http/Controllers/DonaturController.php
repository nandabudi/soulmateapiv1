<?php

namespace App\Http\Controllers;

use App\Donatur;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Client;
use PushNotification;

class DonaturController extends Controller{

  public function coba(Request $request){
        $gcmId = 'eWVj7d74eck:APA91bElnmqDBXsKqG-OsSIbPBSxi0sW4-DSnwMSib-zSceFx8Xt9KOas-1Yv98ZOflhC2ojUQ-NNrEVxE0QK-aW2CzIBtxUORcFheIKt6SLDnKEmxueI9P3bkAElcbi1gFlC0dPeCni';
        GCMController::gcmPushNotifikasi('validasidonasi',$gcmId);
  }


  public function getAllDonatur(){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $label = $client->makeLabel(HelperController::getLabelDonatur());
    $nodes = $label->getNodes();
    $status = 'success';
    $result = array();
    $properties = array();
    foreach($nodes as $node){
      $properties['id'] = $node->getId();
      $properties['properties'] = $node->getProperties();
      array_push($result,$properties);
    }
    return response()->json(array('status' => $status,'data' => $result));
  }

  public function getDonatur($id){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $status = 'failed';
    $properties = array();
    if(count($id) > 0){
      $nodes = $client->getNode($id);
      if(count($nodes) > 0){
        if(count($nodes->getProperties()) > 0){
          $labels = $nodes->getLabels();
          $label = $labels[0]->getName();
          if($label == HelperController::getLabelDonatur()){
            $status = 'success';
            $properties['id'] = $nodes->getId();
            $properties['properties'] = $nodes->getProperties();
          }else{
            $status = 'failed, the label is not donatur check your parameter';
          }
        }else{
          $status = 'failed, the label is not donatur check your parameter';
        }
      }else{
        $status = 'failed, return value is empty check your donatur id';
      }

    }else{
      $status = 'failed, donatur id is empty please check your parameter';
    }
    return response()->json(array('status' => $status,'data' => $properties));
  }

  public function createDonatur(Request $request){
      $client = new Client(HelperController::getHost(), HelperController::getPort());
      $client->getTransport()
        ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
      $username =  $request->input('username');
      $password =  sha1($request->input('password'));
      $email = $request->input('email');
      $nama = $request->input('nama');
      $notelp = $request->input('notelp');
      $gcmId = $request->input('gcmId');
      $status = 'failed';
      $isLogin = 0;

      if(count($username) > 0 && count($password) > 0 ){
        // check if data already exist
        $cypherCek = 'MATCH (n:'.HelperController::getLabelDonatur().') where n.username="'.$username.'" and n.password = "'.$password.'" RETURN n';
        $queryCek = new Query($client, $cypherCek);
        $resultCek = $queryCek->getResultSet();
        if(count($resultCek) > 0){
          $status = 'failed, data already created';
        }else{
          //image upload handler
          $image = $request->input('imagePath');
          $imagePath = HelperController::saveImageWithReturn($image,'donatur');
          $cypher = 'CREATE (n:'.HelperController::getLabelDonatur().' {username:"'.$username.'",password:"'.$password.'",email:"'.$email.'",nama:"'.$nama.'",notelp:"'.$notelp.'",imagePath:"'.$imagePath.'",gcmId:"'.$gcmId.'",isLogin:'.$isLogin.'}) return n';
          $query = new Query($client, $cypher);
          $query->getResultSet();
          $status = 'success';
        }
      }else{
        $status = 'failed, some parameter is emprty please check your parameter';
      }
      return response()->json(array('status' => $status));
  }

  public function deleteDonatur($id){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $status = 'failed';
    if(count($id) > 0){
      $node = $client->getNode($id);
      if(count($node) > 0){
        if(count($node->getProperties()) > 0){
          $labels = $node->getLabels();
          $label = $labels[0]->getName();
          if($label == HelperController::getLabelDonatur()){
            $node->delete();
            $status = 'success';
          }else{
            $status = 'failed, the label is not donatur check your parameter';
          }
        }else{
          $status = 'failed, the label is not donatur check your parameter';
        }
      }else{
        $status = 'failed, return value is empty check your donatur id';
      }
    }else{
      $status = 'failed, donatur id is empty please check your parameter';
    }
    return response()->json(array('status' => $status));
  }

  public function deleteAllDonatur(){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $cypher = 'MATCH (n:Donatur) OPTIONAL MATCH (n)-[r]-() DELETE n,r';
    $query = new Query($client, $cypher);
    $query->getResultSet();
    $status = 'success';
    return response()->json(array('status' => $status));
  }

  public function updateDonatur(Request $request,$id){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $username = $request->input('username');
    $email = $request->input('email');
    $nama = $request->input('nama');
    $notelp = $request->input('notelp');
    $imagePath = $request->input('imagePath');
    $gcmId = $request->input('gcmId');
    $status = 'failed';
    if(count($username) > 0 && count($id) > 0){
      $cypherCek = 'MATCH (n:'.HelperController::getLabelDonatur().') where n.username="'.$username.'" RETURN n';
      $queryCek = new Query($client, $cypherCek);
      $resultCek = $queryCek->getResultSet();
      if(count($resultCek) > 0){
        $status = 'failed, data already exist';
      }else{
        $node = $client->getNode($id);
        $node->setProperty('username', $username)
        ->setProperty('email', $email)
        ->setProperty('nama', $nama)
        ->setProperty('notelp', $notelp)
        ->setProperty('imagePath', $imagePath)
        ->setProperty('gcmId', $gcmId)
        ->save();
        $status = 'success';
      }
    }else{
      $status = 'failed, username or id is empty please check your parameter';
    }
    return response()->json(array('status' => $status));
  }
}
?>
