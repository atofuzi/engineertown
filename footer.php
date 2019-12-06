<footer id="footer">
        Copyright <a href="http://atofuzi.com/">atofuzi.life</a>. All Rights Reserved.
  </footer>
  <!--jsコード-->
  <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>


<script>
    //タブの切替機能
    $(function() {
         //マイページのタブ切替の処理
	    $('.js-tab li:nth-child(1)').addClass('current');
	    $('.js-tab li').click(function() {
		    var num = $(this).parent().children('li').index(this);
		    $('.js-tab li').removeClass('current').eq(num).addClass('current');
		
		    $(this).parent().next().children('.content').hide().eq(num).show();
	    }).first().click();

    //メニューの表示機能
    var $menu = $('.js-menu-icon');

    $menu.on('click',function(){
        var num =  $(this).parent().find('i').index(this);
        var $num =  $(this).parent().find('nav');
        $(this).parent().find('i').show().eq(num).hide();

        if($num.css('display') == 'none'){
            $num.show();
        }else{
            $num.hide();
        }
    });

    //文字カウンター

    var $count = $('#js-count');
    var $countView = $('.js-count-view');

    $count.on('keyup',function(e){
        var charLen = $(this).val().length;
        $countView.html(charLen);
    });

    //////////////////////
　　 //画像プレビュー処理
    //////////////////////

    var $fileInput = $('.js-file-input');
    //プロフィール画像表示
    var $profImg= $('.js-prof-img');

    //プロフィール画像のインプットエリアのエフェクト
    $fileInput.on('mouseover',function(e){
        $(this).parent().addClass('js-wrap-input-active').removeClass('js-wrap-input');
    });
    $fileInput.on('mouseout',function(e){
        $(this).parent().removeClass('js-wrap-input-active').addClass('js-wrap-input');
    });

    //アウトプット登録画像のドラック時のエフェクト
    $fileInput.on('dragover',function(e){
        $(this).parent().addClass('img-drag');
    });
    $fileInput.on('dragleave',function(e){
        $(this).parent().removeClass('img-drag');
    });

    //ファイルがインプットされた場合の処理
    $fileInput.on('change', function(e){
      var file = this.files[0],
          $filePrev = $(this).siblings('.js-file-prev')            // 2. files配列にファイルが入っています
          fileReader = new FileReader();   // 4. ファイルを読み込むFileReaderオブジェクト
     
      // 読み込みが完了した際のイベントハンドラ。imgのsrcにデータをセット
      fileReader.onload = function(event) {

        // 読み込んだデータをjs-prof_imgの背景に設定
        $profImg.css('background-image','url("'+event.target.result+'")');

        // アウトプット登録画面のimg/videoのsrcへurlを直接格納
        $filePrev.attr('src', event.target.result).show();
      };

         // 読み込んだ後の背景色を白にする
         $(this).parent('.js-area-drop').css('background','#FFFFFF');

        // 読み込んだ後に削除ボタンを表示する
        $(this).parent().find('.prev-close').show();

        //読み込んだ後、動画のみ表示順序を最上部にする
        $(this).next('video').css('z-index','3');

      // 6. 画像読み込み
      fileReader.readAsDataURL(file);
    });

    //×ボタンでプレビュー削除
    var $close = $('.prev-close');
    $close.on('click',function(){
 
            $(this).parent().find('.js-file-prev').attr('src',"").hide();
            $(this).parent().find('.js-file-input').val("");


    
            $(this).parent('.js-area-drop').css('background','rgb(235, 233, 233)');
            $(this).css('display','none');
  
    });

    // 画像切替
    var $switchImgSubs = $('.js-switch-img-sub'),
        $switchImgMain = $('.js-switch-img-main');

        $switchImgSubs.on('click',function(e){
        $switchImgMain.attr('src',$(this).attr('src'));
    });
    //////////////////////
    //条件検索画面表示機能
    //////////////////////

    var $filtered_search =$('.filtered-search');
    var $popup =  $('.popup');
    var $filtered_search_table =  $('.filtered-search-table');

    $filtered_search.on('click',function(){
        $popup.addClass('show').fadeIn();
    });

    $('#close').on('click',function(){
        $popup.fadeOut();
    });



    $('#clear').on('click',function(){
        $filtered_search_table.find('input').not(':hidden').prop("checked", false);
    });


    //テキストエリアの高さ自動調整機能
    var $textarea = $('.textarea');
    var lineHeight = parseInt($textarea.css('lineHeight'));
    $textarea.on('input', function(evt) {
    
    //入力文字中の改行の数を数える
    var lines = ($(this).val() + '\n').match(/\n/g).length;
    $(this).height(lineHeight * lines);
    });

   //scrollHeightは要素のスクロールビューの高さを取得するもの
    // $('#js-scroll-bottom').animate({scrollTop: $('#js-scroll-bottom')[0].scrollHeight}, 'fast');


    //お気に入り登録・削除
    var $like = $('.js-click-like') || null;
   
    $like.on('click',function(){
  
        var $this = $(this);
        var likeOutput = $this.data('output') || null;
            if(likeOutput !== undefined && likeOutput !== null){
                
                $.ajax({
                    type: "POST",
                    url: "ajaxLike.php",
                    data: { op_id : likeOutput}
                }).done(function(data){
                    if(data !==false){
                        $this.toggleClass('active');
                        $this.children('.js-like-count').text(data);
                        console.log('ajax ok');
                        
                    }else{
                        window.location.href = "login.php"; // 通常の遷移
                    }
                    }).fail(function(mdg){
                    console.log('ajax Error');
                    
                });
            }
    });

    //フレンド登録・削除
    var $friend = $('#js-click-friend') || null;
    var friendUser = $friend.data('friend') || null;

    if(friendUser !== undefined && friendUser !== null){
   
        $friend.on('click',function(){
            var $this = $(this);
                    $.ajax({
                        type: "POST",
                        url: "ajaxFriend.php",
                        data: { friend_id : friendUser}
                    }).done(function(data){
                        $this.toggleClass('active');
                        if(data){
                            $this.text('フレンド中');
                        }else{
                            $this.text('フレンド登録');
                        }
                        console.log('ajax ok');
                    }).fail(function(mdg){
                        console.log('ajax Error');
                    });           
            });
    }

    //アウトプット削除
    var $delete = $('.op-delete') || null;

    $delete.on('click',function(){
        var deleteId = $(this).data('deleteid') || null;
        if(deleteId !== undefined && deleteId !== null){
            var $this = $(this);
                    $.ajax({
                        type: "POST",
                        url: "ajaxDelete.php",
                        data: { delete_id : deleteId}
                    }).done(function(data){
                        console.log('ajax ok');
                        window.location.href = "profDetail.php"; // 通常の遷移
                       
                    }).fail(function(mdg){
                        console.log('ajax Error');
                    });
        }
    });
});

</script>

