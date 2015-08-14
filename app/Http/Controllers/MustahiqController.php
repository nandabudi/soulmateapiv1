<?php

namespace App\Http\Controllers;

use App\Donatur;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Client;

class MustahiqController extends Controller{

  public $_host = 'localhost';
  public $_port = 7474;
  public $_userNeo4j = 'neo4j';
  public $_passNeo4j = 'soulmate';
  public $_label = 'Mustahiq';
  public $_uriImage = 'http://soulmateapi.cloudapp.net/api/v1/images/';

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

  public function getMustahiq($id){
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

  public function getMustahiqByKategori($id){
    $client = new Client($this->_host, $this->_port);
    $client->getTransport()
      ->setAuth($this->_userNeo4j, $this->_passNeo4j);
    $cypher = 'MATCH (n:'.$this->_label.') where n.kategori="'.$id.'" RETURN n';
    $query = new Query($client, $cypher);
    $nodes = $query->getResultSet();
    $status = 'failed';
    $properties = array();
    $result = array();
    if(count($nodes) > 0){
      $status = 'success';
      foreach($nodes as $node){
        $properties['id'] = $node['n']->getId();
        $properties['properties'] = $node['n']->getProperties();
        array_push($result,$properties);
      }
    }
    return response()->json(array('status' => $status,'data' => $result));
  }

  public function createMustahiq(Request $request){
      $client = new Client($this->_host, $this->_port);
      $client->getTransport()
        ->setAuth($this->_userNeo4j, $this->_passNeo4j);
      $nama = $request->input('nama');
      $desc = $request->input('desc');
      $tempatLahir = $request->input('tempatLahir');
      $tanggalLahir = $request->input('tanggalLahir');
      $nominal = $request->input('nominal');
      $alamat = $request->input('alamat');
      $latlong = $request->input('latlong');
      $status = $request->input('status');
      $jenjangPendidikan = $request->input('jenjangPendidikan');
      $asalSekolah = $request->input('asalSekolah');
      $alamatSekolah = $request->input('alamatSekolah');
      $namaOrangTua = $request->input('namaOrangTua');
      $alamatOrangTua = $request->input('alamatOrangTua');
      $pekerjaanOrangTua = $request->input('pekerjaanOrangTua');
      $kategori = $request->input('kategori');
      $donaturId = $request->input('donaturId');
      $tahunLahir = $request->input('tahunLahir');
      $persentaseBantuan = 0;
      $prioritas = 'low';
      $isApproved = 'NO';
      $statusRequest = 'failed';
      //image upload handler
      $image = $request->input('imagePath');
      $filename  = $tanggalLahir.'-'. time() . '.jpg' ;
      $imageSave = base_path().'/storage/pics/';
      $imagePath = $this->_uriImage.$filename;
      $binary=base64_decode($image);
      header('Content-Type: bitmap; charset=utf-8');
      $file = fopen($imageSave.$filename, 'wb');
      fwrite($file, $binary);
      fclose($file);

      if(count($nama) > 0 && count($latlong) > 0 ){
          $cypher = 'CREATE (n:'.$this->_label.' {nama:"'.$nama.'",desc:"'.$desc.'"
          ,tempatLahir:"'.$tempatLahir.'",tanggalLahir:"'.$tanggalLahir.'",nominal:'.$nominal.'
          ,alamat:"'.$alamat.'",latlong:"'.$latlong.'",status:"'.$status.'",jenjangPendidikan:"'.$jenjangPendidikan.'"
          ,asalSekolah:"'.$asalSekolah.'",alamatSekolah:"'.$alamatSekolah.'",namaOrangTua:"'.$namaOrangTua.'",alamatOrangTua:"'.$alamatOrangTua.'"
          ,pekerjaanOrangTua:"'.$pekerjaanOrangTua.'",kategori:"'.$kategori.'",persentaseBantuan:'.$persentaseBantuan.'
          ,prioritas:"'.$prioritas.'",imagePath:"'.$imagePath.'",isApproved:"'.$isApproved.'",tahunLahir:"'.$tahunLahir.'"}) return n';
          $query = new Query($client, $cypher);
          $nodes = $query->getResultSet();

          // add mustahiq relationship
          $datenow = date('Y-m-d H:i:s');
          $mustahiqId = 0;
          foreach($nodes as $node){
             $mustahiqId = $node['n']->getId();
          }
          $donatur = $client->getNode($donaturId);
          $mustahiq = $client->getNode($mustahiqId);
          $donatur->relateTo($mustahiq, 'RECOMMENDED')
          ->setProperty('tanggal', $datenow)
          ->save();
          $statusRequest = 'success';
      }
      return response()->json(array('status' => $statusRequest));
  }

  public function deleteMustahiq($id){
    $client = new Client($this->_host, $this->_port);
    $client->getTransport()
      ->setAuth($this->_userNeo4j, $this->_passNeo4j);
    $node = $client->getNode($id);
    $status = 'failed';
    if(count($node) > 0){
      $cypher = 'START n=node('.$id.') MATCH n-[r]-() DELETE r, n';
      $query = new Query($client, $cypher);
      $query->getResultSet();
      $status = 'success';
    }
    return response()->json(array('status' => $status));
  }

  public function deleteAllMustahiq(){
    $client = new Client($this->_host, $this->_port);
    $client->getTransport()
      ->setAuth($this->_userNeo4j, $this->_passNeo4j);
    $cypher = 'MATCH (n:Mustahiq) OPTIONAL MATCH (n)-[r]-() DELETE n,r';
    $query = new Query($client, $cypher);
    $query->getResultSet();
    $status = 'success';
    return response()->json(array('status' => $status));
  }

  public function updateMustahiq(Request $request,$id){
    $client = new Client($this->_host, $this->_port);
    $client->getTransport()
      ->setAuth($this->_userNeo4j, $this->_passNeo4j);
    $nama = $request->input('nama');
    $desc = $request->input('desc');
    $tempatLahir = $request->input('tempatLahir');
    $tanggalLahir = $request->input('tanggalLahir');
    $nominal = $request->input('nominal');
    $alamat = $request->input('alamat');
    $latlong = $request->input('latlong');
    $status = $request->input('status');
    $jenjangPendidikan = $request->input('jenjangPendidikan');
    $asalSekolah = $request->input('asalSekolah');
    $alamatSekolah = $request->input('alamatSekolah');
    $namaOrangTua = $request->input('namaOrangTua');
    $alamatOrangTua = $request->input('alamatOrangTua');
    $pekerjaanOrangTua = $request->input('pekerjaanOrangTua');
    $kategori = $request->input('kategori');
    $persentaseBantuan = $request->input('persentaseBantuan');
    $prioritas = $request->input('prioritas');
    $imagePath = $request->input('imagePath');
    $isApproved = $request->input('isApproved');
    $tahunLahir = $request->input('tahunLahir');
    $statusRequest = 'failed';
    if(count($id) > 0){
      $node = $client->getNode($id);
      $node->setProperty('nama', $nama)
      ->setProperty('desc', $desc)
      ->setProperty('tempatLahir', $tempatLahir)
      ->setProperty('tanggalLahir', $tanggalLahir)
      ->setProperty('alamat', $alamat)
      ->setProperty('latlong', $latlong)
      ->setProperty('status', $status)
      ->setProperty('jenjangPendidikan', $jenjangPendidikan)
      ->setProperty('asalSekolah', $asalSekolah)
      ->setProperty('alamatSekolah', $alamatSekolah)
      ->setProperty('namaOrangTua', $namaOrangTua)
      ->setProperty('alamatOrangTua', $alamatOrangTua)
      ->setProperty('pekerjaanOrangTua', $pekerjaanOrangTua)
      ->setProperty('kategori', $kategori)
      ->setProperty('persentaseBantuan', $persentaseBantuan)
      ->setProperty('prioritas', $prioritas)
      ->setProperty('imagePath', $imagePath)
      ->setProperty('nominal', $nominal)
      ->setProperty('isApproved', $isApproved)
      ->setProperty('tahunLahir', $tahunLahir)
      ->save();
      $statusRequest = 'success';
    }
    return response()->json(array('status' => $statusRequest));
  }
}
?>
