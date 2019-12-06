<?php

//function関数を読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　アウトプット登録・変更画面　」　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');

//ログ取り開始
debugLogStart();

//================================
// 画面処理
//================================
//ログイン認証
require('auth.php');

// GETデータを格納
$op_id = (!empty($_GET['op_id'])) ? $_GET['op_id'] : '';
$user_id = $_SESSION['user_id'];
// DBから商品データを取得
$dbFormData = (!empty($op_id)) ? getOutPut($user_id, $op_id) : '';

// 新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? false : true;

// パラメータ改ざんチェック
//================================
// GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい商品データが取れないのでマイページへ遷移させる

if(!empty($op_id) && empty($dbFormData)){
    debug('GETパラメータの商品IDが違います。マイページへ遷移します。');
    header("Location:mypage.php"); //マイページへ
  }

debug('DB取得情報'.print_r($dbFormData,true));

//ポスト送信判定
if(!empty($_POST)){

    debug('ポスト情報'.print_r($_POST,true));
    debug('ファイル情報'.print_r($_FILES,true));

    //ファイルパス名格納変数
    $pic_main="";
    $pic_sub1="";
    $pic_sub2="";
    $movie="";


    //バリデーションチェック
    //作品名の未入力チェック
    debug('作品名の未入力チェックバリデーションチェック');
    validRequired($_POST['op_name'],'op_name');

    //商品説明蘭の説明チェック
    debug('アウトプット説欄バリデーションチェック');
    validExplanationMax($_POST['explanation'],'explanation');

    //画像のバリデーションチェック
    //新規登録画面の場合のみ画像の未入力チェックを行う
    if($edit_flg==false){
        debug('メイン画像が入力されているかのバリデーションチェック');
        validRequired($_FILES['pic_main']['name'],'pic_main');
    }
  
    debug('エラー内容'.print_r($err_msg,true));

    if(empty($err_msg)){

        debug('バリデーションチェック第一段階通過');

        //画像・動画のサイズ・ファイル型名の判定
        //もしメイン画像に更新があった場合
        if(!empty($_FILES['pic_main']['name'])){
            debug('メイン画像バリデーションチェック');
            $pic_main = uploadImg($_FILES['pic_main'], 'pic_main');
        }

        //もしサブ画像２に更新があった場合
        if(!empty($_FILES['pic_sub1']['name'])){
            debug('サブ画像１バリデーションチェック');
            $pic_sub1 = uploadImg($_FILES['pic_sub1'], 'pic_sub1');
        }

        //もしサブ画像２に更新があった場合
        if(!empty($_FILES['pic_sub2']['name'])){
            debug('サブ画像２バリデーションチェック');
            $pic_sub2 = uploadImg($_FILES['pic_sub2'], 'pic_sub2');
        }

        //ムービーのアップロード処理
        if(!empty($_FILES['movie']['name'])){
            $movie = uploadMovie($_FILES['movie'],'movie');
        }

    }

    if(empty($err_msg)){
        //新規登録画面か変更画面かの判定
        //変更画面の場合(edit_flgがtrue)
        if($edit_flg===true){

            //変更画面の場合は、UPDATEでデータベース内容を更新
            debug('アウトプット変更画面です');

            //画像サイズ判定のために使ったMAX_FILE_SIZEを削除
            unset($_POST['MAX_FILE_SIZE']);

            debug('ポスト情報'.print_r($_POST,true));
            debug('ファイル情報'.print_r($_FILES,true));

            $before = $dbFormData;
            $after = $_POST;
            $new_data = 0;


            //画像ファイルの更新があった場合に変更数を+1する
            if(!empty($_FILES['pic_main']['name'])){
                $dbFormData['pic_main'] = $pic_main;
                $new_data++;
            }
  
            if(!empty($_FILES['pic_sub1']['name'])){
                $dbFormData['pic_sub1'] = $pic_sub1;
                $new_data++;
            }

    
            if(!empty($_FILES['pic_sub2']['name'])){
                $dbFormData['pic_sub2'] = $pic_sub2;
                $new_data++;
            }
    
            if(!empty($_FILES['movie']['name'])){
                $dbFormData['movie'] = $movie;
                $new_data++;
            }

            foreach ($after as $key => $value) {
                if($after[$key] !== $before[$key]){
                      $dbFormData[$key] = $after[$key];
                      $new_data++;
                }
            }

            if($new_data != 0){
                  debug('アウトプット情報に違いがあります');

                      try{
                            $dbh = dbConnect();
                            $sql = 'UPDATE output 
                                              SET op_name = :op_name , explanation  = :explanation , pic_main = :pic_main , pic_sub1 = :pic_sub1 , 
                                                  pic_sub2 = :pic_sub2 , movie = :movie , html_flg = :html_flg ,
                                                  css_flg = :css_flg , js_jq_flg = :js_jq_flg , sql_flg = :sql_flg ,
                                                  java_flg = :java_flg , php_flg = :php_flg , php_oj_flg = :php_oj_flg , 
                                                  php_fw_flg = :php_fw_flg , ruby_flg = :ruby_flg , rails_flg = :rails_flg ,
                                                  laravel_flg = :laravel_flg , swift_flg = :swift_flg , scala_flg = :scala_flg ,
                                                  go_flg = :go_flg , kotolin_flg = :kotolin_flg 
                                            WHERE id = :id ';

                            //更新するデータを$dataへ全て格納
                            foreach($dbFormData as $key => $value){

                            //$afterを配列名と配列データに分け、$data[配列名]へ配列データを全て格納する
                            $data[':'.$key] = $value;
        
                            } 
                            $data[':id'] = $op_id;

                            debug('dataの中身は？'.print_r($data,true));

                            //クエリ実行
                            $stmt = queryPost($dbh,$sql,$data);

                            debug('DBを更新します');
                            debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
                            debug('「「　DBを更新完了。プロフィール詳細画面へ転移します。');
                            debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
                            header("Location:profDetail.php");
                        
                      }catch (Exception $e) {
                            error_log('エラー発生:'.$e->getMessage());
                            $err_msg['common']= MSG07;
                      }

            }else{
                //ユーザー情報に変更がなければ、特に何も処理しない
                debug('ユーザー情報に変更なし');
                header("Location:outputRegist.php?op_id=".$op_id);
            }


        }else{
            //新規登録画面
            debug('アウトプット新規登録画面です');
            debug('ポスト情報'.print_r($_POST,true));
            debug('ファイル情報'.print_r($_FILES,true));

            //画像サイズ判定のために使ったMAX_FILE_SIZEを削除
            unset($_POST['MAX_FILE_SIZE']);

            //INSERT　INTOでDBへデータを格納し登録直後のidを取得
            $op_id = outputRegist($user_id , $_POST , $pic_main , $pic_sub1 , $pic_sub2 , $movie);

            debug(print_r($op_id,true));

            //処理終了後は再度アウトプット登録画面へ戻る
            debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
            debug('「「　新規登録完了。アウトプット変更画面へ転移します。');
            debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');

            header("Location:profDetail.php");
          
        } 
    }
}

?>



<!DOCTYPE html>
<html lang="ja">

  <head>
    <meta charset="utf-8">
  <title>アウトプット登録画面</title>
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
            <li><a href="outputList.php".php">掲示板</a></li>
            <li><a href="profDetail.php">マイページ</a></li>
            <li><a href="logout.php">ログアウト</a></li>
        </ul>
    </nav>
    </div>
  </header>
    <form action="" method="post" id="contents" enctype="multipart/form-data" class="site-width">
        <h2 class="title">アウトプット登録</h2>
        <!--　エラーメッセージ -->
        <p class="<?php if(!empty($err_msg['common'])) echo "err-msg";?>"><?php if(!empty($err_msg['common'])) echo $err_msg['common'];?></p>

            <section class="output-regist">
                <div class="output-title">
                    <label>作品タイトル  <span style="color: #FF0000; padding-left: 10px;">※必須</span>
                        <!--エラーメッセージ表示エリア-->
                        <span class="<?php if(!empty($err_msg['op_name'])) echo "err-msg"; ?>"><?php if(!empty($err_msg['op_name'])) echo $err_msg['op_name'];?></span>
                        <!--作品タイトル入力蘭-->
                        <input type="text" name="op_name" class="op_name" value="<?php if(!empty($dbFormData['op_name'])) echo getFormData('op_name'); ?>"><br>
                    </label>
                </div>

                <div class="output-explanation">
                    <label>説明
                    <!--エラーメッセージ表示エリア-->
                    <span class="<?php if(!empty($err_msg['explanation'])) echo "err-msg"; ?>"><?php if(!empty($err_msg['explanation'])) echo $err_msg['explanation'];?></span>
                      <!--作品説明入力蘭-->
                    <textarea name="explanation" id="js-count"><?php if(!empty($dbFormData['explanation'])) echo getFormData('explanation'); ?></textarea>
                    </label>
                    <p class="counter-text"><span class="js-count-view"><?php if(!empty($dbFormData['explanation'])){ echo mb_strlen(getFormData('explanation')); }else{ echo 0; }?></span>/255文字</p>
                </div>

                <div class="pic-area" style="overflow: hidden;"> 

                    <!--メイン画像-->
                    <div class="pic-main">
                      <p>
                        【メイン画像】
                         <span style="color: #FF0000;">※必須</span>
                         <span class="<?php if(!empty($err_msg['pic_main'])) echo "err-msg"; ?>"><?php if(!empty($err_msg['pic_main'])) echo $err_msg['pic_main'];?></span>
                      </p>
                        <div class="area-drop-main js-area-drop">
                        <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                        <input type="file" name="pic_main" class="input-area js-file-input">
                        <img class="img-area js-file-prev" src="<?php if(!empty($dbFormData['pic_main'])){ echo $dbFormData['pic_main']; }?>">
                        ドラッグ&ドロップ
                        <i class="fas fa-times prev-close" style="display: none;"></i>
                        </div>
                    </div>
                    <!--サブ画像-->
                    
                    <div class="pic-sub"><p>【サブ画像①】</p>
                        <div class="area-drop-sub js-area-drop">
                        <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                        <input type="file" name="pic_sub1" class="input-area js-file-input" >
                        <img class="img-area js-file-prev" src="<?php if(!empty($dbFormData['pic_sub1'])){ echo $dbFormData['pic_sub1']; }?>">
                        ドラッグ&ドロップ
                        <i class="fas fa-times prev-close" style="display: none;"></i>
                        </div>
                    </div>
                      <!--サブ画像-->
                      <div class="pic-sub"><p>【サブ画像②】</P>
                        <div class="area-drop-sub js-area-drop">
                            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                            <input type="file" name="pic_sub2" class="input-area js-file-input">
                            <img class="img-area js-file-prev" src="<?php if(!empty($dbFormData['pic_sub2'])){ echo $dbFormData['pic_sub2']; }?>">
                            ドラッグ&ドロップ
                            <i class="fas fa-times prev-close" style="display: none;"></i>
                        </div>
                    </div>
                </div>

                    <!--動画エリア-->
                <div class="movie-area" style="float: left;"> 
                    <div class="movie">
                    <p>
                    【動画】(対応拡張子：mp4)
                    <!--エラーメッセージ表示エリア-->
                    <span class="<?php if(!empty($err_msg['movie'])) echo "err-msg"; ?>"><?php if(!empty($err_msg['movie'])) echo $err_msg['movie'];?></span>
                    </p>
                        <div class="area-drop-main js-area-drop">
                            <input type="hidden" name="MAX_FILE_SIZE" value="62428800">
                            <input type="file" name="movie" class="input-area js-file-input">
                            <video class="video-area js-file-prev" muted controls controlslist="nodownload" src="<?php if(!empty($dbFormData['movie'])){ echo $dbFormData['movie']; }?>" style="<?php if(empty($dbFormData['movie'])){ echo "display: none;"; }?>"></video>
                                ドラッグ&ドロップ
                                <i class="fas fa-times prev-close" style="display: none;"></i>
                        </div>
                    </div>
                </div>

                <div class="skills-area" style="overflow: hidden;"> 
                    <div class="op-skills">
                        <p>・使用言語</p>
                        <input type="hidden" name="html_flg" value=0>
                        <label><input type="checkbox" name="html_flg" value=1 <?php if(!empty($op_id) && getFormData('html_flg')==1) echo "checked" ?>>HTML</label><br>

                        <input type="hidden" name="css_flg" value=0>
                        <label><input type="checkbox" name="css_flg" value=1 <?php if(!empty($op_id) && getFormData('css_flg')==1) echo "checked" ?>>CSS</label><br>

                        <input type="hidden" name="js_jq_flg" value=0>
                        <label><input type="checkbox" name="js_jq_flg" value=1 <?php if(!empty($op_id) && getFormData('js_jq_flg')==1) echo "checked" ?>>javascript・jquery</label><br>

                        <input type="hidden" name="sql_flg" value=0>
                        <label><input type="checkbox" name="sql_flg" value=1 <?php if(!empty($op_id) && getFormData('sql_flg')==1) echo "checked" ?>>SQL</label><br>

                        <input type="hidden" name="java_flg" value=0>
                        <label><input type="checkbox" name="java_flg" value=1 <?php if(!empty($op_id) && getFormData('java_flg')==1) echo "checked" ?>>JAVA</label><br>

                        <input type="hidden" name="php_flg" value=0>
                        <label><input type="checkbox" name="php_flg" value=1 <?php if(!empty($op_id) && getFormData('php_flg')==1) echo "checked" ?>>PHP</label><br>

                        <input type="hidden" name="php_oj_flg" value=0>
                        <label><input type="checkbox" name="php_oj_flg" value=1 <?php if(!empty($op_id) && getFormData('php_oj_flg')==1) echo "checked" ?>>PHP（オブジェクト指向)</label><br>

                        <input type="hidden" name="php_fw_flg" value=0>
                        <label><input type="checkbox" name="php_fw_flg" value=1 <?php if(!empty($op_id) && getFormData('php_fw_flg')==1) echo "checked" ?>>PHP（フレームワーク）</label><br>

                        <input type="hidden" name="ruby_flg" value=0>
                        <label><input type="checkbox" name="ruby_flg" value=1 <?php if(!empty($op_id) && getFormData('ruby_flg')==1) echo "checked" ?>>ruby</label><br>

                        <input type="hidden" name="rails_flg" value=0>
                        <label><input type="checkbox" name="rails_flg" value=1 <?php if(!empty($op_id) && getFormData('rails_flg')==1) echo "checked" ?>>rails</label><br>

                        <input type="hidden" name="laravel_flg" value=0>
                        <label><input type="checkbox" name="laravel_flg" value=1 <?php if(!empty($op_id) && getFormData('laravel_flg')==1) echo "checked" ?>>laravel</label><br>

                        <input type="hidden" name="swift_flg" value=0>
                        <label><input type="checkbox" name="swift_flg" value=1 <?php if(!empty($op_id) && getFormData('swift_flg')==1) echo "checked" ?>>swift</label><br>

                        <input type="hidden" name="scala_flg" value=0>
                        <label><input type="checkbox" name="scala_flg" value=1 <?php if(!empty($op_id) && getFormData('scala_flg')==1) echo "checked" ?>>scala</label><br>

                        <input type="hidden" name="go_flg" value=0>
                        <label><input type="checkbox" name="go_flg" value=1 <?php if(!empty($op_id) && getFormData('go_flg')==1) echo "checked" ?>>go</label><br>

                        <input type="hidden" name="kotolin_flg" value=0>
                        <label><input type="checkbox" name="kotolin_flg" value=1 <?php if(!empty($op_id) && getFormData('kotolin_flg')==1) echo "checked" ?>>kotolin</label><br>
                    </div>
                  
                </div>
                <input type="submit" value="登録する">
            </section> 
    </form>
    <?php
 require('footer.php');

 ?>


</body>
</html>