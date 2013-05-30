<?php

/**
* CommondialogController for class
*
* 共通ダイアログ情報処理クラス
*
* @category   Commondialog
* @package    Pado
* @author     Hitomi Aihara
* @author     
* @version    1.0
* @link       http://kikansub.pado.jp/commondialog/
*/
class CommondialogController extends Zend_Controller_Action
{
	/**
	* エラーメッセージ
	*/
	public $ErrMsg = "";

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
	* コード情報検索ダイアログ
	* 
	* @param
	* @return 
	*/
    public function codeAction()
    {
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlMstJuchuShubetu.php";
		require_once dirname(__FILE__) . "/../models/SqlMstKeisaihan.php";
		require_once dirname(__FILE__) . "/../models/SqlMstSyainRoll.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlMstJShubetu = new SqlMstJuchuShubetu();
		$clsSqlMstKeisaihan = new SqlMstKeisaihan();
		$clsSqlMstSyainRoll = new SqlMstSyainRoll();
		$arrErr = array();
		$arrPram = array();

		//パラメータ設定
		$type = $clsCommon->SetParam($this->getRequest(), "type");		//種別
		$kcd = $clsCommon->SetParam($this->getRequest(), "kcd");		//会社コード
		$serch = $clsCommon->SetParam($this->getRequest(), "serch");	//検索項目
		$dcd = $clsCommon->SetParam($this->getRequest(), "dcd");		//大分類コード
		$ccd = $clsCommon->SetParam($this->getRequest(), "ccd");		//中分類コード
		$token = $clsCommon->SetParam($this->getRequest(), "token");

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token, $clsComConst::CODE_DIALOG)) { throw new Exception("", $clsComConst::ERR_CODE_403); }

		//セッション情報取得
		$session = $clsCommon->GetSession();

		//パラメータチェック
		$clsParamCheck->ChkMust($type, "種別");
		$clsParamCheck->ChkNumeric($type, "種別");
		$arrErr = $clsParamCheck->GetErrMsg();
		foreach($arrErr as $value) { $msg .= $value; }
		if(count($arrErr) > 0) { throw new Exception($msg, $clsComConst::ERR_CODE_400); }

		$clsParamCheck = new ParamCheck();
		$arrErr = array();
		if(isset($kcd)) { $clsParamCheck->ChkNumeric($kcd, "会社コード"); } else { $kcd = $session->kcd; }
		if(isset($serch)) { $clsParamCheck->ChkStr($serch, "検索項目"); }
		if(isset($dcd)) { $clsParamCheck->ChkNumeric($dcd, "大分類コード"); }
		if(isset($ccd)) { $clsParamCheck->ChkNumeric($ccd, "中分類コード"); }
		$arrErr = $clsParamCheck->GetErrMsg();

		if(count($arrErr) > 0)
		{
			foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
			$this->view->ErrMsg = $msg;
		}
		else
		{
			//パラメータ生成
			$arrPram = array("serch" => $serch, "dcd" => $dcd ,"ccd" => $ccd ,"kcd" => $kcd);

			//コードマスタ検索
			switch ($type)
			{
				case $clsComConst::CODE_DAIBUNRUI:
					//大分類情報検索
					$blnRet = $clsSqlMstJShubetu->SelectMstDaibunrui($clsComConst::DB_KIKAN , $arrPram);
					if($blnRet) { $arrRet = $clsSqlMstJShubetu->GetData(); }
					break;
				case $clsComConst::CODE_CHUBUNRUI:
					//中分類情報検索
					$blnRet = $clsSqlMstJShubetu->SelectMstChubunrui($clsComConst::DB_KIKAN , $arrPram);
					if($blnRet) { $arrRet = $clsSqlMstJShubetu->GetData(); }
					break;
				case $clsComConst::CODE_SHUBETU:
					//受注種別情報検索
					$blnRet = $clsSqlMstJShubetu->SelectMstJuchuShubetu($clsComConst::DB_KIKAN , $arrPram);
					if($blnRet) { $arrRet = $clsSqlMstJShubetu->GetData(); }
					break;
				case $clsComConst::CODE_KEISAIHAN:
					//掲載版情報検索
					$blnRet = $clsSqlMstKeisaihan->SelectMstKeisaihan($clsComConst::DB_KIKAN , $arrPram);
					if($blnRet) { $arrRet = $clsSqlMstKeisaihan->GetData(); }
					break;
				case $clsComConst::CODE_ROLE:
					//権限情報検索
					$arrPram = array("serch" => $serch, "entry" => "ROLE_NM" ,"kcd" => $kcd);
					$blnRet = $clsSqlMstSyainRoll->SelectMstSystemData($clsComConst::DB_KIKAN_SUB , $arrPram);
					if($blnRet) { $arrRet = $clsSqlMstSyainRoll->GetData(); }
					break;
			}
		}

		//Gridデータ表示
		if(count($arrRet) > 0)
		{
			if(count($arrRet) == 1000) { $this->view->ErrMsg = $clsComConst::ERR_MSG_COUNT_OVER; }
			$this->view->arrdata = json_encode($arrRet);
		}
		else { $this->view->arrdata = json_encode(array()); }

		//設定処理
		$this->view->Type = $clsCommon->ConverDisp($type);
		$this->view->DCode = $clsCommon->ConverDisp($dcd);
		$this->view->CCode = $clsCommon->ConverDisp($ccd);
		$this->view->SerchName = $clsCommon->ConverDisp($serch);
		$this->view->code_daibunrui = $clsComConst::CODE_DAIBUNRUI;
		$this->view->code_chubunrui = $clsComConst::CODE_CHUBUNRUI;
		$this->view->code_shubetu = $clsComConst::CODE_SHUBETU;
		$this->view->code_keisaihan = $clsComConst::CODE_KEISAIHAN;
		$this->view->code_role = $clsComConst::CODE_ROLE;
		$this->view->Token = $clsCommon->ConverDisp($token);
    }

	/**
	* 社員検索ダイアログ
	* 
	* @param
	* @return 
	*/
    public function employeeAction()
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
		$type = $clsCommon->SetParam($this->getRequest(), "type");		//種別
		$kcd = $clsCommon->SetParam($this->getRequest(), "kcd");		//会社コード
		$serch = $clsCommon->SetParam($this->getRequest(), "serch");	//検索項目
		$fhold = $clsCommon->SetParam($this->getRequest(), "fhold");	//営業拠点コード
		$post = $clsCommon->SetParam($this->getRequest(), "post");		//部署
		$token = $clsCommon->SetParam($this->getRequest(), "token");

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token, $clsComConst::CODE_DIALOG)) { throw new Exception("", $clsComConst::ERR_CODE_403); }

		//セッション情報取得
		$session = $clsCommon->GetSession();

		//パラメータチェック
		$clsParamCheck->ChkMust($type, "種別");
		$clsParamCheck->ChkNumeric($type, "種別");
		$arrErr = $clsParamCheck->GetErrMsg();
		foreach($arrErr as $value) { $msg .= $value; }
		if(count($arrErr) > 0) { throw new Exception($msg, $clsComConst::ERR_CODE_400); }

		$clsParamCheck = new ParamCheck();
		$arrErr = array();
		if(isset($kcd)) { $clsParamCheck->ChkNumeric($kcd, "会社コード"); } else { $kcd  = $session->kcd; }
		if(isset($serch)) { $clsParamCheck->ChkStr($serch, "検索項目"); }
		if(isset($fhold)) { $clsParamCheck->ChkNumeric($fhold, "営業拠点"); }
		if(isset($post)) { $clsParamCheck->ChkNumeric($post, "部署"); }
		$arrErr = $clsParamCheck->GetErrMsg();

		if(count($arrErr) > 0)
		{
			foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
			$this->view->ErrMsg = $msg;
		}

		//パラメータ生成
		if(isset($post)) { $fhold = null; }
		$arrPram = array("kcd" => $kcd, "serch" => $serch, "fhold" => $fhold, "post" => $post);

		//部署検索
		$blnRet = $clsSqlMstShain->SelectMstBusho($clsComConst::DB_KIKAN , $arrPram);

		if($blnRet)
		{
			$arrRet = $clsSqlMstShain->GetData();
			$this->view->busho = $clsCommon->ConverArrDropdownList("post", $arrRet, $post);
			if(isset($post)) { $fhold = $arrRet[0]["ecd"]; }
		}

		//営業拠点検索
		$clsSqlMstShain = new SqlMstShain();
		$blnRet = $clsSqlMstShain->SelectMstEigyoKyoten($clsComConst::DB_KIKAN , $arrPram);
		if($blnRet)
		{ 
			$arrRet = $clsSqlMstShain->GetData();
			$this->view->ekyoten = $clsCommon->ConverArrDropdownList("fhold", $arrRet, $fhold);
			$arrRet = array();
		}

		//検索ボタン押下時
		if($_POST['btnSerch'] && count($arrErr) == 0)
		{
			//社員情報検索
			$clsSqlMstShain = new SqlMstShain();
			$blnRet = $clsSqlMstShain->SelectMstShain($clsComConst::DB_KIKAN , $arrPram);
			if($blnRet) { $arrRet = $clsSqlMstShain->GetData(); }
		}

		//Gridデータ表示
		if(count($arrRet) > 0)
		{
			if(count($arrRet) == 1000) { $this->view->ErrMsg = $clsComConst::ERR_MSG_COUNT_OVER; }
			$this->view->arrdata = json_encode($arrRet);
		}
		else { $this->view->arrdata = json_encode(array()); }

		//設定処理
		$this->view->Type = $clsCommon->ConverDisp($type);
		$this->view->Kaisyacd = $clsCommon->ConverDisp($lcd);
		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->Seach = $clsCommon->ConverDisp($serch);
		$this->view->Fhold = $clsCommon->ConverDisp($fhold);
		$this->view->Post = $clsCommon->ConverDisp($post);
		$this->view->code_connect_employee = $clsComConst::CODE_CONNECT_EMPLOYEE;
		$this->view->code_claim_employee = $clsComConst::CODE_CLAIM_EMPLOYEE;
		$this->view->code_climent_employee = $clsComConst::CODE_CLIENT_EMPLOYEE;
		$this->view->code_sales_employee = $clsComConst::CODE_SALES_EMPLOYEE;
    }

	/**
	* 顧客検索ダイアログ
	* 
	* @param
	* @return 
	*/
    public function clientAction()
    {
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlMstTorihikisaki.php";
		require_once dirname(__FILE__) . "/../models/SqlMstShain.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlMstTorihikisaki = new SqlMstTorihikisaki();
		$clsSqlMstShain = new SqlMstShain();
		$arrErr = array();
		$arrPram = array();

		//パラメータ設定
		$type = $clsCommon->SetParam($this->getRequest(), "type");			//種別
		$cname = $clsCommon->SetParam($this->getRequest(), "cname");		//顧客名
		$ckana = $clsCommon->SetParam($this->getRequest(), "ckana");		//顧客名カナ
		$ecd = $clsCommon->SetParam($this->getRequest(), "ecd");			//営業担当者コード
		$ename = $clsCommon->SetParam($this->getRequest(), "ename");		//営業担当者名
		$tel = $clsCommon->SetParam($this->getRequest(), "tel");			//電話番号
		$address = $clsCommon->SetParam($this->getRequest(), "address");	//住所
		$conname = $clsCommon->SetParam($this->getRequest(), "conname");	//客先取引担当者名
		$claname = $clsCommon->SetParam($this->getRequest(), "claname");	//客先請求担当者名
		$token = $clsCommon->SetParam($this->getRequest(), "token");

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token, $clsComConst::CODE_DIALOG)) { throw new Exception("", $clsComConst::ERR_CODE_403); }

		//パラメータチェック
		$clsParamCheck->ChkMust($type, "種別");
		$clsParamCheck->ChkNumeric($type, "種別");
		$arrErr = $clsParamCheck->GetErrMsg();
		foreach($arrErr as $value) { $msg .= $value; }
		if(count($arrErr) > 0) { throw new Exception($msg, $clsComConst::ERR_CODE_400); }

		//セッション情報取得
		$session = $clsCommon->GetSession();

		//検索ボタン押下時
		if($_POST['btnSerch'])
		{
			$clsParamCheck = new ParamCheck();
			$arrErr = array();
			if(isset($cname)) { $clsParamCheck->ChkStr($cname, "顧客名"); }
			if(isset($ckana)) { $clsParamCheck->ChkStrKana($ckana, "顧客名カナ"); }
			if(isset($ecd)) { $clsParamCheck->ChkNumeric($ecd, "営業担当者コード"); }
			if(isset($tel)) { $clsParamCheck->ChkTel($tel, "電話番号"); }
			if(isset($address)) { $clsParamCheck->ChkStr($address, "住所"); }
			if(isset($conname)) { $clsParamCheck->ChkStr($conname, "客先取引担当者"); }
			if(isset($claname)) { $clsParamCheck->ChkStr($claname, "客先請求担当者"); }
			$arrErr = $clsParamCheck->GetErrMsg();

			if(count($arrErr) > 0)
			{
				foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
				$this->view->ErrMsg = $msg;
			}
			else
			{
				//パラメータ生成
				$arrPram = array("kcd" => $session->kcd, "cname" => $cname, "ckana" => $ckana ,"ecd" => $ecd , 
								 "tel" => $tel, "address" => $address, "conname" => $conname , "claname" => $claname);

				//顧客情報検索
				$blnRet = $clsSqlMstTorihikisaki->SelectMstTorihikisaki($clsComConst::DB_KIKAN , $arrPram);
				if($blnRet) { $arrRet = $clsSqlMstTorihikisaki->GetData(); }
			}
		}
		else
		{
			//パラメータチェック
			$clsParamCheck = new ParamCheck();
			$arrErr = array();
			if(isset($ecd)) { $clsParamCheck->ChkNumeric($ecd, "営業担当者コード"); }
			$arrErr = $clsParamCheck->GetErrMsg();

			if(count($arrErr) > 0)
			{
				foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
				$this->view->ErrMsg = $msg;
			}
			else
			{
				if(isset($ecd))
				{
					//営業担当者名取得
					$arrPram = array("kcd" => $session->kcd, "cd" => $ecd);
					$blnRet = $clsSqlMstShain->SelectMstShain($clsComConst::DB_KIKAN , $arrPram);
					if($blnRet) { $ename = $clsSqlMstShain->GetShainName(); }
					if(!isset($ename) || empty($ename)) { $ecd = ""; }
				}
				else { $ename = ""; }
			}
		}

		//Gridデータ表示
		if(count($arrRet) > 0)
		{
			if(count($arrRet) == 1000) { $this->view->ErrMsg = $clsComConst::ERR_MSG_COUNT_OVER; }
			$this->view->arrdata = json_encode($arrRet);
		}
		else { $this->view->arrdata = json_encode(array()); }

		//設定処理
		$this->view->Type = $clsCommon->ConverDisp($type);
		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->ClientName = $clsCommon->ConverDisp($cname);
		$this->view->ClientKana = $clsCommon->ConverDisp($ckana);
		$this->view->EmployeeCd = $clsCommon->ConverDisp($ecd);
		$this->view->EmployeeName = $clsCommon->ConverDisp($ename);
		$this->view->Tel = $clsCommon->ConverDisp($tel);
		$this->view->Address = $clsCommon->ConverDisp($address);
		$this->view->ConnectName = $clsCommon->ConverDisp($conname);
		$this->view->ClaimName = $clsCommon->ConverDisp($claname);
 		$this->view->code_contract = $clsComConst::CODE_CONTRACT;
		$this->view->code_claimant = $clsComConst::CODE_CLAIMANT;
		$this->view->code_advertiser = $clsComConst::CODE_ADVERTISER;
		$this->view->code_climent_employee = $clsComConst::CODE_CLIENT_EMPLOYEE;
		$this->view->cdialog_employee_url = $clsComConst::CDIALOG_EMPLOYEE_URL;
   }
}
