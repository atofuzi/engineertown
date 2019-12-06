<?php

//function関数を読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　プロフィール編集画面　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');

//ログ取り開始
debugLogStart();

//================================
// 画面処理
//================================
//ログイン認証
require('auth.php');

//profileをDBから取得してくる

$dbFormData = getProf($_SESSION['user_id']);
$id = $_SESSION['user_id'];
debug('DBユーザー情報'.print_r($dbFormData,true));


if(!empty($_POST)){
  //バリデーションチェック
  if(!empty($_POST['profile'])){

  debug('プロフィール登録バリデーションチェック');
  validProfMax($_POST['profile'],'profile');
  }

  if(!empty($_FILES['pic']['name'])){
  //もしファイル情報に更新があった場合
  debug('画像処理バリデーションチェック');
  $pic = uploadImg($_FILES['pic'], 'pic');
  }

  if(empty($err_msg)){

  debug('プロフィール変更登録処理に入ります');
  //画像サイズ判定のために使ったMAX_FILE_SIZEを削除
  unset($_POST['MAX_FILE_SIZE']);

  debug('ポスト情報'.print_r($_POST,true));
  debug('ファイル情報'.print_r($_FILES,true));

  $before = $dbFormData;
  $after = $_POST;
  $new_data = 0;



  
  

  //画像ファイルの更新があった場合に変更数を+1する
  if(!empty($_FILES['pic']['name'])){
    $dbFormData['pic'] = $pic;
    $new_data++;
  }


  foreach ($after as $key => $value) {
    if(!($after[$key] == $before[$key])){
      $dbFormData[$key] = $after[$key];
      $new_data++;

    }
  }

  if(!empty($new_data)){
      debug('ユーザー情報に違いがあります');

      try{
          $dbh = dbConnect();
          $sql = 'UPDATE `users` 
                    SET `name` = :name , `profile` = :profile , pic = :pic , html_flg = :html_flg ,
                        css_flg = :css_flg , js_jq_flg = :js_jq_flg , sql_flg = :sql_flg ,
                        java_flg = :java_flg , php_flg = :php_flg , php_oj_flg = :php_oj_flg , 
                        php_fw_flg = :php_fw_flg , ruby_flg = :ruby_flg , rails_flg = :rails_flg ,
                        laravel_flg = :laravel_flg , swift_flg = :swift_flg , scala_flg = :scala_flg ,
                        go_flg = :go_flg , kotolin_flg = :kotolin_flg , `year` = :year ,
                        `month` = :month , engineer_history = :engineer_history ,work_flg = :work_flg
                  WHERE id = :id ';

          //更新するデータを$dataへ全て格納
          foreach($dbFormData as $key => $value){

          //$afterを配列名と配列データに分け、$data[配列名]へ配列データを全て格納する

          $data[':'.$key] = $value;

          }
          $data[':id'] = $id;

          debug('dataの中身は？'.print_r($data,true));

      debug('DBを更新します');
      /*$sql = 'UPDATE `users` SET `name` = :name WHERE users.id = :id';
      
      $sample = array('name' => '秘密', 'id' => $id);

      foreach($sample as $str1 => $str2){
      $data[':'.$str1] = $str2;
      }
      */

      //クエリ実行
      $stmt = queryPost($dbh,$sql,$data);

          header('Location:profDetail.php');
      }catch (Exception $e) {
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common']= MSG07;
      }

    }else{
      //ユーザー情報に変更がなければ、特に何も処理しない
      debug('ユーザー情報に変更なし');
    }
  }
}debug('処理終了');

?>



<!DOCTYPE html>
<html lang="ja">

  <head>
    <meta charset="utf-8">
  <title>プロフィール変更</title>
  <link rel="stylesheet" type="text/css" href="style.css">
  <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
	<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
  </head>
<body>
  <header>
    <div class="site-width">
    <div id="header-icon" class="icon-red">
    </div>
    <div id="header-icon" class="icon-orange">
    </div>
    <div id="header-icon" class="icon-green">
    </div>
    <h1><a href="outputList.php">Engineer Town</a></h1>
    
    <nav id="nav-top">
        <ul>
            <li><a href="outputList.php">掲示板</a></li>
            <li><a href="profDetail.php">マイページ</a></li>
            <li><a href="logout.php">ログアウト</a></li>
        </ul>
    </nav>
    </div>
  </header>
<body>
<form action="" method="post" id="contents" enctype="multipart/form-data" class="site-width">
            <section class="profEdit-burner">
        
                <h2 class="profEdit-title">プロフィール編集</h2>

                <!--プロフィール画像登録-->
                <!--DBにプロフィール画像が登録されていたら背景色を黒にする-->
                <div class="profEdit-img" style="<?php if(!empty($dbFormData['pic'])) echo "background: #000;"; ?>">
                <!--DBにプロフィール画像が登録されていたら画像を表示-->
                <div class="img-style js-prof-img" style="<?php if(!empty($dbFormData['pic'])){ echo "background-image: url(".$dbFormData['pic'].")"; }?>">
                    <!--カメラアイコン-->
                    <i class="fas fa-camera-retro icon-camera"></i>
                     <div class="js-wrap-input">
                        <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                        <input type="file" name="pic" class="input-area js-file-input">
                    </div>
                </div>
                </div>
                    <label class="<?php if(!empty($err_msg['pic'])) echo "err-msg"; ?>"><?php if(!empty($err_msg['pic']))echo $err_msg['pic'];?></label>
               
            </section>
            <section class="form-container">
                <div class="profEdit-form">
                    <!--　エラーメッセージ -->
                    <p class="<?php if(!empty($err_msg['common'])) echo "err-msg";?>"><?php if(!empty($err_msg['common'])) echo $err_msg['common'];?></p>

                    <label>ニックネーム<br>
                        <input type="text" name="name" class="name" value="<?php if(!empty($dbFormData['name'])) echo getFormData('name'); ?>"><br>
                    </label>
                    <div class="profile-area">
                    <label>自己紹介文<span class="<?php if(!empty($err_msg['profile'])) echo "err-msg"; ?>"><?php if(!empty($err_msg['profile'])) echo $err_msg['profile'];?></span>
                    <textarea name="profile" id="js-count"><?php if(!empty($dbFormData['profile'])) echo getFormData('profile'); ?></textarea>
                    </label>
                    <p class="counter-text"><span class="js-count-view"><?php if(!empty($dbFormData['profile'])){ echo mb_strlen(getFormData('profile')); }else{ echo 0; }?></span>/255文字</p>
                    </div>
                <section class="skills">
                        <p>・習得言語</p>
                        <input type="hidden" name="html_flg" value=0>
                        <label><input type="checkbox" name="html_flg" value=1 <?php if(getFormData('html_flg')) echo "checked" ?>>HTML</label><br>

                        <input type="hidden" name="css_flg" value=0>
                        <label><input type="checkbox" name="css_flg" value=1 <?php if(getFormData('css_flg')) echo "checked" ?>>CSS</label><br>

                        <input type="hidden" name="js_jq_flg" value=0>
                        <label><input type="checkbox" name="js_jq_flg" value=1 <?php if(getFormData('js_jq_flg')) echo "checked" ?>>javascript・jquery</label><br>

                        <input type="hidden" name="sql_flg" value=0>
                        <label><input type="checkbox" name="sql_flg" value=1 <?php if(getFormData('sql_flg')) echo "checked" ?>>SQL</label><br>

                        <input type="hidden" name="java_flg" value=0>
                        <label><input type="checkbox" name="java_flg" value=1 <?php if(getFormData('java_flg')) echo "checked" ?>>JAVA</label><br>

                        <input type="hidden" name="php_flg" value=0>
                        <label><input type="checkbox" name="php_flg" value=1 <?php if(getFormData('php_flg')) echo "checked" ?>>PHP</label><br>

                        <input type="hidden" name="php_oj_flg" value=0>
                        <label><input type="checkbox" name="php_oj_flg" value=1 <?php if(getFormData('php_oj_flg')) echo "checked" ?>>PHP（オブジェクト指向)</label><br>

                        <input type="hidden" name="php_fw_flg" value=0>
                        <label><input type="checkbox" name="php_fw_flg" value=1 <?php if(getFormData('php_fw_flg')) echo "checked" ?>>PHP（フレームワーク）</label><br>

                        <input type="hidden" name="ruby_flg" value=0>
                        <label><input type="checkbox" name="ruby_flg" value=1 <?php if(getFormData('ruby_flg')) echo "checked" ?>>ruby</label><br>

                        <input type="hidden" name="rails_flg" value=0>
                        <label><input type="checkbox" name="rails_flg" value=1 <?php if(getFormData('rails_flg')) echo "checked" ?>>rails</label><br>

                        <input type="hidden" name="laravel_flg" value=0>
                        <label><input type="checkbox" name="laravel_flg" value=1 <?php if(getFormData('laravel_flg')) echo "checked" ?>>laravel</label><br>

                        <input type="hidden" name="swift_flg" value=0>
                        <label><input type="checkbox" name="swift_flg" value=1 <?php if(getFormData('swift_flg')) echo "checked" ?>>swift</label><br>

                        <input type="hidden" name="scala_flg" value=0>
                        <label><input type="checkbox" name="scala_flg" value=1 <?php if(getFormData('scala_flg')) echo "checked" ?>>scala</label><br>

                        <input type="hidden" name="go_flg" value=0>
                        <label><input type="checkbox" name="go_flg" value=1 <?php if(getFormData('go_flg')) echo "checked" ?>>go</label><br>

                        <input type="hidden" name="kotolin_flg" value=0>
                        <label><input type="checkbox" name="kotolin_flg" value=1 <?php if(getFormData('kotolin_flg')) echo "checked" ?>>kotolin</label><br>
                </section>
                <section class="question">
                        <p>・学習開始時期　※初学者のみ</p>
                        <label><input type="text" name="year" value="<?php if(!empty($dbFormData['year'])) echo getFormData('year'); ?>" placeholder="2019">年</label>
                        <label><input type="text" name="month" value="<?php if(!empty($dbFormData['month'])) echo getFormData('month'); ?>" placeholder="1">月</label>
                        <p>・エンジニア歴</p>
                        <label><input type="text" name="engineer_history" value="<?php if(isset($dbFormData['engineer_history'])) echo getFormData('engineer_history'); ?>">年</label>
                        <p>・実務経験</p>
                        <input type="hidden" name="work_flg" value=2>
                        <label><input type="radio" name="work_flg" value=0 <?php if(getFormData('work_flg')==0) echo "checked" ?>>なし</label>
                        <label><input type="radio" name="work_flg" value=1 <?php if(getFormData('work_flg')==1) echo "checked" ?>>あり</label><br>
                </section>
                <input type="submit" value="変更する">
                </div>
            </section> 
      </form>
    <?php
 require('footer.php');

 ?>


</body>
</html>