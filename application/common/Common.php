<?php

/**
 * Common for class
 *
 * 共通クラス
 *
 * @category   Common
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class Common 
{
	/**
	 * ブラウザチェック
	 * 
	 * @param 
	 * @return true:成功、false:失敗
	 */
	public function ChkBrowser()
	{
		$blnChk = false;
		$ua = getenv("HTTP_USER_AGENT");

		switch (true)
		{
			case (preg_match('/MSIE/', $ua)):
				$blnChk = false;
				break;
			case (preg_match('/Firefox/', $ua)):
				$blnChk = true;
				break;
			case (preg_match('/Chrome/', $ua)):
				$blnChk = true;
				break;
			case (preg_match('/Safari/', $ua)):
				$blnChk = true;
				break;
		}

		return $blnChk;
	}

	/**
	 * 変換処理
	 * 
	 * @param $value  対象項目
	 * @return true:成功、false:失敗
	 */
	public function ConverDisp($value)
	{
		//return htmlentities($value, ENT_QUOTES);
		return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * パラメータ設定処理
	 * 
	 * @param $req   リクエスト情報
	 * @param $name  パラメータ名
	 * @return true:成功、false:失敗
	 */
	public function SetParam($req, $name)
	{
		$value = trim(mb_convert_kana($req->getParam($name), "s"));

		if(isset($value) && !empty($value)) { return $value; }
		else { $value = $req->getPost($name); }

		if(isset($value) && !empty($value)) { return $value; }

		return null;
	}

	/**
	 * ドロップダウンリスト生成処理
	 * 
	 * @param $inputName      名称
	 * @param $srcArray       リスト配列情報
	 * @param $selectedIndex  選択行
	 * @param $initFlg        初期フラグ(true:0データを先頭に追加する, false:0データを追加しない)
	 * @param $submitFlg      サブミットフラグ(true:サブミットコード仕込む, false:サブミットコード仕込まない)
	 * @param $cdFlg          コードフラグ(true:コード情報を表示する, false:コード情報を表示しない)
	 * @return true:成功、false:失敗
	 */
	public function ConverArrDropdownList($inputName, $srcArray, $selectedIndex, $initFlg = true, $submitFlg = true, $cdFlg = true)
	{
		if($submitFlg) { $temphtml = '<select name="'. htmlspecialchars($inputName). '" onchange="this.form.submit();" >'. "\n"; }
		else { $temphtml = '<select name="'. htmlspecialchars($inputName). '" >'. "\n"; }

		if($initFlg) { $temphtml .= '<option value="0"></option>'. "\n"; }

		foreach ($srcArray as $row)
		{
			if ($selectedIndex == $row["cd"]) { $selectedText = ' selected="selected"'; } else { $selectedText = ''; }
			if($cdFlg) { $temphtml .= '<option value="'. htmlspecialchars($row["cd"]). '"'. $selectedText. '>'. htmlspecialchars($row["cd"]) . ":" . htmlspecialchars($row["name"]). '</option>'. "\n"; }
			else { $temphtml .= '<option value="'. htmlspecialchars($row["cd"]). '"'. $selectedText. '>'. htmlspecialchars($row["name"]). '</option>'. "\n"; }
		}
		$temphtml .= '</select>'. "\n";

		return $temphtml;
	}

	/**
	 * マルチセレクトリスト生成処理
	 * 
	 * @param $inputName      名称
	 * @param $srcArray       リスト配列情報
	 * @param $selectedIndex  選択行
	 * @return true:成功、false:失敗
	 */
	public function ConverMultiSelectList($inputName, $srcArray, $selectedIndex)
	{
		$temphtml = '<select name="'. htmlspecialchars($inputName). '" multiple="multiple" onchange="ChangeSelect(); this.form.submit();">'. "\n";

		foreach ($srcArray as $row)
		{
			$selectedText = '';
			if(isset($selectedIndex)){ if(strstr($selectedIndex, $row["cd"])) { $selectedText = ' selected="selected"'; }}
			$temphtml .= '<option value="'. htmlspecialchars($row["cd"]). '"'. $selectedText. '>'. htmlspecialchars($row["cd"]) . ":" . htmlspecialchars($row["name"]). '</option>'. "\n";
		}
		$temphtml .= '</select>'. "\n";

		return $temphtml;
	}

	/**
	 * アクセスチェック処理
	 * 
	 * @param $token  認証キー
	 * @param $dflg   ダイアログフラグ(1:サブ画面)
	 * @return true:成功、false:失敗
	 */
	public function ChkAccess($token, $dflg=0)
	{
		$clsComConst = new ComConst();

		//ブラウザチェック
		if (!self::ChkBrowser()) { return false; }

		//URL設定
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")
		{ $url = "https://"; } else { $url = "http://"; }
		$url .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		if(strstr($url, "?")) { $url = substr($url, 0, $of=strpos($url,"?")); }

		//セッションチェック
		$session = new Zend_Session_Namespace('padouser');
		if((isset($session->scd) || $session->scd != null) &&
		   (isset($session->token) || $session->token != null) && $session->token == $token) { return true; }
		else
		{
			if($dflg == $clsComConst::CODE_DIALOG)
			{
				//サブ画面タイムアウト処理
				header("Content-Type: text/html; charset=UTF-8");
				echo "<script type=\"text/javascript\" >";
				echo "alert(\"認証が不正です。\");";
				echo "window.opener.location.reload();";
				echo "window.close();";
				echo "</script>";
				exit();
			}

			//ログインページへリダイレクト
			header("HTTP/1.1 301 Moved Permanently");
			header("Location:" . $clsComConst::LOGIN_URL . "?url=" . urlencode($url));
			exit();
		}

		return true;
	}

	/**
	 * セッション情報取得
	 * 
	 * @return true:成功、false:失敗
	 */
	public function GetSession()
	{
		$session = new Zend_Session_Namespace('padouser');
		return $session;
	}

	/**
	 * アクセスログ出力処理
	 * 
	 * @param $session  セッション情報
	 * @return true:成功、false:失敗
	 */
	public function AccessLog($session)
	{
		$clsComConst = new ComConst();

		try
		{
			$backtraces = debug_backtrace();
			$msg = $backtraces[1]['class'] . "：" . $backtraces[1]['function'] . "：" . "(" . $session->kcd . ") " . $session->scd . "：" . $session->snm;
			$logFile = $clsComConst::ACCESS_LOG_PATH. '/'. strtr('#DT#.log', array('#DT#'=>date('Ymd')));
			$logger = new Zend_Log(new Zend_Log_Writer_Stream($logFile));
			$logger->log($msg, Zend_Log::INFO);
		}
		catch(Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * CSV出力処理
	 * 
	 * @param $file     ファイル名
	 * @param $arrdata  出力配列データ
	 * @param $header   ヘッダー名
	 * @return true:成功、false:失敗
	 */
	public function SetCsv($file, $arrdata, $header="")
	{
		try
		{
			//CSVファイル名設定
			$csv_file = $file . ".csv";

			// CSVヘッダー情報設定
			$csv_data = $header . "\n";

			// CSVデータ生成
			foreach ($arrdata as $row)
			{
				foreach ( $row as $col => $val )
				{
					if(!is_numeric($col)) { continue; }

					$val = str_replace(';', '","', $val);
					$val = str_replace("(", "[", $val);
					$val = str_replace(")", "]", $val);
					$csv_data .= '"' . $val. '",';
				}

				$csv_data = substr($csv_data, 0, strlen($csv_data) - 1);
				$csv_data .= "\n";
			}

			// CSV出力
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=$csv_file");
			print(mb_convert_encoding($csv_data, 'SJIS'));
		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage(), $clsComConst::ERR_CODE_500);
		}

		return true;
	}
}

