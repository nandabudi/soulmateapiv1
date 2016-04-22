<?php

namespace App\Http\Controllers;

use App\Donatur;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Client;

class MustahiqController extends Controller{

  public function getAllMustahiq(){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $label = $client->makeLabel(HelperController::getLabelMustahiq());
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
  
  public function getMustahiqByApproved($id){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $status = 'failed';
    $properties = array();
    $result = array();
    if(count($id) > 0){
      $cypher = 'MATCH (n:'.HelperController::getLabelMustahiq().') where n.isApproved="'.$id.'" RETURN n';
      $query = new Query($client, $cypher);
      $nodes = $query->getResultSet();
      if(count($nodes) > 0){
        $status = 'success';
        foreach($nodes as $node){
          $properties['id'] = $node['n']->getId();
          $properties['properties'] = $node['n']->getProperties();
          array_push($result,$properties);
        }
      }else{
        $status = 'failed, return value is empty check your mustahiq category';
      }
    }else{
      $status = 'failed, mustahiq category is empty please check your parameter';
    }
    return response()->json(array('status' => $status,'data' => $result));
  }

  public function getMustahiq($id){
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
          if($label == HelperController::getLabelMustahiq()){
            $status = 'success';
            $properties['id'] = $nodes->getId();
            $properties['properties'] = $nodes->getProperties();
          }else{
            $status = 'failed, the label is not mustahiq check your parameter';
          }
        }else{
          $status = 'failed, the label is not mustahiq check your parameter';
        }
      }else{
        $status = 'failed, return value is empty check your mustahiq id';
      }
    }else{
      $status = 'failed, mustahiq id is empty please check your parameter';
    }
    return response()->json(array('status' => $status,'data' => $properties));
  }

  public function getMustahiqByKategori($id){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $status = 'failed';
    $properties = array();
    $result = array();
    if(count($id) > 0){
      $cypher = 'MATCH (n:'.HelperController::getLabelMustahiq().') where n.kategori="'.$id.'" RETURN n';
      $query = new Query($client, $cypher);
      $nodes = $query->getResultSet();
      if(count($nodes) > 0){
        $status = 'success';
        foreach($nodes as $node){
          $properties['id'] = $node['n']->getId();
          $properties['properties'] = $node['n']->getProperties();
          array_push($result,$properties);
        }
      }else{
        $status = 'failed, return value is empty check your mustahiq category';
      }
    }else{
      $status = 'failed, mustahiq category is empty please check your parameter';
    }
    return response()->json(array('status' => $status,'data' => $result));
  }

  public function createMustahiq(Request $request){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
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
    $jumlahPenolong = 0;
    $prioritas = 0;
    $isApproved = 'NO';
    $statusRequest = 'failed';

    if(count($nama) > 0 && count($latlong) > 0 && count($donaturId) > 0){
        $nodeDonatur = $client->getNode($donaturId);
        if(count($nodeDonatur) > 0){
          if(count($nodeDonatur->getProperties()) > 0){
            $labels = $nodeDonatur->getLabels();
            $label = $labels[0]->getName();
            $gcmId = $nodeDonatur->getProperty('gcmId');
            if($label == HelperController::getLabelDonatur()){
              //image upload handler
              $image = $request->input('imagePath');
              $imagePath = HelperController::saveImageWithReturn($image,'mustahiq');
              $cypher = 'CREATE (n:'.HelperController::getLabelMustahiq().' {nama:"'.$nama.'",desc:"'.$desc.'"
              ,tempatLahir:"'.$tempatLahir.'",tanggalLahir:"'.$tanggalLahir.'",nominal:'.$nominal.'
              ,alamat:"'.$alamat.'",latlong:"'.$latlong.'",status:"'.$status.'",jenjangPendidikan:"'.$jenjangPendidikan.'"
              ,asalSekolah:"'.$asalSekolah.'",alamatSekolah:"'.$alamatSekolah.'",namaOrangTua:"'.$namaOrangTua.'",alamatOrangTua:"'.$alamatOrangTua.'"
              ,pekerjaanOrangTua:"'.$pekerjaanOrangTua.'",kategori:"'.$kategori.'",persentaseBantuan:'.$persentaseBantuan.'
              ,prioritas:'.$prioritas.',imagePath:"'.$imagePath.'",isApproved:"'.$isApproved.'"
              ,tahunLahir:'.$tahunLahir.',jumlahPenolong:'.$jumlahPenolong.',donaturId:'.$donaturId.'}) return n';
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
              NotifikasiController::createNotifikasiNode($donaturId,$mustahiqId,$nama,$datenow,$imagePath,-1,'rekomendasi');
              GCMController::gcmPushNotifikasi('rekomendasi',$gcmId);

            }else{
              $statusRequest = 'failed, the label is not donatur check your parameter';
            }
          }else{
            $statusRequest = 'failed, the label is not donatur check your parameter';
          }
        }else{
          $statusRequest = 'failed, return value is empty check your donatur id';
        }
    }else{
      $statusRequest = 'failed, please check your parameter';
    }
    return response()->json(array('status' => $statusRequest));
  }

  public function deleteMustahiq($id){
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
          if($label == HelperController::getLabelMustahiq()){
            $cypher = 'START n=node('.$id.') MATCH n-[r]-() DELETE r, n';
            $query = new Query($client, $cypher);
            $query->getResultSet();
            $status = 'success';
          }else{
            $status = 'failed, the label is not mustahiq check your parameter';
          }
        }else{
          $status = 'failed, the label is not mustahiq check your parameter';
        }
      }else{
        $status = 'failed, return value is empty check your mustahiq id ';
      }
    }else{
      $status = 'failed, mustahiq id is empty please check your parameter';
    }

    return response()->json(array('status' => $status));
  }

  public function deleteAllMustahiq(){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $cypher = 'MATCH (n:Mustahiq) OPTIONAL MATCH (n)-[r]-() DELETE n,r';
    $query = new Query($client, $cypher);
    $query->getResultSet();
    $status = 'success';
    return response()->json(array('status' => $status));
  }

  public function updateMustahiq(Request $request,$id){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
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
