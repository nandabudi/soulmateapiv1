<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Client;

class NotifikasiController extends Controller{

  public static function createNotifikasiNode($donaturId,$mustahiqId,$nama,$tanggal,$imagePath,$nominal){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $cypherQuery = 'CREATE (n:'.HelperController::getLabelNotifikasi().' {donaturId:"'.$donaturId.'",mustahiqId:"'.$mustahiqId.'",nama:"'.$nama.'",tanggal:"'.$tanggal.'",imagePath:"'.$imagePath.'",nominal:'.$nominal.'}) return n';
    $cypher = $cypherQuery;
    $query = new Query($client, $cypher);
    $query->getResultSet();
  }
  public function getAllNotifikasi(){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $label = $client->makeLabel(HelperController::getLabelNotifikasi());
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

  public function getNotifikasi($id){
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
          if($label == HelperController::getLabelNotifikasi()){
            $status = 'success';
            $properties['id'] = $nodes->getId();
            $properties['properties'] = $nodes->getProperties();
          }else{
            $status = 'failed, the label is not notifikasi check your parameter';
          }
        }else{
          $status = 'failed, the label is not notifikasi check your parameter';
        }
      }else{
        $status = 'failed, return value is empty check your notifikasi id';
      }
    }else{
      $status = 'failed, notifikasi id is empty please check your parameter';
    }
    return response()->json(array('status' => $status,'data' => $properties));
  }

}

?>
