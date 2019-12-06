<?php

//function関数を読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　アウトプットリスト　」　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');

//ログ取り開始
debugLogStart();


$search="";
$search_flg = false;
$skill=array();

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

        $op_list = getListOutPut($search,$search_flg);

        debug(print_r($op_list,true));

}else{
    debug('検索条件なし');
    //データベースから検索条件に合致したアウトプットのリストを取ってくる
    $op_list = getListOutPut($search,$search_flg);
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

debug(print_r($skill,true));

//================================
// 画面処理
//================================

?>

<!DOCTYPE html>
<html lang="ja">

  <head>
    <meta charset="utf-8">
  <title>アウトプットリスト</title>
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
            <?php 
            if(isLogin()){
            ?>
                <li><a href="profDetail.php">マイページ</a></li>
                <li><a href="logout.php">ログアウト</a></li>
             <?php
             }else{
             ?>
                <li><a href="login.php">ログイン</a></li>
                <li><a href="signup.php">ユーザー登録</a></li>
            <?php
            } 
            ?>
        </ul>
    </nav>
    </div>
  </header>
  <div class="site-width">
  <section>
  <form action="" method="post" id="contents" enctype="multipart/form-data" class="wrap">
    <div class='keyword-search-area'>
        <input type="text" name="keyword" placeholder="キーワードを入力" class="keyword">
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
  </section>
    <section class="output-list">
      <?php
      foreach($op_list['data'] as $key => $value){
        ?>
        <div class="panel-list">
            <a href="profDetail.php?u_id=<?php echo $op_list['data'][$key]['user_id']; ?>">
                <div class="user-img">
                    <img src="<?php echo sanitize($op_list['data'][$key]['pic_user']);?>">
                </div>
            </a>
            <div class="wrap">
                <a href="outputDetail.php?op_id=<?php echo sanitize($op_list['data'][$key]['id']); ?>" class="panel">
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
        }
        ?>
      
        
    </section>
</div>

<?php
 require('footer.php');
 ?>

</body>

</html>