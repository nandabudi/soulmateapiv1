<?php

namespace App\Http\Controllers;

use App\Donatur;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Client;

class DonaturController extends Controller{

  public function loginDonatur(Request $request){
    $client = new Client('localhost', 7474);
    $client->getTransport()
      ->setAuth('neo4j', 'soulmate');
    $username =  $request->input('username');
    $password =  $request->input('password');
    $cypher = 'MATCH (n:Muzakki) where n.username="'.$username.'" and n.password = "'.$password.'" RETURN n as muzakki';
    $query = new Query($client, $cypher);
    $nodes = $query->getResultSet();
    print_r(count($nodes));
    print_r($cypher);
    $status = 'failed';
    $properties = array();
    if(count($nodes) > 0){
      $status = 'success';
      foreach($nodes as $node){
        $properties['id'] = $node['muzakki']->getId();
        $properties['username'] = $node['muzakki']->getProperty('username');
        $properties['password'] = $node['muzakki']->getProperty('password');
      }
    }

    return response()->json(array('status' => $status,'data' => $properties));
  }

  public function index(){
    // $client = new Client('localhost', 7474);
    // $client->getTransport()
    //   ->setAuth('neo4j', 'soulmate');
    $client = new Client('localhost', 7474);
    $client->getTransport()
      ->setAuth('neo4j', 'soulmate');
    $label = $client->makeLabel('Muzakki');
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
    $client = new Client('localhost', 7474);
    $client->getTransport()
      ->setAuth('neo4j', 'soulmate');
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

      // $Book = Book::create($request->all());
      $client = new Client('localhost', 7474);
      $client->getTransport()
        ->setAuth('neo4j', 'soulmate');
      $transaction = $client->beginTransaction();
      // Add a single query to a transaction, $result is a single ResultSet object
      $label = 'Muzakki';
      $param = 'nanda';
      $cypher = 'CREATE (n:'.$label.' {username:"'.$param.'",password:"nanda"}) return n';
      $query = new Query($client, $cypher);
      $result = $query->getResultSet();
      $status = 'failed';
      if ($transaction->commit()){
        $status = 'success';
      }
      return response()->json(array('status' => $status));

  }

  public function deleteDonatur($id){
      $Book  = Book::find($id);
      $Book->delete();

      return response()->json('deleted');
  }

  public function updateDonatur(Request $request,$id){
      $Book  = Book::find($id);
      $Book->title = $request->input('title');
      $Book->author = $request->input('author');
      $Book->isbn = $request->input('isbn');
      $Book->save();

      return response()->json($Book);
  }
}
?>
