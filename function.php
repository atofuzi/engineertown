<?php
//================================
// ログ
//================================
//ログを取るか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');

//================================
// デバッグ
//================================
//デバッグフラグ
$debug_flg = true;
//デバックログ関数
function debug($str){
    global $debug_flg;
    if(!empty($debug_flg)){
        error_log('デバッグ：'.$str);
    }
}

function console_log( $data ){
    echo '<script>';
    echo 'console.log('. json_encode( $data ) .')';
    echo '</script>';
}

//================================
// セッション準備・セッション有効期限を延ばす
//================================
//セッションファイルの置き場所を変更する(/var/tmp/以下に置くと30日は削除されない)
session_save_path("/var/tmp/");
//ガーベージコレクションが削除するセッションの有効期限を設定(30日以上経っている物に対して100分の１の確率で削除)
ini_set('session.gc_maxlifetime', 60*60*24*30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime', 60*60*24*30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える(なりすましのセキュリティ対策)
session_regenerate_id();

//================================
// 画面表示処理開始ログ吐き出し関数
//================================
function debugLogStart(){
    debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
    debug('セッションID：'.session_id());
    debug('セッション変数の中身：'.print_r($_SESSION,true));
    debug('現在日時タイムスタンプ：'.time());
    if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
      debug( 'ログイン期限日時タイムスタンプ：'.( $_SESSION['login_date'] + $_SESSION['login_limit'] ) );
    }
  }

//================================
// 定数
//================================
//エラーメッセージを定数に設定
define('MSG01','※入力必須です');
define('MSG02','※emailの形式で入力してください');
define('MSG03','※すでに同じemailが登録されています');
define('MSG04','※6文字以上で入力してください');
define('MSG05','※255文字以下で入力してください');
define('MSG06','※パスワードが不一致です');
define('MSG07','※エラーが発生しました。しばらく経ってからやり直してください');
define('MSG08','※emailもしくはパスワードが違います');
define('MSG09','※半角英数字で入力してください');
define('MSG10','※255文字以内で入力してください');
define('MSG11','※認証キーが間違っています');
define('MSG12','※認証キーの有効期限が切れています');
define('MSG13','文字で入力してください');
define('SUC01', 'メールを送信しました');

//================================
// グローバル変数
//================================
//エラーメッセージ格納用の配列
$err_msg = array();



//================================
// ログイン認証
//================================
function isLogin(){
    // ログインしている場合
    if( !empty($_SESSION['login_date']) ){
      debug('ログイン済みユーザーです。');
  
      // 現在日時が最終ログイン日時＋有効期限を超えていた場合
      if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
        debug('ログイン有効期限オーバーです。');
  
        // セッションを削除（ログアウトする）
        session_destroy();
        return false;
      }else{
        debug('ログイン有効期限以内です。');
        return true;
      }
  
    }else{
      debug('未ログインユーザーです。');
      return false;
    }
  }

//================================
// データベース
//================================
//DB接続関数
function dbConnect(){
    //DBへの接続準備
    $dsn = 'mysql:dbname=engineertown;host=localhost;charset=utf8';
    $user = 'root';
    $password = 'root';
    $options = array(
      // SQL実行失敗時にはエラーコードのみ設定
      PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
      // デフォルトフェッチモードを連想配列形式に設定
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
      // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
      PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );
    // PDOオブジェクト生成（DBへ接続）
    $dbh = new PDO($dsn, $user, $password, $options);
    return $dbh;
  }

  //SQL実行関数
//function queryPost($dbh, $sql, $data){
//  //クエリー作成
//  $stmt = $dbh->prepare($sql);
//  //プレースホルダに値をセットし、SQL文を実行
//  $stmt->execute($data);
//  return $stmt;
//}
function queryPost($dbh,$sql,$data){
    //クエリー作成
    $stmt = $dbh->prepare($sql);
    //プレースホルダに値をセットし、SQL文を実行
    if(!$stmt->execute($data)){
        debug('クエリに失敗しました');
        debug('失敗したSQL:'.print_r($stmt,true));
        $err_msg['common'] = MSG07;
        return 0;
    }
        debug('クエリ成功');
        return $stmt;
}
//================================
// バリデーションチェック
//================================

//未入力チェック
function validRequired($str,$key){
    global $err_msg;
    if(empty($str)){
        $err_msg[$key] = MSG01;
    }
}
//emailの形式チェック
function validEmail($str,$key){
    global $err_msg;
    if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
        $err_msg[$key]=MSG02;
        debug( $err_msg[$key]);
    }
}
//emailの文字数上限チェック
function validEmailLen($str,$key){
    global $err_msg;
    if(mb_strlen($str) > 255){
        $err_msg[$key] = MSG05;
    }
}

//emailの重複チェク
function validEmailDup($str,$key){
    global $err_msg;

    try{
        $dbh = dbConnect();

        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email'=> $str);
        $stmt = queryPost($dbh,$sql,$data);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);


        if(!empty(array_shift($result))){
        $err_msg[$key]=MSG03;
        }

    } catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }
}


//パスワードの文字数チェック(6以上)
function validPassMin($str,$key){
    global $err_msg;
    if(mb_strlen($str) < 6){
        $err_msg[$key] = MSG04;
    }
}
function validPassMax($str,$key){
    global $err_msg;
    if(mb_strlen($str) > 255){
        $err_msg[$key] = MSG05;
    }
}
//パスワードのリマインダーチェク
function validMatch($str1,$str2,$key){
    global $err_msg;
    if($str1 !== $str2){
        $err_msg[$key] = MSG06;
    }
}

//パスワードの半角整数チェク
function validHalf($str,$key){
    global $err_msg;
    if(!preg_match("/^[a-zA-Z0-9]+$/",$str)){
        $err_msg[$key] = MSG09;
    }
}
//商品説明欄の文字数判定
function validExplanationMax($str,$key){
    global $err_msg;
    if(mb_strlen($str) > 255){
        $err_msg[$key] = MSG05;
    }
}

//ログインでメールとパスワードが一致するユーザーIDがあるかを確認する
function validLogin($str1,$str2,$key){
    global $err_msg;
    try{
    $dbh = dbConnect();
    $sql = 'SELECT id,password FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email'=> $str1); 

    $stmt = queryPost($dbh,$sql,$data);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    debug('検索結果'.print_r($result,true));

        if(!empty($result) && password_verify($str2,$result['password'])){
            debug('パスワードがマッチしました。');

            return $result['id'];

        }else{
            $err_msg[$key]= MSG08;
        }
    } catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }

}


//プロフィール自己紹介欄の文字数チェック
function validProfMax($str,$key){
    global $err_msg;
    if(mb_strlen($str) > 255){
        $err_msg[$key] = MSG10;
    }
}

//固定長チェック
function validLength($str, $key, $len = 8){
    if( mb_strlen($str) !== $len ){
      global $err_msg;
      $err_msg[$key] = $len . MSG13;
    }
  }


// 画像処理
function uploadImg($file, $key){
    debug('画像アップロード処理開始');
    debug('FILE情報：'.print_r($file,true));
    
    if (isset($file['error']) && is_int($file['error'])) {
      try {
        // バリデーション
        // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
        //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
        switch ($file['error']) {
            case UPLOAD_ERR_OK: // OK
                break;
            case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
                throw new RuntimeException('ファイルが選択されていません');
            case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過した場合
            case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
                throw new RuntimeException('ファイルサイズが大きすぎます');
            default: // その他の場合
                throw new RuntimeException('その他のエラーが発生しました');
        }
        
        // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
        // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
        $type = @exif_imagetype($file['tmp_name']);
        if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
            throw new RuntimeException('画像形式が未対応です');
        }
  
        // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
        // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
        // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
        // image_type_to_extension関数はファイルの拡張子を取得するもの
        $path = 'img/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
        if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
            throw new RuntimeException('ファイル保存時にエラーが発生しました');
        }
        // 保存したファイルパスのパーミッション（権限）を変更する
        chmod($path, 0644);
        
        debug('ファイルは正常にアップロードされました');
        debug('ファイルパス：'.$path);
        return $path;
  
      } catch (RuntimeException $e) {
  
        debug($e->getMessage());
        global $err_msg;
        $err_msg[$key] = $e->getMessage();
  
      }
    }
  }

function  uploadMovie($file,$key){
    debug('動画アップロード開始');

    if (isset($file['error']) && is_int($file['error'])) {
        try {
          // バリデーション
          // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
          //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
          switch ($file['error']) {
              case UPLOAD_ERR_OK: // OK
                  break;
              case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
                  throw new RuntimeException('ファイルが選択されていません');
              case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過した場合
              case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
                  throw new RuntimeException('ファイルサイズが大きすぎます');
              default: // その他の場合
                  throw new RuntimeException('その他のエラーが発生しました');
          }
          
          // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
          // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す

        //動画ファイルの指定
        $video_file = $file['tmp_name'];

        //MIMEタイプの取得
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($video_file);
        debug('動画のMINEタイプは'.$mime_type.です);

        $extension_array = array(
            'mp4' => 'video/mp4'
            );
          //MIMEタイプから拡張子を出力
          if($video_extension = array_search($mime_type, $extension_array,true)){
          //拡張子の出力
          debug('動画の拡張子は'.$video_extension);
          }else{
              throw new RuntimeException('動画形式が未対応です');
          }
    
          // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
          // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
          // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
          $path = 'video/'.sha1_file($file['tmp_name']).'.'.$video_extension;
          if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
              throw new RuntimeException('ファイル保存時にエラーが発生しました');
          }
          // 保存したファイルパスのパーミッション（権限）を変更する
          chmod($path, 0644);
          
          debug('ファイルは正常にアップロードされました');
          debug('ファイルパス：'.$path);
          return $path;
    
        } catch (RuntimeException $e) {
    
          debug($e->getMessage());
          global $err_msg;
          $err_msg[$key] = $e->getMessage();
    
        }
      }

}

/*=============================================
プロフィール変更画面
==============================================*/

//プロフィールを取ってくる
function getProf($id){
    try{
        $dbh = dbConnect();
        $sql = 'SELECT `name` , `profile` , pic ,html_flg , css_flg ,
                        js_jq_flg , sql_flg , java_flg , php_flg , php_oj_flg , 
                        php_fw_flg , ruby_flg , rails_flg , laravel_flg , 
                        swift_flg , scala_flg , go_flg , kotolin_flg ,
                         `year` , `month` , engineer_history , work_flg
                FROM users WHERE id = :id';

        $data = array(':id' => $id ); 

        $stmt = queryPost($dbh,$sql,$data);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!empty($result)){
        debug('ユーザー情報取得成功'); 

        return $result;
        }
        debug('ユーザー情報取得失敗'); 

    } catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG10;
    }
}


/*=============================================
アウトプット登録画面
==============================================*/
//アウトプット登録情報を取ってくる
function getOutPut($user_id,$op_id){
    try{
        $dbh = dbConnect();
        $sql = 'SELECT  op_name , explanation , pic_main ,
                        pic_sub1 , pic_sub2 , movie ,html_flg , css_flg ,
                        js_jq_flg , sql_flg , java_flg , php_flg , php_oj_flg , 
                        php_fw_flg , ruby_flg , rails_flg , laravel_flg , 
                        swift_flg , scala_flg , go_flg , kotolin_flg 
                FROM `output` WHERE id = :op_id AND `user_id` = :user_id';

        $data = array(':op_id' => $op_id , ':user_id' => $user_id ); 

        $stmt = queryPost($dbh,$sql,$data);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!empty($result)){
        debug('ユーザー情報取得成功'); 

        return $result;
        }
        debug('ユーザー情報取得失敗'); 

    } catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }
}

function outputRegist($user_id , $op_data , $pic_main , $pic_sub1 , $pic_sub2 , $movie){
    try{
        $dbh = dbConnect();
        $sql = 'INSERT INTO output ( `user_id` , op_name , explanation , pic_main ,
                                    pic_sub1 , pic_sub2 , movie ,html_flg , css_flg ,
                                    js_jq_flg , sql_flg , java_flg , php_flg , php_oj_flg , 
                                    php_fw_flg , ruby_flg , rails_flg , laravel_flg , 
                                    swift_flg , scala_flg , go_flg , kotolin_flg ,create_date)
                            VALUE ( :user_id , :op_name , :explanation , :pic_main ,
                                    :pic_sub1 , :pic_sub2 , :movie , :html_flg , :css_flg ,
                                    :js_jq_flg , :sql_flg , :java_flg , :php_flg , :php_oj_flg , 
                                    :php_fw_flg , :ruby_flg , :rails_flg , :laravel_flg , 
                                    :swift_flg , :scala_flg , :go_flg , :kotolin_flg , :create_date)';
        $data = array(
                    ':user_id' => $user_id , 
                    ':op_name' => $op_data['op_name'] ,
                    ':explanation' => $op_data['explanation'], 
                    ':pic_main' => $pic_main,
                    ':pic_sub1' => $pic_sub1,
                    ':pic_sub2' => $pic_sub2,
                    ':movie' => $movie,
                    ':html_flg' => $op_data['html_flg'], 
                    ':css_flg' => $op_data['css_flg'], 
                    ':js_jq_flg' => $op_data['js_jq_flg'], 
                    ':sql_flg' => $op_data['sql_flg'], 
                    ':java_flg' => $op_data['java_flg'], 
                    ':php_flg' => $op_data['php_flg'], 
                    ':php_oj_flg' => $op_data['php_oj_flg'], 
                    ':php_fw_flg' => $op_data['php_fw_flg'], 
                    ':ruby_flg' => $op_data['ruby_flg'], 
                    ':rails_flg' => $op_data['rails_flg'], 
                    ':laravel_flg' => $op_data['laravel_flg'], 
                    ':swift_flg' => $op_data['swift_flg'], 
                    ':scala_flg' => $op_data['scala_flg'], 
                    ':go_flg' => $op_data['go_flg'], 
                    ':kotolin_flg' => $op_data['kotolin_flg'], 
                    ':create_date' => date('Y-m-d H:i:s')
                     );

        $stmt = queryPost($dbh,$sql,$data);

    }catch (Exception $e) {

        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common']= MSG07;

    }

    try{
        $dbh = dbConnect();
        //登録直後のidを取得
        $sql = 'SELECT MAX(id) FROM `output` WHERE `user_id`= :user_id';
        $data = array(':user_id' => $user_id ); 
        $stmt = queryPost($dbh,$sql,$data);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!empty($result)){
        debug('登録直後のID情報取得成功'); 

        return $result;
        }
        debug('登録直後のID情報取得失敗'); 

    } catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }

}

/*=============================================
アウトプット詳細画面
==============================================*/

function getOutPutDetail($id){

    try{
        $dbh = dbConnect();
        $sql = 'SELECT  op.id , op.user_id , op.op_name , op.explanation , op.pic_main ,
                        op.pic_sub1 , op.pic_sub2 , op.movie ,op.html_flg , op.css_flg ,
                        op.js_jq_flg , op.sql_flg , op.java_flg , op.php_flg , op.php_oj_flg , 
                        op.php_fw_flg , op.ruby_flg , op.rails_flg , op.laravel_flg , 
                        op.swift_flg , op.scala_flg , op.go_flg , op.kotolin_flg ,
                        op.create_date , u.name AS name_user , u.pic AS pic_user
                FROM `output` AS op LEFT JOIN users AS u ON op.user_id = u.id WHERE op.id = :id AND op.delete_flg = :delete_flg';
        $data = array( ':id' => $id , ':delete_flg' => 0);

        $stmt = queryPost($dbh,$sql,$data);

        
        if($stmt){
            // クエリ結果のデータを全レコードを格納
            debug('プトプット詳細のデータ取得成功'); 
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        }else{
            debug('プトプット詳細のデータ取得失敗');
            return false;
        }

    }catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }
}

/*=============================================
掲示板画面
==============================================*/
function getListOutPut($search,$flg){

    //検索条件が指定されている場合
    if($flg==true){

        //$sql文に追記する回数をカウントするための変数
        $count = 0;

        try{
        $dbh = dbConnect();
        $sql = 'SELECT  op.id , op.user_id , op.op_name , op.explanation , op.pic_main ,
                        op.movie ,op.html_flg , op.css_flg ,
                        op.js_jq_flg , op.sql_flg , op.java_flg , op.php_flg , op.php_oj_flg , 
                        op.php_fw_flg , op.ruby_flg , op.rails_flg , op.laravel_flg , 
                        op.swift_flg , op.scala_flg , op.go_flg , op.kotolin_flg ,
                        op.create_date , u.pic AS pic_user
                FROM `output` AS op LEFT JOIN users AS u ON op.user_id = u.id WHERE ';

        //検索条件によってsqlを動的に書き換えるための処理
        foreach($search as $key => $value){
            //検索条件にキーワードが指定されている場合
            if($key=='keyword' && !empty($search[$key])){
                $sql .= 'op.op_name LIKE :op_name OR op.explanation LIKE :explanation ';
                $data[':op_name'] = '%'.$value.'%';
                $data[':explanation'] = '%'.$value.'%';
                $count++;
            
            //検索条件の言語にチェックがある場合
            }elseif(!empty($search[$key])){
                //sql文への追記が初回の場合はANDを付けない
                if($count === 0){
                    $sql .= 'op.'.$key.'= :'.$key.' ';
                    $data[':'.$key] = $value;
                    $count++;
                }
                $sql .= 'AND op.'.$key.'= :'.$key.' ';
                $data[':'.$key] = $value;
            }
        }
 
        //sqlの最後に必ず追記する
        $sql .= 'AND op.delete_flg = :delete_flg ORDER BY op.create_date DESC';

        $data[':delete_flg'] = 0;

        debug(print_r($data,true));

        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            // クエリ結果のデータを全レコードを格納
            $rst['data'] = $stmt->fetchAll();
            return $rst;
          }else{
            return false;
          }


        if(!empty($result)){
        debug('ユーザー情報取得成功'); 

        return $result;
        }
        debug('ユーザー情報取得失敗'); 

    } catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }

    //最初にページを開いた時・検索条件を全てクリアしてポスト送信された時に行う
    }else{
        try{
            $dbh = dbConnect();
            $sql = 'SELECT  op.id , op.user_id , op.op_name , op.explanation , op.pic_main ,
                            op.movie ,op.html_flg , op.css_flg ,
                            op.js_jq_flg , op.sql_flg , op.java_flg , op.php_flg , op.php_oj_flg , 
                            op.php_fw_flg , op.ruby_flg , op.rails_flg , op.laravel_flg , 
                            op.swift_flg , op.scala_flg , op.go_flg , op.kotolin_flg ,
                            op.create_date , u.pic AS pic_user
                    FROM `output` AS op LEFT JOIN users AS u ON op.user_id = u.id
                    WHERE op.delete_flg = :delete_flg
                    ORDER BY op.create_date DESC';

                $data = array(
                            ':delete_flg' => 0
                        ); 
    
            $stmt = queryPost($dbh,$sql,$data);
    
            if($stmt){
                // クエリ結果のデータを全レコードを格納
                debug('ユーザー情報取得成功'); 
                $rst['data'] = $stmt->fetchAll();
                return $rst;
              }else{
                return false;
              }
            debug('ユーザー情報取得失敗'); 
    
        } catch (Exception $e){
            error_log('エラー発生:'.$e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }

}


function getUserOutPut($search,$id,$flg){

    //検索条件が指定されている場合
    if($flg==true){

        //$sql文に追記する回数をカウントするための変数
        $count = 0;

        try{
        $dbh = dbConnect();
        $sql = 'SELECT  op.id , op.user_id , op.op_name , op.explanation , op.pic_main ,
                        op.movie ,op.html_flg , op.css_flg ,
                        op.js_jq_flg , op.sql_flg , op.java_flg , op.php_flg , op.php_oj_flg , 
                        op.php_fw_flg , op.ruby_flg , op.rails_flg , op.laravel_flg , 
                        op.swift_flg , op.scala_flg , op.go_flg , op.kotolin_flg ,
                        op.create_date , u.pic AS pic_user
                FROM `output` AS op LEFT JOIN users AS u ON op.user_id = u.id WHERE ';

        //検索条件によってsqlを動的に書き換えるための処理
        foreach($search as $key => $value){
            //検索条件にキーワードが指定されている場合
            if($key=='keyword' && !empty($search[$key])){
                $sql .= 'op.op_name LIKE :op_name OR op.explanation LIKE :explanation ';
                $data[':op_name'] = '%'.$value.'%';
                $data[':explanation'] = '%'.$value.'%';
                $count++;
            
            //検索条件の言語にチェックがある場合
            }elseif(!empty($search[$key])){
                //sql文への追記が初回の場合はANDを付けない
                if($count === 0){
                    $sql .= 'op.'.$key.'= :'.$key.' ';
                    $data[':'.$key] = $value;
                    $count++;
                }
                $sql .= 'AND op.'.$key.'= :'.$key.' ';
                $data[':'.$key] = $value;
            }
        }
 
        //sqlの最後に必ず追記する
        $sql .= 'AND op.delete_flg = :delete_flg AND op.user_id = :user_id ORDER BY op.create_date DESC';

        $data[':delete_flg'] = 0;
        $data[':user_id'] = $id;

        debug(print_r($data,true));

        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            // クエリ結果のデータを全レコードを格納
            $rst['data'] = $stmt->fetchAll();
            return $rst;
          }else{
            return false;
          }


        if(!empty($result)){
        debug('ユーザー情報取得成功'); 

        return $result;
        }
        debug('ユーザー情報取得失敗'); 

    } catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }

    //最初にページを開いた時・検索条件を全てクリアしてポスト送信された時に行う
    }else{
        try{
            $dbh = dbConnect();
            $sql = 'SELECT  op.id , op.user_id , op.op_name , op.explanation , op.pic_main ,
                            op.movie ,op.html_flg , op.css_flg ,
                            op.js_jq_flg , op.sql_flg , op.java_flg , op.php_flg , op.php_oj_flg , 
                            op.php_fw_flg , op.ruby_flg , op.rails_flg , op.laravel_flg , 
                            op.swift_flg , op.scala_flg , op.go_flg , op.kotolin_flg ,
                            op.create_date , u.pic AS pic_user
                    FROM `output` AS op LEFT JOIN users AS u ON op.user_id = u.id
                    WHERE op.delete_flg = :delete_flg AND op.user_id = :user_id
                    ORDER BY op.create_date DESC';

                $data = array(
                            ':delete_flg' => 0,
                            ':user_id' => $id
                        ); 
    
            $stmt = queryPost($dbh,$sql,$data);
    
            if($stmt){
                // クエリ結果のデータを全レコードを格納
                debug('ユーザー情報取得成功'); 
                $rst['data'] = $stmt->fetchAll();
                return $rst;
              }else{
                return false;
              }
            debug('ユーザー情報取得失敗'); 
    
        } catch (Exception $e){
            error_log('エラー発生:'.$e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }

}

//メッセージデータの取得
function getMsg($board_id , $my_id,$u_id){
    try{
        $dbh = dbConnect();
        $sql = 'SELECT my_id , partner_id , msg , create_date
                FROM `message` WHERE board_id = :board_id AND ( my_id = :my_id OR my_id = :u_id ) AND ( partner_id = :my_id OR partner_id = :u_id ) AND delete_flg = :delete_flg 
                ORDER BY create_date';

        $data = array(':board_id' => $board_id , ':my_id' => $my_id , ':u_id' => $u_id , ':delete_flg' => 0); 

        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            // クエリ結果のデータを全レコードを格納
            debug('メーセージデータ取得成功'); 
            $result['data'] = $stmt->fetchAll();
            return $result;
        }else{
            return false;
        }

    } catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }

}

function getBoard($my_id,$u_id){
    try{
        $dbh = dbConnect();
        $sql = 'SELECT id , my_id , partner_id
                FROM `board` WHERE ( my_id = :my_id OR my_id = :u_id ) AND ( partner_id = :my_id OR partner_id = :u_id ) AND delete_flg = :delete_flg';

        $data = array(':my_id' => $my_id , ':u_id' => $u_id , ':delete_flg' => 0); 

        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            // クエリ結果のデータを全レコードを格納
            debug('掲示板データ取得成功'); 
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        }else{
            return false;
        }

        debug('掲示板データ取得失敗'); 

    } catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }

}
//掲示板データを新規登録
function createBoard($my_id,$u_id){
    try{
        $dbh = dbConnect();
        $sql = 'INSERT INTO board (my_id,partner_id,create_date) VALUE (:my_id, :partner_id , :create_date)';
        $data = array(':my_id'=> $my_id , ':partner_id' => $u_id , ':create_date' => date('Y-m-d H:i:s'));
    
        $stmt = queryPost($dbh,$sql,$data);
  
          if($stmt){
            $result = getBoard($my_id,$u_id);
            return $result;
          }
        }catch (Exception $e) {
          error_log('エラー発生:'.$e->getMessage());
          $err_msg['common']= MSG07;
        }
}

//メッセージデータを登録
function msgRegist($board_id , $my_id , $u_id ,$msg){
    try{
        $dbh = dbConnect();
        $sql = 'INSERT INTO `message` (board_id , my_id , partner_id , msg , create_date) VALUE (:board_id , :my_id, :partner_id , :msg , :create_date)';
        $data = array(':board_id' => $board_id , ':my_id'=> $my_id , ':partner_id' => $u_id , ':msg' => $msg , ':create_date' => date('Y-m-d H:i:s'));
    
        $stmt = queryPost($dbh,$sql,$data);
  
          if($stmt){
            $result['data'] = getMsg($board_id , $my_id , $u_id);
            return $result;
          }
        }catch (Exception $e) {
          error_log('エラー発生:'.$e->getMessage());
          $err_msg['common']= MSG07;
        }
}


//お気に入りアウトプットを取得
function getLike($user_id,$op_id){
    try {
        // DBへ接続
        $dbh = dbConnect();
        // レコードがあるか検索
        // likeという単語はLIKE検索とうSQLの命令文で使われているため、そのままでは使えないため、｀（バッククウォート）で囲む
        $sql = 'SELECT * FROM likeoutput WHERE user_id = :u_id AND op_id = :op_id';
        $data = array(':u_id' => $user_id, ':op_id' => $op_id);
    
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $resultCount = $stmt->rowCount();
   
        return $resultCount;

      } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
      }
}

function countLike($op_id){
    try {
        // DBへ接続
        $dbh = dbConnect();
        // レコードがあるか検索
        $sql = 'SELECT COUNT(*) FROM likeoutput WHERE op_id = :op_id';
        $data = array(':op_id' => $op_id);
    
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        $allCount = $stmt->fetch(PDO::FETCH_ASSOC);

        debug('総レコード数'.print_r($allCount,true));
   
        return $allCount['COUNT(*)'];

      } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
      }
}


//フレンドユーザを取得
function getFriend($friend_id,$my_id){
    try {
        // DBへ接続
        $dbh = dbConnect();
        // レコードがあるか検索
        // likeという単語はLIKE検索とうSQLの命令文で使われているため、そのままでは使えないため、｀（バッククウォート）で囲む
        $sql = 'SELECT * FROM frienduser WHERE user_id = :user_id AND friend_id = :friend_id';
        $data = array(':user_id' => $my_id, ':friend_id' => $friend_id);
    
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
   
        return $result;

      } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
      }
}

//インフォメーションを取得
function info(){
    try {
        // DBへ接続
        $dbh = dbConnect();
        // レコードがあるか検索
        // 
        $sql = 'SELECT info,create_date FROM infomation WHERE delete_flg = :delete_flg ORDER BY create_date DESC';
        $data = array(':delete_flg' => 0);
    
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
            $result = $stmt->fetchAll();
            return $result;
        }

      } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
      }
}
function infoMsg($my_id){
    try{

        $result = array();
        
        debug('ステップ①：掲示板データ取得'); 
        //ユーザーidに関連する掲示板データを全て取得
        $dbh = dbConnect();
        $sql = 'SELECT id , my_id , partner_id
                FROM `board` WHERE ( my_id = :my_id OR partner_id = :my_id ) AND delete_flg = :delete_flg';

        $data = array(':my_id' => $my_id , ':delete_flg' => 0); 

        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            // クエリ結果のデータを全レコードを格納
            debug('ステップ①：掲示板データ取得成功');

            $board = $stmt->fetchAll();

            if(!empty($board)){

                debug('ステップ②：掲示板データを掲示板IDとパートナーIDに整理する');
                    //my_idとpartner_idのうち、自分のid($my_id)は不要のため消す。
                    foreach($board as $key => $value){
                        if($board[$key]['my_id'] === $my_id){
                            $board_data[$key]= array(
                                                    'id' => $board[$key]['id'] ,
                                                    'partner_id' => $board[$key]['partner_id']
                                                );                       
                        }
                        elseif($board[$key]['partner_id'] === $my_id){
                            $board_data[$key]= array(
                                                    'id' => $board[$key]['id'] ,
                                                    'partner_id' => $board[$key]['my_id']
                                                );          
                        }
                    }
                debug('掲示板IDとパートナーID'.print_r($board_data,true));
                   
    
                debug('ステップ③：メッセージデータを取得'); 
                debug('ステップ④：パートナー画像取得'); 
                //return用の変数を配列で定義

                $dbh = dbConnect();
                //board_idに関連するメッセージデータのうち、新しいメッセージ順に取得するSQL文
                $sql1 = 'SELECT board_id , msg , create_date
                        FROM `message` WHERE board_id = :board_id AND delete_flg = :delete_flg ORDER BY create_date DESC';

                //partner_idの名前とプロフィール画像を取得するSQL文(インフォメーションボックスで画像を表示するため)
                $sql2 = 'SELECT `id` , `name` , pic FROM `users` WHERE id = :user_id AND delete_flg = :delete_flg';

                //存在するboard_id分、SQL1、SQL2を展開する
                foreach($board_data as $key => $value){
                    $data1 = array(':board_id' => $board_data[$key]['id'] , ':delete_flg' => 0); 
                    $data2 = array(':user_id' => $board_data[$key]['partner_id'] , ':delete_flg' => 0);

                    $stmt1 = queryPost($dbh,$sql1,$data1);
                    $stmt2 = queryPost($dbh,$sql2,$data2);
    
                    if($stmt1){
                        debug($key.'番目：メーセージデータ取得成功'); 
                        //$keyで指定されたboard_idに関連するメッセージのうち一番最初（最新）のデータを取得
                        $msg = $stmt1->fetch(PDO::FETCH_ASSOC);

                        //掲示板画面に遷移しただけでメッセージのやり取りをしていない場合は処理を行わない
                        if(!empty($msg)){
                            $result[$key] = $msg;
                
                            if($stmt2){
                                debug($key.'番目：パートなー画像取得成功'); 
                                //パートナーの名前とプロフィール画像データをreturnの最後の列に追加する
                                $result[$key] = $result[$key] + $stmt2->fetch(PDO::FETCH_ASSOC);
                            }
                        }           
                    }
                }
            }
        return $result;
        }
    } catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }

}

//フレンドユーザを取得
function infoFriend($my_id){
    try {
        // DBへ接続
        $dbh = dbConnect();
        // レコードがあるか検索
        // likeという単語はLIKE検索とうSQLの命令文で使われているため、そのままでは使えないため、｀（バッククウォート）で囲む
        $sql = 'SELECT u.id , u.name , u.pic FROM frienduser AS f LEFT JOIN users AS u ON f.friend_id = u.id WHERE f.user_id = :user_id';
        $data = array(':user_id' => $my_id);
    
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
            $result = $stmt->fetchAll();
            return $result;
        }

      } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
      }
}
//サニタイズ

function sanitize($str){
    return htmlspecialchars($str,ENT_QUOTES);
  }

  //サニタイズ(改行のみ<br>へ変換)
function sanitize_br($str){
  
    $str = preg_replace('/&/', '&amp;', $str);
    $str = preg_replace('/</', '&lt;', $str);
    $str = preg_replace('/>/', '&gt;', $str);
    $str = preg_replace('/"/', '&quot;', $str);
    $str = preg_replace("/'/", '&#39;', $str);
    $str = preg_replace("/`/", '&#x60;', $str);
    $str = preg_replace("/\r?\n/", '<br />', $str);
    return $str;
    
    }

//フォーム入力保持
function getFormData($key){
    global $dbFormData;

    if(!empty($_POST[$key])){
        //バリデーションチェックでエラーがあった場合
        if(!empty($err_msg[$key])){
            return sanitize($_POST[$key]);

         //エラーメッセージなくDBにすでにデータが格納されている場合
        }elseif(empty($err_msg[$key]) && !empty($dbFormData[$key])){
           
            //入力データが変更されていた場合 (他のデータ項目でエラーがあった場合に表示される）
            if($dbFormData[$key]!==$_POST[$key]){

                return sanitize($_POST[$key]);

            }else{
            //入力データに変更がなかった場合
              
                return sanitize($dbFormData[$key]);
            }
        //エラーメッセージがなくDBにデータがない場合
        }else{
            return sanitize($_POST[$key]);
        }
    }else{
            return sanitize($dbFormData[$key]);
    }
}
//================================
// メール送信
//================================
function sendMail($from, $to, $subject, $comment){
    if(!empty($to) && !empty($subject) && !empty($comment)){
        //文字化けしないように設定（お決まりパターン）
        mb_language("Japanese"); //現在使っている言語を設定する
        mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング（機械が分かる言葉へ変換）するかを設定
        
        //メールを送信（送信結果はtrueかfalseで返ってくる）
        $result = mb_send_mail($to, $subject, $comment, "From: ".$from);
        //送信結果を判定
        if ($result) {
          debug('メールを送信しました。');
        } else {
          debug('【エラー発生】メールの送信に失敗しました。');
        }
    }
}

//認証キー生成
function makeRandKey($length = 8) {
    static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i = 0; $i < $length; ++$i) {
        $str .= $chars[mt_rand(0, 61)];
    }
    return $str;
}

//sessionを１回だけ取得できる
function getSessionFlash($key){
    if(!empty($_SESSION[$key])){
      $data = $_SESSION[$key];
      $_SESSION[$key] = '';
      return $data;
    }
  }


?>