<?php

namespace App\Http\Controllers;

use App\Donatur;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Client;
use Intervention\Image\ImageManagerStatic as Image;

class DonaturController extends Controller{

  public $_host = 'localhost';
  public $_port = 7474;
  public $_userNeo4j = 'neo4j';
  public $_passNeo4j = 'soulmate';
  public $_label = 'Donatur';
  public $_uriImage = 'http://soulmateapi.cloudapp.net/api/v1/images/';

  public function loginDonatur(Request $request){
    $client = new Client($this->_host, $this->_port);
    $client->getTransport()
      ->setAuth($this->_userNeo4j, $this->_passNeo4j);
    $username =  $request->input('username');
    $password =  sha1($request->input('password'));
    $cypher = 'MATCH (n:'.$this->_label.') where n.username="'.$username.'" and n.password = "'.$password.'" RETURN n';
    $query = new Query($client, $cypher);
    $nodes = $query->getResultSet();
    $status = 'failed';
    $properties = array();
    if(count($nodes) > 0){
      $status = 'success';
      foreach($nodes as $node){
        $properties['id'] = $node['n']->getId();
        $properties['username'] = $node['n']->getProperty('username');
        $properties['password'] = $node['n']->getProperty('password');
      }
    }

    return response()->json(array('status' => $status,'data' => $properties));
  }

  public function index(){
    $client = new Client($this->_host, $this->_port);
    $client->getTransport()
      ->setAuth($this->_userNeo4j, $this->_passNeo4j);
    $label = $client->makeLabel($this->_label);
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
    $client = new Client($this->_host, $this->_port);
    $client->getTransport()
      ->setAuth($this->_userNeo4j, $this->_passNeo4j);
    $nodes = $client->getNode($id);
    $status = 'failed';
    $properties = array();
    if(count($nodes) > 0){
      $status = 'success';
      $properties['id'] = $nodes->getId();
      $properties['properties'] = $nodes->getProperties();
    }
    return response()->json(array('status' => $status,'data' => $properties));
  }

  public function createDonatur(Request $request){
      $client = new Client($this->_host, $this->_port);
      $client->getTransport()
        ->setAuth($this->_userNeo4j, $this->_passNeo4j);
      $username =  $request->input('username');
      $password =  sha1($request->input('password'));
      $email = $request->input('email');
      $nama = $request->input('nama');
      $notelp = $request->input('notelp');
      $status = 'failed';
      $imagePath = '';
      if($request->file()){
        $image = $request->file('imagePath');
        $filename  = $username.'-'. time() . '.' . $image->getClientOriginalExtension();
        $imageSave = base_path().'/storage/pics/'.$filename;
        echo $imageSave;
        // $imagePath = $this->_uriImage.$filename;
        // Image::make($image->getRealPath())->save($imageSave);
      }
      // if(count($username) > 0 && count($password) > 0 ){
      //   $cypherCek = 'MATCH (n:'.$this->_label.') where n.username="'.$username.'" and n.password = "'.$password.'" RETURN n';
      //   $queryCek = new Query($client, $cypherCek);
      //   $resultCek = $queryCek->getResultSet();
      //   if(count($resultCek) > 0){
      //     $status = 'failed, data already created';
      //   }else{
      //     $cypher = 'CREATE (n:'.$this->_label.' {username:"'.$username.'",password:"'.$password.'",email:"'.$email.'",nama:"'.$nama.'",notelp:"'.$notelp.'",imagePath:"'.$imagePath.'"}) return n';
      //     $query = new Query($client, $cypher);
      //     $query->getResultSet();
      //     $status = 'success';
      //   }
      // }
      return response()->json(array('status' => $status));
  }

  public function deleteDonatur($id){
    $client = new Client($this->_host, $this->_port);
    $client->getTransport()
      ->setAuth($this->_userNeo4j, $this->_passNeo4j);
    $node = $client->getNode($id);
    $status = 'failed';
    if(count($node) > 0){
      $node->delete();
      $status = 'success';
    }
    return response()->json(array('status' => $status));
  }

  public function updateDonatur(Request $request,$id){
    $client = new Client($this->_host, $this->_port);
    $client->getTransport()
      ->setAuth($this->_userNeo4j, $this->_passNeo4j);
    $username = $request->input('username');
    $email = $request->input('email');
    $nama = $request->input('nama');
    $notelp = $request->input('notelp');
    $imagePath = $request->input('imagePath');
    $status = 'failed';
    $cypherCek = 'MATCH (n:'.$this->_label.') where n.username="'.$username.'" RETURN n';
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
      ->save();
      $status = 'success';
    }
    return response()->json(array('status' => $status));
  }
}
?>
