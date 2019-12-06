<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　Ajax　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// Ajax処理
//================================

// postがあり、ユーザーIDがあり、ログインしている場合
if(isset($_POST['delete_id']) && isset($_SESSION['user_id']) && isLogin()){
  debug('POST送信があります。');
  $delete_id = $_POST['delete_id'];
  debug('削除するアウトプットID：'.print_r($delete_id,true));
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
        $sql = 'UPDATE `output` SET delete_flg = :delete_flg WHERE id = :id'; 
        $data = array(':delete_flg' => 1 , ':id' => $delete_id); 
        $stmt = queryPost($dbh,$sql,$data);


  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }

}

debug('Ajax処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>