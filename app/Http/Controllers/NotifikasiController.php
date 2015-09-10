<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Client;

class NotifikasiController extends Controller{

  public static function createNotifikasiNode($donaturId,$mustahiqId,$nama,$tanggal,$imagePath,$nominal,$kategori){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $cypherQuery = 'CREATE (n:'.HelperController::getLabelNotifikasi().' {donaturId:"'.$donaturId.'",mustahiqId:"'.$mustahiqId.'",nama:"'.$nama.'",tanggal:"'.$tanggal.'",imagePath:"'.$imagePath.'",nominal:'.$nominal.',kategori:"'.$kategori.'"}) return n';
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
    $result = array();
    if(count($id) > 0){
      $cypher = 'MATCH (n:Notifikasi) where n.donaturId="'.$id.'" RETURN n LIMIT 100';
      $query = new Query($client, $cypher);
      $nodes = $query->getResultSet();
      if(count($nodes) > 0){
        $status = 'success';
        foreach ($nodes as $node) {
          $properties['id'] = $node['r']->getId();
          $properties['properties'] = $node['r']->getProperties();
          array_push($result,$properties);
        }
      }else{
        $status = 'failed, return value is empty check your donatur id';
      }
    }else{
      $status = 'failed, notifikasi id is empty please check your parameter';
    }
    return response()->json(array('status' => $status,'data' => $result));
  }

}

?>
