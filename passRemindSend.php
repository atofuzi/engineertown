<?php

//function関数を読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　パスワード再発行メール送信ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');

//ログ取り開始
debugLogStart();

//================================
// 画面処理
//================================

//POST送信がある場合にバリデーションチェックに入る
if(!empty($_POST)){

    //POST送信された情報を変数へ格納
    $email = $_POST['email'];

    //バリデーションチェック

    //未入力チェック
    validRequired($email,'email');

    if(empty($err_msg)){
    
    validEmail($email,'email');
    validEmailLen($email,'email');

    

      //例外処理 
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        // クエリ結果の値を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // EmailがDBに登録されている場合
    if($stmt && array_shift($result)){
        debug('クエリ成功。DB登録あり。');
        $_SESSION['msg_success'] = SUC01;
        
        $auth_key = makeRandKey(); //認証キー生成
            
        //メールを送信
        $from = 'atofuzi.blog@gmail.com';
        $to = $email;
        $subject = '【パスワード再発行認証】｜WEBUKATUMARKET';
          //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
          //EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
        $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：http://localhost:8888/webservice_practice07/passRemindRecieve.php
認証キー：{$auth_key}
※認証キーの有効期限は30分となります

認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
http://localhost:8888/webservice_practice07/passRemindSend.php

////////////////////////////////////////
ウェブカツマーケットカスタマーセンター
URL  https://atofuzi.com/
E-mail atofuzi.blog@gmail.com
////////////////////////////////////////
EOT;
          sendMail($from, $to, $subject, $comment);
          
          //認証に必要な情報をセッションへ保存
          $_SESSION['auth_key'] = $auth_key;
          $_SESSION['auth_email'] = $email;
          $_SESSION['auth_key_limit'] = time()+(60*30); //現在時刻より30分後のUNIXタイムスタンプを入れる
          debug('セッション変数の中身：'.print_r($_SESSION,true));
          
          header("Location:passRemindRecieve.php"); //認証キー入力ページへ

        }else{
          debug('クエリに失敗したかDBに登録のないEmailが入力されました。');
          $err_msg['common'] = MSG07;
        }

      } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
}




debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<!DOCTYPE html>
<html lang="ja">

  <head>
    <meta charset="utf-8">
  <title>パスワード再発行メール送信ページ</title>
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
        <!-- メインコンテンツ　-->
        <p id="js-show-msg" style="display:none;" class="msg-slide">
            <?php echo getSessionFlash('msg_success'); ?>
        </p>
        <div id="contents" class="site-width">
            <!-- main -->
            <section id="form">
                <h2 class="title">パスワード再設定</h2>
                <form action="" method="post" class="form">
                    <div class="form-wrap">
                        <div class="text-area">
                            <label>ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送り致します。</label>
                        </div>
                        <div class="mail-area">
                            <input type="text" name="email" placeholder="email" class="input  <?php if(!empty($err_msg['email'])) echo "err-input"?>" style="<?php if(!empty($err_msg['email'])) echo "margin-top:0px;"?>">
                            <p class="<?php if(!empty($err_msg['email'])) echo "err-msg-first"?>"><?php if(!empty($err_msg['email'])) echo $err_msg['email'];?></p>
                        </div>
                        <div class="login-button">
                            <input type="submit" value="メールを送る"><br>
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