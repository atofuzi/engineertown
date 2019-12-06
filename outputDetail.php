<?php

//function関数を読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　アウトプット詳細画面　」　');
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

// DBからユーザーデータと商品データを取得
$dbFormData = (!empty($op_id)) ? getOutPutDetail($op_id) : '';

$friend = getFriend($dbFormData['user_id'],$user_id);

$skill = array();

// パラメータ改ざんチェック
//================================
// GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい商品データが取れないのでマイページへ遷移させる

if(!empty($op_id) && empty($dbFormData)){
    debug('GETパラメータの商品IDが違います。マイページへ遷移します。');
    header("Location:profDetail.php"); //マイページへ
  }

debug('DB取得情報'.print_r($dbFormData,true));

if(!empty($dbFormData)){
    $skill = array(
                'HTML' => $dbFormData['html_flg'] ,
                'CSS' => $dbFormData['css_flg'] ,
                'javascript・jquery' => $dbFormData['js_jq_flg'] ,
                'SQL' => $dbFormData['sql_flg'] ,
                'JAVA' => $dbFormData['java_flg'] ,
                'PHP' => $dbFormData['php_flg'] ,
                'PHP(オブジェクト指向)' => $dbFormData['php_oj_flg'] ,
                'PHP(フレームワーク)' => $dbFormData['php_fw_flg'] ,
                'ruby' => $dbFormData['ruby_flg'] ,
                'rails' => $dbFormData['rails_flg'] ,
                'laravel' => $dbFormData['laravel_flg'] ,
                'swift' => $dbFormData['swift_flg'] ,
                'scala' => $dbFormData['scala_flg'] ,
                'go' => $dbFormData['go_flg'] ,
                'kotolin' => $dbFormData['kotolin_flg'] ,
            );
}
debug('使用言語情報'.print_r($skill,true));
?>



<!DOCTYPE html>
<html lang="ja">

  <head>
    <meta charset="utf-8">
  <title>アウトプット詳細画面</title>
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

    <div id="contents"class="site-width">
         <!--　エラーメッセージ -->
        <p class="<?php if(!empty($err_msg['common'])) echo "err-msg";?>"><?php if(!empty($err_msg['common'])) echo $err_msg['common'];?></p>
        <section class="profEdit-burner">
            <h2 class="profEdit-title"><?php if(!empty($dbFormData['name_user'])) echo $dbFormData['name_user'];?></h2>
            <nav class="nav-prof">
                <?php
                    if($user_id !== $dbFormData['user_id']){
                ?>
                <form action="message.php" method="post">
                    <a href="<?php echo 'message.php?u_id='.$dbFormData['user_id'];?>"><div class="mail-icon"><i class="far fa-envelope"></i></div></a>
                </form>
                <div id="js-click-friend" class="friend-icon <?php if(!empty($friend)) echo 'active';?>" aria-hidden="true" data-friend="<?php echo $dbFormData['user_id'];?>">
                    <?php if(!empty($friend)){ echo 'フレンド中'; }else{   echo 'フレンド登録';} ?>
                </div>
                <?php
                }elseif($user_id === $dbFormData['user_id']){
                ?>
                <a href="<?php echo 'profEdit.php'; ?>">
                    <div class="prof-icon">プロフィール変更</div>
                </a>
                <?php
                }
                ?>
            </nav>
            <!--DBにプロフィール画像が登録されていたら画像を表示-->
            <a href="<?php echo "profDetail.php?u_id=".$dbFormData['user_id'];?>">
                <div class="profEdit-img">
                    <div class="img-style" style="opacity: 1;<?php if(!empty($dbFormData['pic_user'])){ echo "background-image: url(".$dbFormData['pic_user'].")"; }?>"></div>
                </div>
            </a>
        </section>
       
    
        <section class="output-regist">
            <div class="output-title">
                <label>作品タイトル
                    <!--作品タイトル出力蘭-->
                    <div class="op_name"><?php if(!empty($dbFormData['op_name'])) echo sanitize($dbFormData['op_name']); ?></div>
                </label>
            </div>

            <div class="output-explanation">
                <label>説明
                    <!--作品説明入力蘭-->
                <div><?php if(!empty($dbFormData['explanation'])) echo sanitize_br($dbFormData['explanation']); ?></div>
                </label>
            </div>

            <div class="pic-area" style="overflow: hidden;"> 
                <!--メイン画像-->
                <div class="pic-main">
                    <p>【メイン画像】</p>
                    <div class="area-drop-main" style="<?php if(!empty($dbFormData['pic_main'])) echo 'background: #FFFFFF;'; ?>">
                    <img class="img-area js-switch-img-main" src="<?php if(!empty($dbFormData['pic_main'])){ echo sanitize($dbFormData['pic_main']); }?>">
                    </div>
                </div>
                <!--サブ画像-->
                
                <div class="pic-sub"><p>【サブ画像①】</p>
                    <div class="area-drop-sub" style="<?php if(!empty($dbFormData['pic_sub1'])) echo 'background: #FFFFFF;'; ?>">
                        no-image
                    <img class="img-area js-switch-img-sub" src="<?php if(!empty($dbFormData['pic_sub1'])){ echo sanitize($dbFormData['pic_sub1']); }?>">
                    </div>
                </div>
                    <!--サブ画像-->
                    <div class="pic-sub" ><p>【サブ画像②】</P>
                    <div class="area-drop-sub" style="<?php if(!empty($dbFormData['pic_sub2'])) echo 'background: #FFFFFF;'; ?>">
                        no-image
                        <img class="img-area js-switch-img-sub" src="<?php if(!empty($dbFormData['pic_sub2'])){ echo sanitize($dbFormData['pic_sub2']); }?>">
                    </div>
                </div>
            </div>

                <!--動画エリア-->
            <div class="movie-area" style="float: left;"> 
                <div class="movie">
                    <p>【動画】</p>
                    <div class="area-drop-main" style="<?php if(!empty($dbFormData['movie'])) echo 'background: #FFFFFF;'; ?>">
                        no-movie
                        <?php if(!empty($dbFormData['movie'])){ ?>
                        <video class="video-area" muted controls controlslist="nodownload" src="<?php if(!empty($dbFormData['movie'])){ echo sanitize($dbFormData['movie']); }?>"></video>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="skills-area" style="overflow: hidden;"> 
                <div class="op-skills">
                    <p>・使用言語</p>
                    <?php foreach($skill as $key => $value){
                            if($skill[$key] == 1){
                                echo "<p>".$key."</p>";
                            }
                    }
                    ?>
                </div>
            </div>
        </section> 
    </div>
    <?php
 require('footer.php');

 ?>


</body>
</html>