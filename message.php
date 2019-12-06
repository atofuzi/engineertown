<?php

//function関数を読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　メッセージ画面　」　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');

//ログ取り開始
debugLogStart();

//ログイン認証
require('auth.php');

//GETデータでメッセージをやりとりするユーザーIDを取得
//u_id（受け取る側）
$u_id =(!empty($_GET['u_id']))?  $_GET['u_id'] : '';

//my_id（送る側)
$my_id = $_SESSION['user_id'];

if(empty($u_id)){
    debug('不正に遷移した可能性あり');
    header("Location:profEdit.php");
}

$dbFormData = getProf($u_id);

if(empty($dbFormData) || $u_id === $my_id){
    debug('パラメータに不正値を入力した可能性あり');
    header("Location:profEdit.php");
}


$msg=array();

//メーセージデータをDBから取得
$board = getBoard($my_id,$u_id);

//メッセージ送受信が初めてかどうかの判定
if(!empty($board)){
    $board_id = $board['id'];
    debug('掲示板データ'.print_r($board,true));    
    //POST送信があった場合
    if(!empty($_POST['msg'])){

        //ログイン認証
        require('auth.php');

        $tmp_msg = $_POST['msg'];
        debug('ポスト送信がありました。');
        debug('ポストの中身'.print_r($_POST,true));
        
        //メッセージをデータベースへ格納後、最新のメッセージを含む情報を返り値としてmsgへ格納
        debug('新しいメッセージをDBへ格納します');

        $msg = msgRegist($board_id , $my_id , $u_id , $tmp_msg);
        
        debug('最新のメッセージ情報を取得しました');
        debug('最新のメッセージ情報'.print_r($msg,true));
        header('location:message.php?u_id='.$u_id);
    
    }else{
        $msg = getMsg($board_id , $my_id , $u_id);
        debug('過去のメッセージデータ'.print_r($msg,true));
    }

}else{
    //掲示板データがない場合
    debug('初めてのメッセージやりとりです');

    //メッセージ掲示板DBへユーザーIDを格納し掲示板を作成
    $board_id = createBoard($my_id,$u_id);
    debug('掲示板DBへ登録しました');
    debug('掲示板DBの登録内容'.$board_id.true);
}

?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
  <title>メッセージ画面</title>
  <link rel="stylesheet" type="text/css" href="style.css">
  <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
	<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
  </head>
<body>
    <header>
        <div class="site-width">
            <div id="header-icon" class="icon-red"></div>
            <div id="header-icon" class="icon-orange"></div>
            <div id="header-icon" class="icon-green"></div>
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
    
    <div class="site-width">
        <!--　バナー -->
        <a href="<?php echo 'profDetail.php?u_id='.$u_id; ?>">
            <section class="message-burner">
                <!-- メッセージ送信相手の画像-->
                <div class="msg-img-style" style="background-image: url(<?php if(!empty($dbFormData['pic'])){ echo sanitize($dbFormData['pic']);}else{ echo 'img/no_image.png';} ?>)"></div>
                <!-- メッセージ送信相手 -->
                <h2 class="message-title"><?php echo sanitize($dbFormData['name']); ?></h2>
            </section>
        </a>
        <section class="msg-board" id="js-scroll-bottom">

            <?php
            if(!empty($msg)){
                foreach($msg['data'] as $key => $value){
                    if($msg['data'][$key]['my_id'] !== $my_id){
             ?>
                <div class="msg-card-left-wrap">
                    <div class="msg-card-left">
                        <div class="left-img-area">
                            <div class="msg-img-style" style="background-image: url(<?php if(!empty($dbFormData['pic'])){ echo sanitize($dbFormData['pic']);}else{ echo 'img/no_image.png';} ?>)"></div>
                        </div>
                        <div class="left-msg-area">
                            <div class="left-msg">
                                <?php echo sanitize_br($msg['data'][$key]['msg']); ?>
                            </div>
                        </div>
                    </div>
                    <p><?php echo sanitize($msg['data'][$key]['create_date']); ?></p>
                </div>    
                
                   

            <?php
                }elseif($msg['data'][$key]['my_id'] === $my_id){
            ?>

                    <div class="msg-card-right">
                        <div class="right-msg-area">
                            <div class="right-msg">
                                <?php echo sanitize_br($msg['data'][$key]['msg']); ?>
                            </div>
                            <p style="text-align:right"><?php echo sanitize($msg['data'][$key]['create_date']); ?></p>
                        </div>
                    </div>

            <?php
                } 
            }
        }
             ?>

        </section>
        <form action="" method="post" enctype="multipart/form-data">
            <div class ="message-burner">
                <textarea class="textarea" name="msg" id="js-count" placeholder="メッセージを作成"></textarea>
                
                <label><button><i class="fas fa-paper-plane" style="font-size: 24px;"></i></button></label>
            </div>
        </form>
    </div>

    <?php require('footer.php'); ?>


</body>
</html>