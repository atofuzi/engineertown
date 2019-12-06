
<?php

//================================
// ログ
//================================
//ログを取るか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','test.log');


//function関数を読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　テスト　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('ポスト情報'.print_r($_POST,true));
debug('ファイル情報'.print_r($_FILES,true));

if(!empty($_POST)){
  $str=$_POST['msg'];
 
  $str = preg_replace('/&/', '&amp;', $str);
  $str = preg_replace('/</', '&lt;', $str);
  $str = preg_replace('/>/', '&gt;', $str);
  $str = preg_replace('/"/', '&quot;', $str);
  $str = preg_replace("/'/", '&#39;', $str);
  $str = preg_replace("/`/", '&#x60;', $str);
  $str = preg_replace("/\r?\n/", '<br />', $str);
  
}

?>

<!DOCTYPE html>
<html lang="ja">

  <head>
    <meta charset="utf-8">
  <title>テスト</title>
  <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
  </head>
<body>


<form action="" method="post">
<textarea name="msg"></textarea>
<button>送信</button>
</form>

<?php if(!empty($str)){ ?>
 <div><?php echo $str; ?></div>

<?php } ?>



</form>
<footer>
<!--jsコード-->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>

<script>
$(function(){
var $movie_input = $("#movie-input");
var $movie = $('.js-movie-area');

$movie_input.on('change' , function(e){
  var file = this.files[0],
  fileReader = new FileReader();

  fileReader.onload =  function(event){
    $movie.attr('src',event.target.result).show();

  };

  fileReader.readAsDataURL(file);
});

});

$('button').on('click',function(){
    $('.popup').addClass('show').fadeIn();
});
  
$('#close').on('click',function(){
    $('.popup').fadeOut();
});

$('#clear').on('click',function(){
  $('.js-clear').checked = false;
});

});



</script>


</footer>
</body>

</html>