<?php

namespace App\Http\Controllers;

use App\Donatur;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Client;

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

  public function logoutDonatur(Request $request){
    $client = new Client($this->_host, $this->_port);
    $client->getTransport()
      ->setAuth($this->_userNeo4j, $this->_passNeo4j);
    $donaturId =  $request->input('donaturId');
    $status = 'failed';
    if(count($donaturId) > 0){
      $node = $client->getNode($donaturId);
      if(count($node) > 0){
        $node->setProperty('gcmId', '')
        ->save();
        $status = 'success';
      }
    }
    return response()->json(array('status' => $status));
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
      $gcmId = $request->input('gcmId');
      $status = 'failed';

      //image upload handler
      $image = $request->input('imagePath');
      $filename  = rand().'-'. time() . '.jpg' ;
      $imageSave = base_path().'/storage/pics/';
      $imagePath = $this->_uriImage.$filename;
      $binary=base64_decode($image);
      header('Content-Type: bitmap; charset=utf-8');
      $file = fopen($imageSave.$filename, 'wb');
      fwrite($file, $binary);
      fclose($file);
      if(count($username) > 0 && count($password) > 0 ){
        $cypherCek = 'MATCH (n:'.$this->_label.') where n.username="'.$username.'" and n.password = "'.$password.'" RETURN n';
        $queryCek = new Query($client, $cypherCek);
        $resultCek = $queryCek->getResultSet();
        if(count($resultCek) > 0){
          $status = 'failed, data already created';
        }else{
          $cypher = 'CREATE (n:'.$this->_label.' {username:"'.$username.'",password:"'.$password.'",email:"'.$email.'",nama:"'.$nama.'",notelp:"'.$notelp.'",imagePath:"'.$imagePath.'",gcmId:"'.$gcmId.'"}) return n';
          $query = new Query($client, $cypher);
          $query->getResultSet();
          $status = 'success';
        }
      }
      return response()->json(array('status' => $status));
  }

  public function createDonasi(Request $request){
      $client = new Client($this->_host, $this->_port);
      $client->getTransport()
        ->setAuth($this->_userNeo4j, $this->_passNeo4j);
      $donaturId = $request->input('donaturId');
      $mustahiqId = $request->input('mustahiqId');
      $jenisDonasi = $request->input('jenisDonasi');
      $nama = $request->input('nama');
      $nominal = $request->input('nominal');
      $bank = $request->input('bank');
      $norek = $request->input('norek');
      $namaPengirim = $request->input('namaPengirim');
      $lazis = $request->input('lazis');
      $namaBarang = $request->input('namaBarang');
      $alamat = $request->input('alamat');
      $tglJemput = $request->input('tglJemput');
      $waktu = $request->input('waktu');
      $status = 'failed';
      $datenow = date('Y-m-d H:i:s');
      $donatur = $client->getNode($donaturId);
      $mustahiq = $client->getNode($mustahiqId);

      $imagePath = '';
      //image upload handler
      if($jenisDonasi == 1){
        $image = $request->input('imagePath');
        $filename  = rand().'-'. time() . '.jpg' ;
        $imageSave = base_path().'/storage/pics/';
        $imagePath = $this->_uriImage.$filename;
        $binary=base64_decode($image);
        header('Content-Type: bitmap; charset=utf-8');
        $file = fopen($imageSave.$filename, 'wb');
        fwrite($file, $binary);
        fclose($file);
      }
      if(count($donatur) > 0 && count($mustahiq) >0){
        $node = $client->getNode($mustahiqId);
        $jumlahPenolong = $node->getProperty('jumlahPenolong');
        $persentaseBantuan = $node->getProperty('persentaseBantuan');
        $nominalBantuan = $node->getProperty('nominal');
        $jumlahPenolong++;
        $persentase = ($nominal/$nominalBantuan) * 100;
        $persentaseBantuan = $persentaseBantuan + $persentase;
        $node->setProperty('jumlahPenolong', $jumlahPenolong)
        ->setProperty('persentaseBantuan', $persentaseBantuan)
        ->save();
        $donatur->relateTo($mustahiq, 'DONASI')
        ->setProperty('tanggal', $datenow)
        ->setProperty('nama', $nama)
        ->setProperty('donaturId', $donaturId)
        ->setProperty('nominal', $nominal)
        ->setProperty('bank', $bank)
        ->setProperty('norek', $norek)
        ->setProperty('namaPengirim', $namaPengirim)
        ->setProperty('lazis', $lazis)
        ->setProperty('namaBarang', $namaBarang)
        ->setProperty('alamat', $alamat)
        ->setProperty('tglJemput', $tglJemput)
        ->setProperty('waktu', $waktu)
        ->setProperty('imagePath', $imagePath)
        ->save();
        $status = 'success';
      }
      return response()->json(array('status' => $status));
  }


  public function getDonasi($id){
    $client = new Client($this->_host, $this->_port);
    $client->getTransport()
      ->setAuth($this->_userNeo4j, $this->_passNeo4j);
    $cypher = 'MATCH (DONATUR)-[r:DONASI]->(MUSTAHIQ) where id(MUSTAHIQ)='.$id.' RETURN r LIMIT 100';
    $query = new Query($client, $cypher);
    $nodes = $query->getResultSet();
    $status = 'failed';
    $properties = array();
    $result = array();
    if(count($nodes) > 0){
      $status = 'success';
      foreach ($nodes as $node) {
        $properties['id'] = $node['r']->getId();
        $properties['properties'] = $node['r']->getProperties();
        array_push($result,$properties);
      }
    }
    return response()->json(array('status' => $status,'data' => $result));
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

  public function deleteAllDonatur(){
    $client = new Client($this->_host, $this->_port);
    $client->getTransport()
      ->setAuth($this->_userNeo4j, $this->_passNeo4j);
    $cypher = 'MATCH (n:Donatur) OPTIONAL MATCH (n)-[r]-() DELETE n,r';
    $query = new Query($client, $cypher);
    $query->getResultSet();
    $status = 'success';
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
    $gcmId = $request->input('gcmId');
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
      ->setProperty('gcmId', $gcmId)
      ->save();
      $status = 'success';
    }
    return response()->json(array('status' => $status));
  }
}
?>
