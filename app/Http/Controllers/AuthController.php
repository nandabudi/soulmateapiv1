<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Client;

class AuthController extends Controller{

  public function loginDonatur(Request $request){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $username =  $request->input('username');
    $password =  sha1($request->input('password'));
    $gcmId = $request->input('gcmId');
    $cypher = 'MATCH (n:'.HelperController::getLabelDonatur().') where n.username="'.$username.'" and n.password = "'.$password.'" RETURN n';
    $query = new Query($client, $cypher);
    $nodes = $query->getResultSet();
    $status = 'failed, check your internet connection';
    $properties = array();
    $isLogin = 0;
    if(count($gcmId) > 0){
      if(count($nodes) > 0){
        foreach($nodes as $node){
          $properties['id'] = $node['n']->getId();
          $properties['username'] = $node['n']->getProperty('username');
          $properties['password'] = $node['n']->getProperty('password');
          $isLogin = $node['n']->getProperty('isLogin');
        }
        if($isLogin == 0){
          $status = 'success';
          $node = $client->getNode($properties['id']);
          $node->setProperty('gcmId', $gcmId)
          ->setProperty('isLogin', 1)
          ->save();
        }else{
          $status = 'failed, user already login in other device';
          $properties = array();
        }
      }else{
        $status = 'failed, check your username and password';
      }
    }else{
      $status = 'failed, check your gcm id';
    }
    return response()->json(array('status' => $status,'data' => $properties));
  }

  public function logoutDonatur(Request $request){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $donaturId =  $request->input('donaturId');
    $status = 'failed';
    if(count($donaturId) > 0){
      $node = $client->getNode($donaturId);
      if(count($node) > 0){
        $labels = $node->getLabels();
        $labelName = $labels[0]->getName();
        if($labelName == HelperController::getLabelDonatur()){
          $node->setProperty('gcmId', '')
          ->setProperty('isLogin', 0)
          ->save();
          $status = 'success';
        }else{
          $status = 'failed, label not match check your id';
        }
      }else{
        $status = 'failed, nothing to return check your id';
      }
    }else{
      $status = 'failed, id is empty check your id';
    }
    return response()->json(array('status' => $status));
  }
}


?>
