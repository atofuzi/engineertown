<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード再発行認証キー入力ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証はなし（ログインできない人が使う画面なので）

//SESSIONに認証キーがあるか確認、なければリダイレクト
if(empty($_SESSION['auth_key'])){
   header("Location:passRemindSend.php"); //認証キー送信ページへ
}

//================================
// 画面処理
//================================
//post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST,true));
  
  //変数に認証キーを代入
  $auth_key = $_POST['token'];

  //未入力チェック
  validRequired($auth_key, 'token');

  if(empty($err_msg)){
    debug('未入力チェックOK。');
    
    //固定長チェック
    validLength($auth_key, 'token');
    //半角チェック
    validHalf($auth_key, 'token');

    if(empty($err_msg)){
      debug('バリデーションOK。');
      
      if($auth_key !== $_SESSION['auth_key']){
        $err_msg['token'] = MSG11;
      }
      if(time() > $_SESSION['auth_key_limit']){
        $err_msg['token'] = MSG12;
      }
      
      if(empty($err_msg)){
        debug('認証OK。');
        
        $pass = makeRandKey(); //パスワード生成
        
        //例外処理
        try {
          // DBへ接続
          $dbh = dbConnect();
          // SQL文作成
          $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
          $data = array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass, PASSWORD_DEFAULT));
          // クエリ実行
          $stmt = queryPost($dbh, $sql, $data);

          // クエリ成功の場合
          if($stmt){
            debug('クエリ成功。');

            //メールを送信
            $from = 'atofuzi.blog@gmail.com';
            $to = $_SESSION['auth_email'];
            $subject = '【パスワード再発行完了】｜EngineerTown';
            //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
            //EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
            $comment = <<<EOT
本メールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力頂き、ログインください。

ログインページ：http://localhost:8888/webservice_practice07/login.php
再発行パスワード：{$pass}
※ログイン後、パスワードのご変更をお願い致します

////////////////////////////////////////
ウェブカツマーケットカスタマーセンター
URL  http://atofuzu.com/
E-mail atofuzi.blog@gmail.com
////////////////////////////////////////
EOT;
            sendMail($from, $to, $subject, $comment);

            //セッション削除
            session_unset();
            $_SESSION['msg_success'] = SUC01;
            debug('セッション変数の中身：'.print_r($_SESSION,true));

            header("Location:login.php"); //ログインページへ

          }else{
            debug('クエリに失敗しました。');
            $err_msg['common'] = MSG07;
          }

        } catch (Exception $e) {
          error_log('エラー発生:' . $e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="ja">

  <head>
    <meta charset="utf-8">
  <title>パスワード再発行画面</title>
  <link rel="stylesheet" type="text/css" href="style.css">
  <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
  </head>

    <header>
        <div class="site-width">
            <div id="header-icon" class="icon-red"></div>
            <div id="header-icon" class="icon-orange"></div>
            <div id="header-icon" class="icon-green"></div>

            <h1><a href="outputList.php">Engineer Town</a></h1>
    
            <nav id="nav-top">
                <ul>
                    <li><a href="outputList.php">掲示板</a></li>
                    <li><a href="login.php">ログイン</a></li>
                    <li><a href="signup.php">ユーザー登録</a></li>
                </ul>
            </nav>
        </div>
    </header>


    <body>
    <!-- メニュー -->
  
    <p id="js-show-msg" style="display:none;" class="msg-slide">
      <?php echo getSessionFlash('msg_success'); ?>
    </p>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <!-- Main -->
          <section id="form">
                <h2 class="title">パスワード再設定</h2>
                <form action="" method="post" class="form">
                    <div class="form-wrap">
                        <div class="text-area">
                            <label>ご指定のメールアドレスお送りした【パスワード再発行認証】メール内にある「認証キー」をご入力ください。</label>
                            <div class="err-msg">
                                <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                            </div>
                        </div>
                        <div class="token-area">
                            <input type="text" name="token" placeholder="認証キー" class="input  <?php if(!empty($err_msg['token'])) echo "err-input"?>">
                            <p class="err-msg"><?php if(!empty($err_msg['token'])) echo $err_msg['token'];?></p>
                        </div>
                        <div class="login-button">
                            <input type="submit" value="再発行する"><br>
                        </div>
                        <div class="text-area">
                            <a href="passRemindSend.php">&lt; パスワード再発行メールを再度送信する</a>
                        </div>
                    </div>
                </form>
          </section>
    </div>
    <!-- footer -->

    <?php
      require('footer.php');
    ?>

    </body>

</html>