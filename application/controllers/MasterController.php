<?php

/**
* MasterController for class
*
* マスタ情報処理クラス
*
* @category   Master
* @package    Pado
* @author     Hitomi Aihara
* @author     
* @version    1.0
* @link       http://kikansub.pado.jp/master/
*/
class MasterController extends Zend_Controller_Action
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
	* システムマスタ情報検索処理
	* 
	* @param
	* @return 
	*/
	public function mtsystemAction()
	{
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlMstSystem.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlMstSystem = new SqlMstSystem();
		$arrErr = array();
		$arrPram = array();

		//パラメータ設定
		$kcd = $clsCommon->SetParam($this->getRequest(), "kcd");			//会社コード
		$grid = $clsCommon->SetParam($this->getRequest(), "grid");			//Gridデータ
		$serch = $clsCommon->SetParam($this->getRequest(), "serch");		//検索フラグ
		$token = $clsCommon->SetParam($this->getRequest(), "token");

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token, '', '', $clsComConst::PGID_MASTER_M_TSYSTEM))
		{ throw new Exception("", $clsComConst::ERR_CODE_403); }

		//セッション情報取得
		$session = $clsCommon->GetSession();

		//Gridデータデコード
		if(isset($grid)){ $grid = json_decode($grid); } else { $grid = array();}

		//検索ボタン押下時
		if($_POST['btnSerch'] || $serch == 1)
		{
			//アクセスログ出力処理
			$clsCommon->AccessLog($session, "serch");

			//パラメータチェック
			if(isset($kcd)) { $clsParamCheck->ChkNumeric($kcd, "会社コード"); }
			$arrErr = $clsParamCheck->GetErrMsg();

			if(count($arrErr) > 0)
			{
				foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
				$this->view->ErrMsg = $msg;
			}
			else
			{
				switch($session->roll)
				{
					case $clsComConst::ROLL_M_TSYSTEM_GKANRI:
						//経理・業務管理
						$pentry = $clsComConst::AUTH_M_TSYSTEM_GKANRI;
						break;
					case $clsComConst::ROLL_M_TSYSTEM_ADMIN:
						//管理者
						break;
					default:
						throw new Exception("権限が不正です。", $clsComConst::ERR_CODE_400);
						break;
				}

				//パラメータ生成
				$arrPram = array("kcd" => $kcd, "entry" => $pentry);

				//システムメッセージ情報検索
				$blnRet = $clsSqlMstSystem->SelectSystemMsg($clsComConst::DB_KIKAN_SUB , $arrPram);
				if($blnRet) { $arrRet = $clsSqlMstSystem->GetData(); }
			}

			//Gridデータ表示
			if(count($arrRet) > 0)
			{
				if(count($arrRet) == 1000) { $this->view->ErrMsg = $clsComConst::ERR_MSG_COUNT_OVER; }
			 	$this->view->arrdata = json_encode($arrRet);
			}
			else { $this->view->arrdata = json_encode(array()); }
		}
		else
		{
			$arrRet = array();
			$this->view->arrdata = json_encode($grid);
		}

		//設定処理
		if($session->roll == $clsComConst::ROLL_M_TSYSTEM_GKANRI){ $this->view->Visible = "style='visibility:hidden'"; }
		$this->view->master_system_detail_url = $clsCommon->ConverDisp($clsComConst::MASTER_M_TSYSTEM_DETAIL_URL);
		$this->view->TypeIns = $clsCommon->ConverDisp($clsComConst::CODE_INSERT);
		$this->view->TypeUpd = $clsCommon->ConverDisp($clsComConst::CODE_UPDATE);
		$this->view->KaisyaCd = $clsCommon->ConverDisp($kcd);
		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->List = $clsCommon->GetMenuList();
		$this->view->SubList = $clsCommon->GetSubMenuList();
	}

	/**
	* システムマスタ情報登録処理
	* 
	* @param
	* @return 
	*/
	public function mtsystemdetailAction()
	{
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlMstSystem.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlMstSystem = new SqlMstSystem();
		$arrErr = array();
		$arrPram = array();
		$name = "";

		//パラメータ設定
		$type = $clsCommon->SetParam($this->getRequest(), "type");				//会社コード(検索)
		$kcd = $clsCommon->SetParam($this->getRequest(), "kcd");				//会社コード(検索)
		$gkcd = $clsCommon->SetParam($this->getRequest(), "gkcd");				//会社コード
		$gentry = $clsCommon->SetParam($this->getRequest(), "gentry");			//エントリー
		$gsection = $clsCommon->SetParam($this->getRequest(), "gsection");		//セクション
		$gval = $clsCommon->SetParam($this->getRequest(), "gval");				//値
		$gbiko = $clsCommon->SetParam($this->getRequest(), "gbiko");			//備考
		$update = $clsCommon->SetParam($this->getRequest(), "update");			//更新フラグ
		$token = $clsCommon->SetParam($this->getRequest(), "token");

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token, '', $clsComConst::PGID_MASTER_M_TSYSTEM, $clsComConst::PGID_MASTER_M_TSYSTEM_DETAIL))
		{ throw new Exception("", $clsComConst::ERR_CODE_403); }

		//パラメータチェック
		if(!$clsParamCheck->ChkNumeric($type, "種別"))
		{ throw new Exception("種別が不正です。", $clsComConst::ERR_CODE_400); break; }

		switch($type)
		{
			case $clsComConst::CODE_INSERT:
				$this->view->UpdHidden = 'style="display:none "';
				$this->view->DelHidden = 'style="display:none "';
				break;
			case $clsComConst::CODE_UPDATE:
			case $clsComConst::CODE_DELETE:
				//パラメータチェック
				if(isset($gkcd)) { if(!$clsParamCheck->ChkNumeric($gkcd, "会社コード"))
				{ throw new Exception("会社コードが不正です。", $clsComConst::ERR_CODE_400); break; }}
				$this->view->Readonly = 'class="readonly"';
				$this->view->InsHidden = 'style="display:none"';
				break;
			default:
				throw new Exception("種別が不正です。", $clsComConst::ERR_CODE_400);
				break;
		}

		//セッション情報取得
		$session = $clsCommon->GetSession();

		switch($session->roll)
		{
			case $clsComConst::ROLL_M_TSYSTEM_GKANRI:
				//経理・業務管理
				$this->view->DelHidden = 'style="display:none "';
				break;
			case $clsComConst::ROLL_M_TSYSTEM_ADMIN:
				//管理者
				break;
			default:
				throw new Exception("権限が不正です。", $clsComConst::ERR_CODE_400);
				break;
		}

		//登録ボタン押下時
		if($update != "")
		{
			//パラメータチェック
			$clsParamCheck->ChkMust($gkcd, "会社コード");
			$clsParamCheck->ChkMust($gentry, "エントリー");
			$clsParamCheck->ChkMust($gsection, "セクション");
			if(isset($gkcd)) { $clsParamCheck->ChkNumeric($gkcd, "会社コード"); }
			$arrErr = $clsParamCheck->GetErrMsg();

			if(count($arrErr) > 0)
			{
				foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
				$this->view->ErrMsg = $msg;
			}
			else
			{
				//パラメータ生成
				$arrPram = array("kcd" => $gkcd, "scd" => $session->scd, "entry" => $gentry, "section" => $gsection, "value" => $gval, "biko" => $gbiko);

				switch($update)
				{
					case $clsComConst::CODE_INSERT:
						//システムマスタ情報検索
						$arrSelectPram = array("kcd" => $gkcd, "entry" => "'" . $gentry . "'", "section" => $gsection);
						$blnRet = $clsSqlMstSystem->SelectSystemMsg($clsComConst::DB_KIKAN_SUB , $arrSelectPram);
						if($blnRet) { $arrRet = $clsSqlMstSystem->GetData(); }
						if(count($arrRet) > 0) { $this->view->ErrMsg = $clsComConst::ERR_MSG_INSERT; $blnRet = false; }
						else
						{
							//システムマスタ情報登録
							$blnRet = $clsSqlMstSystem->InsertSystem($clsComConst::DB_KIKAN_SUB , $arrPram);
							if(!$blnRet) { throw new Exception("登録失敗しました。", $clsComConst::ERR_CODE_400); }
							$name = "Insert";
						}
						break;
					case $clsComConst::CODE_UPDATE:
						//システムマスタ情報更新
						$blnRet = $clsSqlMstSystem->UpdateSystem($clsComConst::DB_KIKAN_SUB , $arrPram);
						if(!$blnRet) { throw new Exception("更新失敗しました。", $clsComConst::ERR_CODE_400); }
						$name = "Update";
						break;
					case $clsComConst::CODE_DELETE:
						//システムマスタ情報削除
						$blnRet = $clsSqlMstSystem->DeleteSystem($clsComConst::DB_KIKAN_SUB , $arrPram);
						if(!$blnRet) { throw new Exception("削除失敗しました。", $clsComConst::ERR_CODE_400); }
						$name = "Delete";
						break;
					default:
						throw new Exception("種別が不正です。", $clsComConst::ERR_CODE_400);
						break;
				}

				if($blnRet)
				{
					//アクセスログ出力処理
					$clsCommon->AccessLog($session, $name);
					//システムマスタ情報検索画面へ遷移
					header("HTTP/1.1 301 Moved Permanently");
					header("Location:" . $clsComConst::MASTER_M_TSYSTEM_URL . "?kcd=" . $kcd . "&serch=1" . "&token=" . $token);
					exit();
				}
			}
		}

		//設定処理
		$this->view->master_system_detail_url = $clsCommon->ConverDisp($clsComConst::MASTER_M_TSYSTEM_DETAIL_URL);
		$this->view->master_system_url = $clsCommon->ConverDisp($clsComConst::MASTER_M_TSYSTEM_URL);
		$this->view->TypeIns = $clsCommon->ConverDisp($clsComConst::CODE_INSERT);
		$this->view->TypeUpd = $clsCommon->ConverDisp($clsComConst::CODE_UPDATE);
		$this->view->Type = $clsCommon->ConverDisp($type);
		$this->view->KaisyaCd = $clsCommon->ConverDisp($gkcd);
		$this->view->Entry = $clsCommon->ConverDisp($gentry);
		$this->view->Section = $clsCommon->ConverDisp($gsection);
		$this->view->Value = $clsCommon->ConverDisp($gval);
		$this->view->Biko = $clsCommon->ConverDisp($gbiko);
		$this->view->PKaisyaCd = $clsCommon->ConverDisp($kcd);
		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->List = $clsCommon->GetMenuList();
		$this->view->SubList = $clsCommon->GetSubMenuList();
	}
}

