<?php

//function関数を読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログインページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');

//ログ取り開始
debugLogStart();

//================================
// 画面処理
//================================
//ログイン認証
require('auth.php');


//POST送信がある場合にバリデーションチェックに入る
if(!empty($_POST)){

  //POST送信された情報を変数へ格納
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_save = (!empty($_POST['pass_save'])) ? true : false;
  
  //未入力チェック
  validRequired($email,'email');
  validRequired($pass,'pass');


  //emailの形式チェック
  validEmail($email, 'email');
  //emailの最大文字数チェック
  validEmailLen($email, 'email');

   //パスワードの半角英数字チェック
   validHalf($pass, 'pass');
   //パスワードの最大文字数チェック
   validPassMax($pass, 'pass');
   //パスワードの最小文字数チェック
   validPassMin($pass, 'pass');

  if(empty($err_msg)){
    
    //DBへ接続しユーザーID情報を取ってくる処理を行う
    $id = validLogin($email,$pass,'email'); //エラーメッセージはemailの欄へ出力させる

    if(empty($err_msg)){

      //ログイン有効期限を1時間に設定
      $sesLimit = 60*60;

      //ログインタイムを記録
      $_SESSION['login_date'] = time();

      //ユーザーIDを格納
      $_SESSION['user_id'] = $id;

        //自動ログインにチェックがあった場合
        if(!empty($pass_save)){
          debug('ログイン保持にチェックがあります。');
          //ログインリミットを24時間に設定
          $_SESSION['login_limit'] = $sesLimit * 24 * 30;
        }else{

         //ログイン上限時間を格納　※ログイン有効期限の判定に使う
          $_SESSION['login_limit'] = $sesLimit;
        }

        debug('$_SESSIONの中身'.print_r($_SESSION,true));


        try{

        $dbh = dbConnect();
        $sql = 'UPDATE users SET login_time = :login_time'; 
        $data = array(':login_time' => date('Y-m-d H:i:s')); 
        $stmt = queryPost($dbh,$sql,$data);


        debug('マイページへ遷移します。');
        header('Location:profDetail.php');

        } catch (Exception $e){

        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
        }

      } 
    }

  } 

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<!DOCTYPE html>
<html lang="ja">

  <head>
    <meta charset="utf-8">
  <title>ログイン画面</title>
  <link rel="stylesheet" type="text/css" href="style.css">
  <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
  </head>

    <header class="header-non-img">
        <div class="site-width">
            <div id="header-icon" class="icon-red"></div>
            <div id="header-icon" class="icon-orange"></div>
            <div id="header-icon" class="icon-green"></div>

            <h1><a href="outputList.php">Engineer Town</a></h1>
    
            <nav id="nav-top">
                <ul>
                    <li><a href="outputList.php">掲示板</a></li>
                    <li><a href="signup.php">ユーザー登録</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <body class="background">
        <!-- メインコンテンツ　-->
        <div id="contents" class="site-width">
            <!-- main -->
            <section id="form">
                <form action="" method="post" class="form">
                  <div class="form-wrap" >
                      <div class="mail-area">
                        <p class="<?php if(!empty($err_msg['email'])) echo "err-msg"?>"><?php if(!empty($err_msg['email'])) echo $err_msg['email'];?></>
                        <input type="text" name="email" placeholder="email" class="input  <?php if(!empty($err_msg['email'])) echo "err-input"?>" style="<?php if(!empty($err_msg['email'])) echo "margin-top:0px;"?>">
                      </div>
                      <div class="pass-area">
                        <p class="<?php if(!empty($err_msg['pass'])) echo "err-msg"?>"><?php if(!empty($err_msg['pass'])) echo $err_msg['pass'];?></p>
                        <input type="password" name="pass" placeholder="パスワード" class="<?php if(!empty($err_msg['pass'])) echo "err-input"?>"><br>
                      </div>
                      <div class="text-area">
                        <input type="checkbox" name="pass_save">次回ログインを省略する<br>
                        <p>パスワードを忘れた方は<a href="passRemindSend.php">こちら<a></p>
                      </div>
                      <div class="login-button">
                        <input type="submit" value="ログイン"><br>
                      </div>
                  </div>
                </form>
            </section>
        </div>
          <!-- footer -->
        <footer id="footer">
        Copyright <a href="http://atofuzi.com/">atofuzi.life</a>. All Rights Reserved.
        </footer>
    </body>
  </html>