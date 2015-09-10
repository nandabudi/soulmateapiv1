<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use PushNotification;

class GCMController extends Controller{

  public static function getPesan($from){
    if($from == 'rekomendasi'){
      return 'Terima kasih, data mustahiq akan segera di validasi :)';
    }else if($from == 'validasimustahiq'){
      return 'Terima kasih, data mustahiq berhasil di validasi :)';
    }else if ($from == 'donasi'){
      return 'Terima kasih, data donasi akan segera di validasi :)';
    }else if ($from == 'validasidonasi'){
      return 'Terima kasih, data donasi berhasil di validasi :)';
    }else{
      return 'Salah kategori';
    }
  }

  public static function gcmPushNotifikasi($from,$gmcId){
    $pesan = self::getPesan($from);
    $collection = PushNotification::app(['environment' => 'production',
        'apiKey'      => HelperController::getApiKeyGCM(),
        'service'     => 'gcm'])
        ->to($gmcId)
        ->send($pesan);
  }
}


?>
