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
if(isset($_POST['friend_id']) && isset($_SESSION['user_id']) && isLogin()){
  debug('POST送信があります。');
  $friend_id = $_POST['friend_id'];
  debug('フレンドID：'.print_r($friend_id,true));
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // レコードがあるか検索
    // likeという単語はLIKE検索とうSQLの命令文で使われているため、そのままでは使えないため、｀（バッククウォート）で囲む
    $sql = 'SELECT * FROM frienduser WHERE user_id = :u_id AND friend_id = :friend_id';
    $data = array(':u_id' => $_SESSION['user_id'], ':friend_id' => $friend_id);

    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $resultCount = $stmt->rowCount();
    debug('レコード数'.$resultCount);
    
    // レコードが１件でもある場合
    if(!empty($resultCount)){
      // レコードを削除する
      $sql = 'DELETE FROM frienduser WHERE user_id = :u_id AND friend_id = :friend_id';
      $data = array(':u_id' => $_SESSION['user_id'], ':friend_id' => $friend_id);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      echo false;
    }else{
      // レコードを挿入する
      $sql = 'INSERT INTO frienduser (user_id, friend_id, create_date) VALUES (:u_id, :friend_id, :date)';
      $data = array(':u_id' => $_SESSION['user_id'], ':friend_id' => $friend_id, ':date' => date('Y-m-d H:i:s'));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      echo true;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
debug('Ajax処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');