<?php

/**
* LoginController for class
*
* ログイン情報処理クラス
*
* @category   Login
* @package    Pado
* @author     Hitomi Aihara
* @author     
* @version    1.0
* @link       http://kikansub.pado.jp/login/
*/
class LoginController extends Zend_Controller_Action
{
	/**
	* 初期処理
	* 
	* @param
	* @return 
	*/
	public function init()
	{
        /* Initialize action controller here */
	}

	/**
	* ログイン情報検索処理
	* 
	* @param
	* @return 
	*/
    public function indexAction()
    {
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlMstShain.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlMstShain = new SqlMstShain();
		$arrErr = array();
		$arrPram = array();

		//パラメータ設定
		$kcd = $clsCommon->SetParam($this->getRequest(), "kcd");				//会社コード
		$id = $clsCommon->SetParam($this->getRequest(), "id");					//ログインID
		$pass = $clsCommon->SetParam($this->getRequest(), "pass");				//パスワード
		$url = urldecode($clsCommon->SetParam($this->getRequest(), "url"));		//とび先URL
		$count = $clsCommon->SetParam($this->getRequest(), "count");			//カウンタ

		//ブラウザチェック
		if(!$clsCommon->ChkBrowser()) { throw new Exception("", $clsComConst::ERR_CODE_403); }

		//パラメータチェック
		if(isset($url) && !empty($url)) { $clsParamCheck->ChkUrl($url, "とび先URL"); }
		$arrErr = $clsParamCheck->GetErrMsg();
		foreach($arrErr as $value) { $msg .= $value; }
		if(count($arrErr) > 0) { throw new Exception($msg, $clsComConst::ERR_CODE_400); }

		//検索ボタン押下時
		if($_POST['btnLogin'])
		{
			//パラメータチェック
			$clsParamCheck = new ParamCheck();
			$arrErr = array();
			if($clsParamCheck->ChkMust($kcd, "会社コード")) { $clsParamCheck->ChkNumeric($kcd, "会社コード"); }
			if($clsParamCheck->ChkMust($id, "ログインID")) { $clsParamCheck->ChkNumeric($id, "ログインID"); }
			//if($clsParamCheck->ChkMust($pass, "パスワード")) { $clsParamCheck->ChkStr($pass, "パスワード"); }
			$clsParamCheck->ChkMust($pass, "パスワード");
			$arrErr = $clsParamCheck->GetErrMsg();

			if(count($arrErr) > 0)
			{
				foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
				$this->view->ErrMsg = $msg;
			}
			else
			{
				//パラメータ生成
				$arrPram = array("kcd" => $kcd, "id" => $id, "pass" => $pass);

				//ログイン情報検索
				$clsSqlMstShain = new SqlMstShain();
				$blnRet = $clsSqlMstShain->SelectMstSystemUsr($clsComConst::DB_KIKAN , $arrPram);
				if($blnRet) { $arrRet = $clsSqlMstShain->GetData(); }

				if(count($arrRet) > 0)
				{
					//パスワード有効期限チェック
					if($arrRet[0]["LAST_PWD_CHG_DATE"] <= $clsComConst::ERR_PWD_LIMIT_DAY)
					{
						//セッション情報設定
						Zend_Session::start();
						$session = new Zend_Session_Namespace('padouser');
						$session->kcd = $arrRet[0]["KAISHA_CD"];
						$session->scd = $arrRet[0]["SHAIN_CD"];
						$session->snm = $arrRet[0]["SHAIN_NM"];
						$session->token = md5(uniqid(mt_rand(),true));

						//有効期間設定
						$session->setExpirationSeconds($clsComConst::ERR_SESSION_LIMIT);

						//アクセスログ出力処理
						$clsCommon->AccessLog($session);

						if(isset($url) && !empty($url))
						{
							//指定ページへリダイレクト
							header("HTTP/1.1 301 Moved Permanently");
							header("Location:" . $url . "?token=$session->token");
							exit();
						}
						else
						{
							//TOPページへリダイレクト
							header("HTTP/1.1 301 Moved Permanently");
							header("Location:" . $clsComConst::HOME_URL . "?token=$session->token");
							exit();
						}
					}
					else
					{
						$count = $count  + 1;
						if($count >= 5){ throw new Exception("会社コード：" . $kcd . "ログインID" . $id, $clsComConst::ERR_CODE_403); }
						$this->view->ErrMsg = $clsComConst::ERR_MSG_PWD;
					}
				}
				else
				{
					$count = $count  + 1;
					if($count >= 5){ throw new Exception("会社コード：" . $kcd . ", ログインID:" . $id, $clsComConst::ERR_CODE_403); }
					$this->view->ErrMsg = $clsComConst::ERR_MSG_LOGIN;
				}
			}
		}

		//パラメータ表示
		$this->view->CompanyCd = $clsCommon->ConverDisp($kcd);
		$this->view->LoginId = $clsCommon->ConverDisp($id);
		$this->view->Password = $clsCommon->ConverDisp($pass);
		$this->view->Url = urlencode($clsCommon->ConverDisp($url));
		$this->view->Count = $clsCommon->ConverDisp($count);
    }
}

