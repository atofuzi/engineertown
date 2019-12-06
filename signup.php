<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザー登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();



debug($_SERVER["REQUEST_URI"]);

if(!empty($_POST)){
  $name = $_POST['name'];
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];
  //未入力チェック
  validRequired($name,'name');
  validRequired($email,'email');
  validRequired($pass,'pass');
  validRequired($name,'pass_re');

  if(empty($err_msg)){
  
    //email形式チェック
    validEmail($email,'email');
    //email文字数チェック
    validEmailLen($email,'email');
     //email同値チェック
    validEmailDup($email,'email');

    //パスワードの文字数チェック
    validPassMin($pass,'pass');
    validPassMax($pass,'pass');
    validMatch($pass,$pass_re,'pass');
    if(empty($err_msg)){
  

      //以上の結果何もなければDBへのユーザー登録処理を行う
      if(empty($err_msg)){
        try{
        $dbh = dbConnect();
        $sql = 'INSERT INTO users (name,email,password,login_time,create_date) VALUE (:name,:email,:pass,:login_time,:create_date)';
        $data = array(':name'=> $name , ':email' => $email , ':pass' => password_hash($pass,PASSWORD_DEFAULT),':login_time' => date('Y-m-d H:i:s'), ':create_date' => date('Y-m-d H:i:s'));
    
        $stmt = queryPost($dbh,$sql,$data);
  
          if($stmt){
              $sesLimit = 60*60;
              $_SESSION['login_date'] = time();
              $_SESSION['login_limit'] = $sesLimit;
              //ユーザーIDを格納
              $_SESSION['user_id'] = $dbh ->LastInsertID();
              debug('セッション変数の中身：'.print_r($_SESSION,true));
              debug('バリデーションチェックOK　マイページへ移動');
              //バリデーションチェックOK　マイページへ移動
              header("Location:mypage.php");
          }
        }catch (Exception $e) {
          error_log('エラー発生:'.$e->getMessage());
          $err_msg['common']= MSG07;
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
  <title>ユーザー登録画面</title>
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
                    <li><a href="login.php">ログイン</a></li>
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
                      <div class="name-area">  
                        <p class="<?php if(!empty($err_msg['name'])) echo "err-msg"?>"><?php if(!empty($err_msg['name'])) echo $err_msg['name'];?></p>
                        <input type="text" name="name" value="<?php if(!empty($_POST)) echo $_POST['name'] ?>" placeholder="ニックネームを入力"  class="first-input <?php if(!empty($err_msg['name'])) echo "err-input"?>" style="<?php if(!empty($err_msg['name'])) echo "margin-top:0px;"?>">
                      </div> 
                      <div class="mail-area">  
                        <p class="<?php if(!empty($err_msg['email'])) echo "err-msg"?>"><?php if(!empty($err_msg['email'])) echo $err_msg['email'];?></p>
                        <input type="text" name="email" value="<?php if(!empty($_POST)) echo $_POST['email'] ?>" placeholder="email" class="<?php if(!empty($err_msg['email'])) echo "err-input"?>">
                      </div>
                      <div class="pass-area">
                        <p class="<?php if(!empty($err_msg['pass'])) echo "err-msg"?>"><?php if(!empty($err_msg['pass'])) echo $err_msg['pass'];?></p>
                        <input type="password" name="pass" value="<?php if(!empty($_POST)) echo $_POST['pass'] ?>" placeholder="パスワード(6文字以上)"class="<?php if(!empty($err_msg['pass'])) echo "err-input"?>" >
                      </div>
                      <div class="pass-area">
                        <input type="password" name="pass_re" value="<?php if(!empty($_POST)) echo $_POST['pass_re'] ?>" placeholder="パスワード(確認用)" class="<?php if(!empty($err_msg['pass'])) echo "err-input"?>">
                      </div>                        
                      <div class="login-button">
                        <input type="submit" value="登録する">
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