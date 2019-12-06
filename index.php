<!DOCTYPE html>
<html lang="ja">

  <head>
    <meta charset="utf-8">
  <title>アウトプットリスト</title>
  <link rel="stylesheet" type="text/css" href="style.css">
  <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
	<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
  </head>
<body class="background">
    <div class="site-width">
        <main class="top-page-content">
            <h2 class="top-page-title" style="display: none;">Engineer Town</h2>
            <div  class="menu-list" style="display: none;">
                <a href="outputList.php"><div class="top-page-menu"><span>enter</span></div></a>
                <a href="login.php"><div class="top-page-menu"><span>login</span></div></a>
                <a href="signup.php"><div class="top-page-menu"><span>ユーザー登録</span></div></a>
            </div>  
        </main>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script>
    //トップページタイトル表示
    var $top_title = $('.top-page-title');
    var $top_menu = $('.menu-list');

    $.when(
	    $top_title.fadeIn(2000)
    ).done(function(){ 
        $top_menu.fadeIn(500);
    });
</script>

</html>