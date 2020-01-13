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
if(isset($_POST['op_id']) && isset($_SESSION['user_id']) && isLogin()){
  debug('POST送信があります。');
  $op_id = $_POST['op_id'];
  debug('アウトプットID：'.print_r($op_id,true));
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // レコードがあるか検索
    // likeという単語はLIKE検索とうSQLの命令文で使われているため、そのままでは使えないため、｀（バッククウォート）で囲む
    $sql = 'SELECT * FROM likeoutput WHERE user_id = :u_id AND op_id = :op_id';
    $data = array(':u_id' => $_SESSION['user_id'], ':op_id' => $op_id);

    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $resultCount = $stmt->rowCount();
    debug('クリック前の自分のお気に入り数'.$resultCount);
    
    // レコードが１件でもある場合
    if(!empty($resultCount)){
      // レコードを削除する
      debug('自分のお気に入り登録を削除');
      $sql = 'DELETE FROM likeoutput WHERE user_id = :u_id AND op_id = :op_id';
      $data = array(':u_id' => $_SESSION['user_id'], ':op_id' => $op_id);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
    }else{
      // レコードを挿入する
      debug('自分のお気に入りアウトプットとして登録');
      $sql = 'INSERT INTO `likeoutput` (user_id, op_id, create_date) VALUES (:u_id, :op_id, :date)';
      $data = array(':u_id' => $_SESSION['user_id'], ':op_id' => $op_id, ':date' => date('Y-m-d H:i:s'));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }

  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // レコードがあるか検索
    // likeという単語はLIKE検索とうSQLの命令文で使われているため、そのままでは使えないため、｀（バッククウォート）で囲む
    $sql = 'SELECT * FROM likeoutput WHERE op_id = :op_id';
    $data = array(':op_id' => $op_id);

    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $allCount = $stmt->rowCount();
    debug('クリック後の総お気に入り数'.$allCount);
    // レコードが１件でもある場合
    if(!empty($allCount)){
        echo $allCount;
    }else{
        echo "";
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}else{
   echo false;
}

debug('Ajax処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
