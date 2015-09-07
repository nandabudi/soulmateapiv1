<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Client;

class DonasiController extends Controller{

  public function createDonasi(Request $request){
      $client = new Client(HelperController::getHost(), HelperController::getPort());
      $client->getTransport()
        ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());

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

      if(count($donaturId)>0 && count($mustahiqId)> 0){

        $donatur = $client->getNode($donaturId);
        $mustahiq = $client->getNode($mustahiqId);

        if(count($donatur) > 0 && count($mustahiq) > 0){
          $imagePath = '';
          //image upload handler
          if($jenisDonasi == 1){
            $image = $request->input('imagePath');
            $imagePath = HelperController::saveImageWithReturn($image,'donasi');
          }

          //proses edit persentasi dan jumlah penolong
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

          // proses untuk menambahkan relasi donatur ke mustahiq
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
          ->setProperty('isValidate',0)
          ->save();
          $status = 'success';

          //push notification
          $gcmId = $donatur->getProperty('gcmId');
          GCMController::gcmPushNotifikasi('donasi',$gcmId);
        }else{
          $status = 'failed, return value is empty check your donatur or mustahiq id';
        }
      }else{
        $status = 'failed, donatur or mustahiq id is empty';
      }

      return response()->json(array('status' => $status));
  }


  public function getDonasi($id){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $status = 'failed';
    $properties = array();
    $result = array();
    if(count($id) > 0){
      $cypher = 'MATCH (DONATUR)-[r:DONASI]->(MUSTAHIQ) where id(MUSTAHIQ)='.$id.' and r.isValidate=1 RETURN r LIMIT 100';
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
        $status = 'failed, return value is empty check your mustahiq id';
      }
    }else{
      $status = 'failed, mustahiq id is empty';
    }
    return response()->json(array('status' => $status,'data' => $result));
  }

  public function getAllDonasi(){
    $client = new Client(HelperController::getHost(), HelperController::getPort());
    $client->getTransport()
      ->setAuth(HelperController::getUserNeo4j(), HelperController::getPassNeo4j());
    $status = 'failed';
    $properties = array();
    $result = array();
    $cypher = 'MATCH (DONATUR)-[r:DONASI]->(MUSTAHIQ) RETURN r LIMIT 100';
    $query = new Query($client, $cypher);
    $nodes = $query->getResultSet();
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
}


?>
