<?php

//function関数を読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　プロフィール詳細画面　」　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');

//ログ取り開始
debugLogStart();

//ログイン認証
require('auth.php');

// GETデータを格納
if(!empty($_GET['u_id'])){
    //GETデータあればプロフィールを表示するユーザーIDを格納
    $u_id = $_GET['u_id'];
}else{
    //GETデータない場合は自分のIDを格納
    $u_id =  $_SESSION['user_id'];;
}
$my_id = $_SESSION['user_id'];

// DBからユーザーデータを取得
$dbUserData = (!empty($u_id)) ? getProf($u_id) : '';

if(!empty($_GET['u_id']) && empty($dbUserData)){
    debug('パラメータ改竄の可能性あり');
    header("Location:outputList.php");
}

  //変数の中身を確認
debug(print_r($dbUserData,true));

$search="";
$search_flg = false;
$skill=array();
$friend=array();


//プロフィールが自分じゃない場合
if($u_id !== $my_id){
    //フレンドユーザー登録を取得
    debug('フレンドユーザーかどうかの確認をします');
    $friend = getFriend($u_id,$my_id);
    debug('フレンドユーザー情報'.print_r($friend,true));

//プロフィールが自分の場合
}elseif($u_id === $my_id){
    //インフォメーションエリアのデータを取ってくる
    debug('インフォメーションの各データを取得します');
    //インフォメーションを取得
    debug('インフォメーションを取得します');
    $info = info();
    debug('インフォメーションデータ'.print_r($info,true));
    //インフォメーションのメッセージ表示情報を取得
    debug('メッセージデータを取得します');
    $info_msg = infoMsg($my_id);
    debug('メッセージデータ'.print_r($info_msg,true));

    //インフォメーションのフレンド情報を取得
    $info_friend = infoFriend($my_id);
    debug('フレンドリストデータ'.print_r($info_friend,true));
}



if(!empty($_POST)){
    foreach($_POST as $key => $value){
        if(!empty($value)){
            $search_flg = true;
        }
    }

        //ポストの中身を変数へ格納
        $search=$_POST;

        //変数の中身を確認
        debug(print_r($search,true));

        //データベースから検索条件に合致したアウトプットのリストを取ってくる

        $op_list = getUserOutPut($search,$u_id,$search_flg);

        debug(print_r($op_list,true));

}else{
    debug('検索条件なし');
    //データベースから検索条件に合致したアウトプットのリストを取ってくる
    $op_list = getUserOutPut($search,$u_id,$search_flg);

    debug(print_r($op_list,true));
}

if(!empty($op_list)){
    foreach($op_list['data'] as $key => $value){
    $skill[$key] = array(
                'HTML' => $op_list['data'][$key]['html_flg'] ,
                'CSS' => $op_list['data'][$key]['css_flg'] ,
                'javascript・jquery' => $op_list['data'][$key]['js_jq_flg'] ,
                'SQL' => $op_list['data'][$key]['sql_flg'] ,
                'JAVA' => $op_list['data'][$key]['java_flg'] ,
                'PHP' => $op_list['data'][$key]['php_flg'] ,
                'PHP(オブジェクト指向)' => $op_list['data'][$key]['php_oj_flg'] ,
                'PHP(フレームワーク)' => $op_list['data'][$key]['php_fw_flg'] ,
                'ruby' => $op_list['data'][$key]['ruby_flg'] ,
                'rails' => $op_list['data'][$key]['rails_flg'] ,
                'laravel' => $op_list['data'][$key]['laravel_flg'] ,
                'swift' => $op_list['data'][$key]['swift_flg'] ,
                'scala' => $op_list['data'][$key]['scala_flg'] ,
                'go' => $op_list['data'][$key]['go_flg'] ,
                'kotolin' => $op_list['data'][$key]['kotolin_flg'] ,
            );
    }
}

if(!empty($dbUserData)){
    $user_skill = array(
                'HTML' => $dbUserData['html_flg'] ,
                'CSS' => $dbUserData['css_flg'] ,
                'javascript・jquery' => $dbUserData['js_jq_flg'] ,
                'SQL' => $dbUserData['sql_flg'] ,
                'JAVA' => $dbUserData['java_flg'] ,
                'PHP' => $dbUserData['php_flg'] ,
                'PHP(オブジェクト指向)' => $dbUserData['php_oj_flg'] ,
                'PHP(フレームワーク)' => $dbUserData['php_fw_flg'] ,
                'ruby' => $dbUserData['ruby_flg'] ,
                'rails' => $dbUserData['rails_flg'] ,
                'laravel' => $dbUserData['laravel_flg'] ,
                'swift' => $dbUserData['swift_flg'] ,
                'scala' => $dbUserData['scala_flg'] ,
                'go' => $dbUserData['go_flg'] ,
                'kotolin' => $dbUserData['kotolin_flg'] ,
            );
    }

debug(print_r($skill,true));

//================================
// 画面処理
//================================

?>

<!DOCTYPE html>
<html lang="ja">

  <head>
    <meta charset="utf-8">
  <title>プロフィール詳細画面</title>
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
            <li><a href="logout.php">ログアウト</a></li>
        </ul>
    </nav>
    </div>
  </header>
  <div class="site-width">
    <section class="profEdit-burner">
        <h2 class="profEdit-title"><?php if(!empty($dbUserData['name'])) echo $dbUserData['name'];?></h2>
        <nav class="nav-prof">
            <?php
                if($u_id !== $my_id){
            ?>
            <form action="message.php" method="post">
                <a href="<?php echo 'message.php?u_id='.$u_id;?>"><div class="mail-icon"><i class="far fa-envelope"></i></div></a>
            </form>
            <div id="js-click-friend" class="friend-icon <?php if(!empty($friend)) echo 'active';?>" aria-hidden="true" data-friend="<?php echo $u_id;?>">
                <?php if(!empty($friend)){ echo 'フレンド中'; }else{   echo 'フレンド登録';} ?>
            </div>
            <?php
            }elseif($u_id === $my_id){
            ?>
             <a href="<?php echo 'profEdit.php'; ?>">
                <div class="prof-icon">プロフィール変更</div>
            </a>
            <?php
            }
            ?>
        </nav>
        <!--DBにプロフィール画像が登録されていたら画像を表示-->
        <div class="profEdit-img">
            <div class="img-style" style="opacity: 1;<?php if(!empty($dbUserData['pic'])){ echo "background-image: url(".$dbUserData['pic'].")"; }?>"></div>
        </div>
            <label class="<?php if(!empty($err_msg['pic'])) echo "err-msg"; ?>"><?php if(!empty($err_msg['pic']))echo $err_msg['pic'];?></label>
    </section>
    <section class="prof-area">
        <table>
            <tr>
                <th align="left">プロフィール</th>
                <td><?php if(!empty($dbUserData['profile'])) echo sanitize($dbUserData['profile']); ?></td>
            <tr>
            <tr>
                <th align="left">学習開始時期</th>
                <td><?php if(!empty($dbUserData['year'])) echo sanitize($dbUserData['year'].'年'.sanitize($dbUserData['month'].'月')); ?></td>
            <tr>
            <tr>
                <th align="left">プログラミング歴</th>
                <td><?php if(isset($dbUserData['engineer_history'])) echo sanitize($dbUserData['engineer_history'].'年'); ?></td>
            <tr>
            <tr>
                <th align="left">実務経験</th>
                <td><?php if($dbUserData['work_flg']){ echo 'あり'; }else{ echo 'なし';} ?></td>
            <tr>
            <tr>
                <th align="left">習得言語</th>
                <td>
                    <?php
                        foreach($user_skill as $lang => $value){
                            if($user_skill[$lang]==1){
                                 echo(sanitize($lang.' , '));
                            }
                        }
                    ?>  

                </td>
            <tr>
        </table>
    </section>

    <?php
        if($u_id === $my_id){
    ?>
    <!--インフォメーション-->

    <section id="info" class="info-area">
        <ul class="js-tab">
            <li>お知らせ</li>
            <li>メッセージ</li>
            <li>フレンド</li>
        </ul>
        <div class="js-contents">
            <div class="content">
            <?php if(!empty($info)){
                foreach($info as $key => $value){
            ?>
                <div><?php echo sanitize($info[$key]['create_date']); ?></div>
                <div><?php echo sanitize($info[$key]['info']); ?></div>
            <?php 
                }
            }else{
                echo 'お知らせはありません';
            }
             ?>
            </div>
            <div class="content" id="js-scroll-bottom">
            <?php foreach($info_msg as $key => $value){ ?>
                <a href="<?php echo 'message.php?u_id='.$info_msg[$key]['id']; ?>">
                    <div class="info-msg-wrap">
                        <div class="info-msg-img" style="background-image: url(<?php if(!empty($info_msg[$key]['pic'])){ echo sanitize($info_msg[$key]['pic']);}else{ echo 'img/no_image.png'; }?>"></div>
                        <div class="info-msg">
                            <h3>
                            <?php if(!empty($info_msg[$key]['name'])){
                                    echo sanitize($info_msg[$key]['name']);
                                }else{
                                    echo "no-name"; 
                                }
                            ?>
                            </h3>
                            <p>
                            <?php if(!empty($info_msg[$key]['msg'])){
                                    echo sanitize($info_msg[$key]['msg']);
                                }else{
                                    echo ""; 
                                }
                            ?>
                            </p>
                        </div>
                        <p>
                        <?php if(!empty($info_msg[$key]['create_date'])){
                                    echo sanitize($info_msg[$key]['create_date']);
                                }else{
                                    echo ""; 
                                }
                        ?>
                        </p>
                    </div>
                </a>
            <?php } ?>
            </div>
            <div class="content">
                    <div class="info-friend-list">
                    <?php foreach($info_friend as $key => $value){ ?>
                    <a href="<?php echo 'profDetail.php?u_id='.$info_friend[$key]['id']; ?>">
                        <div class="info-friend-wrap">
                            <div class="info-friend-img" style="background-image: url(<?php if(!empty($info_friend[$key]['pic'])){ echo sanitize($info_friend[$key]['pic']);}else{ echo 'img/no_image.png'; }?>)"></div>
                            <h3><?php if(!empty($info_friend[$key]['name'])){ echo sanitize($info_friend[$key]['name']);}else{ echo "no-name"; }?></h3>
                        </div>
                        </a>
                    <?php } ?>
                    </div>
            </div>
        </div>
    </section>
    <?php
        }
    ?>
    <h2><i class="fas fa-list-ul" style="margin-right:5px;"></i> OUTPUT　List</h2>
    
    <form action="" method="post" id="contents" enctype="multipart/form-data" class="wrap">
        <div class='keyword-search-area'>
            <input type="search" name="keyword" placeholder="キーワードを入力" class="keyword">
            <button class="search-button"><i class="fas fa-search"></i></button>
        </div>
        <div class="filtered-search">条件検索 <i class="fas fa-caret-down"  style="color: #FFF; margin-left: 5px;"></i></div>
            <div class="popup">
                <div class="filtered-search-panel">
                    <p>ー使用している言語ー</p>
                    <i class="fas fa-times icon-batu" id="close"></i>
                    <table class="filtered-search-table">
                    <tr>
                        <td>
                            <input type="hidden" name="html_flg" value="">
                            <label><input type="checkbox" name="html_flg" value=1 <?php if(!empty($search) && getFormData('html_flg')==1) echo "checked" ?> class="js-clear">HTML</label>
                        </td>
                        <td>
                            <input type="hidden" name="css_flg" value="">
                            <label><input type="checkbox" name="css_flg" value=1 <?php if(!empty($search) && getFormData('css_flg')==1) echo "checked" ?>>CSS</label>
                        </td>
                        <td>
                            <input type="hidden" name="js_jq_flg" value="">
                            <label><input type="checkbox" name="js_jq_flg" value=1 <?php if(!empty($search) && getFormData('js_jq_flg')==1) echo "checked" ?>>javascript・jquery</label>
                        </td>
                        <td>
                            <input type="hidden" name="sql_flg" value="">
                            <label><input type="checkbox" name="sql_flg" value=1 <?php if(!empty($search) && getFormData('sql_flg')==1) echo "checked" ?>>SQL</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="hidden" name="java_flg" value="">
                            <label><input type="checkbox" name="java_flg" value=1 <?php if(!empty($search) && getFormData('java_flg')==1) echo "checked" ?>>JAVA</label>
                        </td>
                        <td>
                            <input type="hidden" name="php_flg" value="">
                            <label><input type="checkbox" name="php_flg" value=1 <?php if(!empty($search) && getFormData('php_flg')==1) echo "checked" ?>>PHP</label>
                        </td>
                        <td>
                            <input type="hidden" name="php_oj_flg" value="">
                            <label><input type="checkbox" name="php_oj_flg" value=1 <?php if(!empty($search) && getFormData('php_oj_flg')==1) echo "checked" ?>>PHP（オブジェクト指向)</label>
                        </td>
                        <td>
                            <input type="hidden" name="php_fw_flg" value="">
                            <label><input type="checkbox" name="php_fw_flg" value=1 <?php if(!empty($search) && getFormData('php_fw_flg')==1) echo "checked" ?>>PHP（フレームワーク）</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="hidden" name="ruby_flg" value="">
                            <label><input type="checkbox" name="ruby_flg" value=1 <?php if(!empty($search) && getFormData('ruby_flg')==1) echo "checked" ?>>ruby</label>
                        </td>
                        <td>
                            <input type="hidden" name="rails_flg" value="">
                            <label><input type="checkbox" name="rails_flg" value=1 <?php if(!empty($search) && getFormData('rails_flg')==1) echo "checked" ?>>rails</label>
                        </td>
                        <td>
                            <input type="hidden" name="laravel_flg" value="">
                            <label><input type="checkbox" name="laravel_flg" value=1 <?php if(!empty($search) && getFormData('laravel_flg')==1) echo "checked" ?>>laravel</label>
                        </td>
                        <td>
                            <input type="hidden" name="swift_flg" value="">
                            <label><input type="checkbox" name="swift_flg" value=1 <?php if(!empty($search) && getFormData('swift_flg')==1) echo "checked" ?>>swift</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="hidden" name="scala_flg" value="">
                            <label><input type="checkbox" name="scala_flg" value=1 <?php if(!empty($search) && getFormData('scala_flg')==1) echo "checked" ?>>scala</label>
                        </td>
                        <td>
                            <input type="hidden" name="go_flg" value="">
                            <label><input type="checkbox" name="go_flg" value=1 <?php if(!empty($search) && getFormData('go_flg')==1) echo "checked" ?>>go</label>
                        </td>
                        <td>
                            <input type="hidden" name="kotolin_flg" value="">
                            <label><input type="checkbox" name="kotolin_flg" value=1 <?php if(!empty($search) && getFormData('kotolin_flg')==1) echo "checked" ?>>kotolin</label>
                        </td>
                    </tr>
                    </table>
                    <span id="clear">条件クリア</span>
                    <input type="submit" value="検索">
                </div>
            </div>
        </form>
    <section class="output-list">
    <?php if($u_id === $my_id){ ?>
        <div>
            <a href="outputRegist.php" class="list-input-area">
                <i class="fas fa-folder" style="font-size:24px;"></i><span>Listへ追加+</span>
            </a>
        </div>
    <?php } ?>
    <?php
      foreach($op_list['data'] as $key => $value):
        ?>
        <div class="panel-list">
            
                <?php
                    if($u_id !== $my_id){
                ?>
                <a href="<?php echo 'profDetail.php?u_id='.$u_id;?>">
                    <div class="user-img">
                        <img src="<?php echo sanitize($op_list['data'][$key]['pic_user']);?>">
                    </div>
                </a>
                <?php }else{ ?>
                <div class="menu-icon">
                    <i class="fas fa-bars js-menu-icon"></i>
                    <i class="fas fa-times js-menu-icon" style="display: none;"></i>
                    <nav style="display: none;">
                        <ul>
                            <li><a href="<?php echo 'outputRegist.php?op_id='.$op_list['data'][$key]['id'];?>">編集</a></li>
                            <li class="op-delete" aria-hidden="true" data-deleteid="<?php echo $op_list['data'][$key]['id'];?>">削除</li>
                        </ul>
                    </nav>
                </div>
                <?php } ?>
            
            <div class="wrap">
                    <a href="<?php if($u_id === $my_id){ echo 'outputRegist.php?op_id='.$op_list['data'][$key]['id'];}else{ echo 'outputRegist.php?op_id='.$op_list['data'][$key]['id'];} ?>" class="panel">
                        <p class="panel-title">作品名 : <?php  echo sanitize($op_list['data'][$key]['op_name']); ?></p>
                        <div class="panel-img">
                            <img src="<?php echo sanitize($op_list['data'][$key]['pic_main']);?>">
                        </div>
                        <div class="panel-comment">
                            <label>【アウトプット概要】
                                <p><?php echo sanitize($op_list['data'][$key]['explanation']);?></p>
                            </label>
                        </div>
                        <div class="panel-skills">
                            <label>【使用言語】
                            <p>
                                <?php
                                    foreach($skill[$key] as $lang => $value){
                                        if($skill[$key][$lang]==1){
                                            echo(sanitize($lang.' , '));
                                        }
                                    }
                                ?>
                            </p>
                            </label>
                        </div>
                    </a>
                <p class="post-date">投稿日： <?php echo sanitize($op_list['data'][$key]['create_date']);?></p>
                <div class="like-icon-area">
                    <i class="fa fa-heart icn-like js-click-like <?php if(!empty(getLike($_SESSION['user_id'] , $op_list['data'][$key]['id']))) echo 'active'; ?>" aria-hidden="true" data-output="<?php echo $op_list['data'][$key]['id'];?>">
                        <span class="js-like-count">
                        <?php if(!empty(countLike($op_list['data'][$key]['id']))){

                            $count = countLike($op_list['data'][$key]['id']);
                            echo $count;
                            }else{
                            echo "";
                            }
                        ?>
                    </span>
                    </i>
                </div>
            </div>
        </div>
        <?php
            endforeach;
        ?>
    </section>
</div>

<?php
 require('footer.php');
 ?>

</body>

</html>