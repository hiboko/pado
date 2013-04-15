$(function()
{
	/* ===================================================================
	* スムーススクロール
	=================================================================== */
	// #で始まるアンカーをクリックした場合に処理
	$('a[href^=#]').click(function(){
		// スクロールの速度
		var speed = 400;// ミリ秒
		// アンカーの値取得
		var href= $(this).attr("href");
		// 移動先を取得
		var target = $(href == "#" || href == "" ? 'html' : href);
		// 移動先を数値で取得
		var position = target.offset().top;
		// スムーススクロール
		$($.browser.safari ? 'body' : 'html').animate({scrollTop:position}, speed, 'swing');
		return false;
	});

	$("ul.menu2 li").hover(function(){
		$(">ul:not(:animated)",this).slideDown("fast")
	},
	function(){
		$(">ul",this).slideUp("fast");
	})

	/* ===================================================================
	 * JQuery UI
	=================================================================== */
	$( "#button" ).button();
	$( "#radioset" ).buttonset();
	$( "#dialog" ).dialog({
		autoOpen: false,
		width: 400,
		buttons: [
			{
				text: "Ok",
				click: function() { $( this ).dialog( "close" ); }
			},
			{
				text: "Cancel",
				click: function() { $( this ).dialog( "close" ); }
			}
		]
	});

	$( "#dialog-link" ).click(function( event ) {
		$( "#dialog" ).dialog( "open" );
		event.preventDefault();
	});

	$( "#progressbar" ).progressbar({ value: 20 });
	$( "#datepicker" ).datepicker({ inline: true });
	$( "#dialog-link, #icons li" ).hover(
		function() { $( this ).addClass( "ui-state-hover" ); },
		function() { $( this ).removeClass( "ui-state-hover" ); }
	);

	/* ===================================================================
	 * 検索ボタン押下時 画面ロック処理
	=================================================================== */
	$('#btnSerch').click(function(e) {
		$.blockUI({ message: '<h2>Please Wait...</h2>' });
		var url = './main.php?time=' + (new Date()).getTime();
		$.getJSON(url, function(e) { $.unblockUI(); });
	});
});

/* ===================================================================
 * 日付変換処理
=================================================================== */
function setFormatDate($obj)
{
	$obj.value = $obj.value.replace(/(^\s+)|(\s+$)/g, "");

	if($obj.value === "") { return; }

	if($obj.value.split("/").length != 3)
	{
		if($obj.value.split("/").length != 2)
		{
			if(isNaN($obj.value)){ alert("有効な整数ではありません。");  return false; }
			if($obj.value.length != 8){ alert("有効な桁数ではありません。");  return false; }
			else 
			{
				$obj.value = $obj.value.substr(0, 4) + "/" + $obj.value.substr(4, 2) + "/" + $obj.value.substr(6, 2);
			}
		}
		else
		{
			var today = new Date();
			$obj.value = today.getFullYear() + "/" + $obj.value;
		}
	}

	var date = new Date($obj.value);
	var toDoubleDigits = function(num)
	{
		num += "";
		if (num.length === 1) { num = "0" + num; }
		return num;
	};

	$obj.value = (date.getFullYear() + "/" + toDoubleDigits(date.getMonth() + 1) + "/" + toDoubleDigits(date.getDate()));
}
