<?php

//すでにログインをしてSESSION[id]にデータが格納されている場合
if(!empty($_SESSION['login_date'])){

    debug('ログイン済みユーザーです。');

    //現在日時が最終ログイン時間＋有効期限を超えていた場合
    if(($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
        debug('ログイン有効期限切れです');

        // セッション変数を全て削除
        $_SESSION = array();
        // セッションクッキーを削除
        if (isset($_COOKIE["PHPSESSID"])) {
            setcookie("PHPSESSID", '', time() - 1800, '/');
        }
        // セッションの登録データを削除
        session_destroy();

        //有効期限切れのためログインページへ遷移
        header("Location:login.php");

    }elseif(basename($_SERVER['PHP_SELF']) === 'login.php'){
            //もし仮にログイン状態でログイン画面に遷移してしまった場合
            header("Location:profDetail.php");
    }else{
    
        //最終ログイン時間を現在の時間に更新する(正常処理）
        $_SESSION['login_date'] = time();
    }

}elseif(!(basename($_SERVER['PHP_SELF']) === 'login.php')){

debug('未ログインユーザーです');
header("Location:login.php");
}


