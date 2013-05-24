<?php

/**
* PrintcsvController for class
*
* CSV出力クラス
*
* @category   Printcsv
* @package    Pado
* @author     Hitomi Aihara
* @author     
* @version    1.0
* @link       http://kikansub.pado.jp/printcsv/
*/
class PrintcsvController extends Zend_Controller_Action
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
	* 商売名人リスト検索
	* 
	* @param
	* @return 
	*/
    public function businessnamelistAction()
    {
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlBusinessNameList.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlBusinessNameList = new SqlBusinessNameList();
		$arrErr = array();
		$arrPram = array();

		//パラメータ設定
		$startdate = $clsCommon->SetParam($this->getRequest(), "startdate");		//納期(開始)
		$enddate = $clsCommon->SetParam($this->getRequest(), "enddate");			//納期(終了)
		$print = $clsCommon->SetParam($this->getRequest(), "print");				//出力フラグ
		$token = $clsCommon->SetParam($this->getRequest(), "token");

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token, '', '', $clsComConst::PGID_PRINTCSV_BUSINESSNAME_LIST))
		{ throw new Exception("", $clsComConst::ERR_CODE_403); }

		//CSV出力ボタン押下時
		if($print == 1)
		{
			//パラメータチェック
			$clsParamCheck->ChkMust($startdate, "納期(開始)");
			$clsParamCheck->ChkMust($enddate, "納期(終了)");
			if(isset($startdate)) { $clsParamCheck->ChkDate($startdate, "納期(開始)"); }
			if(isset($enddate)) { $clsParamCheck->ChkDate($enddate, "納期(終了)"); }
			$arrErr = $clsParamCheck->GetErrMsg();

			if(count($arrErr) > 0)
			{
				foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
				$this->view->ErrMsg = $msg;
			}
			else
			{
				//セッション情報取得
				$session = $clsCommon->GetSession();

				//パラメータ生成
				$arrPram = array("kcd" => $session->kcd, "startdate" => $startdate, "enddate" => $enddate);

				//商売名人リスト情報検索
				$blnRet = $clsSqlBusinessNameList->SelectBusinessNameList($clsComConst::DB_KIKAN , $arrPram);
				if($blnRet) { $arrRet = $clsSqlBusinessNameList->GetData(); }

				if(count($arrRet) > 0)
				{ 
					//CSV出力処理
					$clsCommon->SetCsv("商売名人リスト", $arrRet, "伝票番号, 明細番号, 掲載版, 対象月, 納期, サービス利用日(自), サービス利用日(至), 課金日(自), 課金日(至), 解約理由, 契約主コード, 契約主名, 店舗コード, 店舗名, ぱどナビ店舗ID, 大分類コード, 大分類名, 中分類コード, 中分類名, 種別コード, 種別名, 金額, 消費税, 原価, 状況, 拠点コード, 拠点名, 部署コード, 部署名, 社員コード, 社員名");
					exit;
				}
				else { $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
			}
		}

		//設定処理
		$this->view->StartDate = $clsCommon->ConverDisp($startdate);
		$this->view->EndDate = $clsCommon->ConverDisp($enddate);
		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->List = $clsCommon->GetMenuList();
		$this->view->SubList = $clsCommon->GetSubMenuList();
    }

	/**
	* 入金精算出力検索
	* 
	* @param
	* @return 
	*/
    public function receiveadjustedamountAction()
    {
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlReceiveAdjustedAmount.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlReceiveAdjustedAmount = new SqlReceiveAdjustedAmount();
		$arrErr = array();
		$arrPram = array();

		//パラメータ設定
		$sstartdate = $clsCommon->SetParam($this->getRequest(), "sstartdate");		//請求残開始日
		$nstartdate = $clsCommon->SetParam($this->getRequest(), "nstartdate");		//入金残開始日
		$print = $clsCommon->SetParam($this->getRequest(), "print");				//出力フラグ
		$token = $clsCommon->SetParam($this->getRequest(), "token");

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token, '', '', $clsComConst::PGID_PRINTCSV_RECIVE_ADJUSTEDAMOUNT))
		{ throw new Exception("", $clsComConst::ERR_CODE_403); }

		//CSV出力ボタン押下時
		if($print == 1)
		{
			//パラメータチェック
			$clsParamCheck->ChkMust($sstartdate, "請求残開始日");
			$clsParamCheck->ChkMust($nstartdate, "入金残開始日");
			if(isset($sstartdate)) { $clsParamCheck->ChkDate($sstartdate, "請求残開始日"); }
			if(isset($nstartdate)) { $clsParamCheck->ChkDate($nstartdate, "入金残開始日"); }
			$arrErr = $clsParamCheck->GetErrMsg();

			if(count($arrErr) > 0)
			{
				foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
				$this->view->ErrMsg = $msg;
			}
			else
			{
				//セッション情報取得
				$session = $clsCommon->GetSession();

				//パラメータ生成
				$arrPram = array("kcd" => $session->kcd, "sstartdate" => $sstartdate, "nstartdate" => $nstartdate);

				//入金精算出力情報検索
				$blnRet = $clsSqlReceiveAdjustedAmount->SelectReceiveAdjustedAmount($clsComConst::DB_KIKAN , $arrPram);
				if($blnRet) { $arrRet = $clsSqlReceiveAdjustedAmount->GetData(); }

				if(count($arrRet) > 0)
				{ 
					//CSV出力処理
					$clsCommon->SetCsv("入金精算出力", $arrRet, "会社コード, 請求先コード, 取引先名, 取引先カナ, 請求残額, 入金残額");
					exit;
				}
				else { $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
			}
		}

		//設定処理
		$this->view->SStartDate = $clsCommon->ConverDisp($sstartdate);
		$this->view->NStartDate = $clsCommon->ConverDisp($nstartdate);
		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->List = $clsCommon->GetMenuList();
		$this->view->SubList = $clsCommon->GetSubMenuList();
    }

	/**
	* カスタマーセンター問合せ集計出力検索
	* 
	* @param
	* @return 
	*/
    public function customerservicetotalAction()
    {
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlCustomerServiceTotal.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlCustomerServiceTotal = new SqlCustomerServiceTotal();
		$arrErr = array();
		$arrPram = array();

		//パラメータ設定
		$startdate = $clsCommon->SetParam($this->getRequest(), "startdate");		//カスタマー対応日(開始)
		$enddate = $clsCommon->SetParam($this->getRequest(), "enddate");			//カスタマー対応日(終了)
		$print = $clsCommon->SetParam($this->getRequest(), "print");				//出力フラグ
		$token = $clsCommon->SetParam($this->getRequest(), "token");

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token, '', '', $clsComConst::PGID_PRINTCSV_CUSTOMERSERVICE_TOTAL))
		{ throw new Exception("", $clsComConst::ERR_CODE_403); }

		//CSV出力ボタン押下時
		if($print == 1)
		{
			//パラメータチェック
			$clsParamCheck->ChkMust($startdate, "カスタマー対応日(開始)");
			$clsParamCheck->ChkMust($enddate, "カスタマー対応日(終了)");
			if(isset($startdate)) { $clsParamCheck->ChkDate($startdate, "カスタマー対応日(開始)"); }
			if(isset($enddate)) { $clsParamCheck->ChkDate($enddate, "カスタマー対応日(終了)"); }
			$arrErr = $clsParamCheck->GetErrMsg();

			if(count($arrErr) > 0)
			{
				foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
				$this->view->ErrMsg = $msg;
			}
			else
			{
				//パラメータ生成
				$arrPram = array("startdate" => $startdate, "enddate" => $enddate);

				//カスタマーセンター問合せ集計情報検索
				$blnRet = $clsSqlCustomerServiceTotal->SelectCustomerServiceTotal($clsComConst::DB_KIKAN , $arrPram);
				if($blnRet) { $arrRet = $clsSqlCustomerServiceTotal->GetData(); }

				if(count($arrRet) > 0)
				{ 
					//CSV出力処理
					$clsCommon->SetCsv("カスタマーセンター問合せ集計出力", $arrRet, "会社コード, 会社名, 案件番号, 案件種別コード, 案件名, 接点番号, 部署コード, 部署名, 対応社員コード, 対応社員名, 接点日付, 方法コード, 方法名, 内容コード, 内容名, 取引先コード, 取引先名, 顧客備考, 顧客登録日, 住所１, 住所２, 業種コード, 業種名, 業種細分コード, 業種細分名, 主担当営業拠点コード, 主担当営業拠点名, 主担当部署コード, 主担当部署名, 主担当者コード, 主担当者名, 受注登録日, 最短納期, 受注金額合計, 粗利合計, 受注伝票明細数, 接点詳細");
					exit;
				}
				else { $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
			}
		}

		//設定処理
		$this->view->StartDate = $clsCommon->ConverDisp($startdate);
		$this->view->EndDate = $clsCommon->ConverDisp($enddate);
		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->List = $clsCommon->GetMenuList();
		$this->view->SubList = $clsCommon->GetSubMenuList();
    }

	/**
	* S版管理表出力検索
	* 
	* @param
	* @return 
	*/
    public function seditionlistAction()
    {
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlSEditionList.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlSEditionList = new SqlSEditionList();
		$arrErr = array();
		$arrPram = array();
		$blnErr = true;

		//パラメータ設定
		$startdate = $clsCommon->SetParam($this->getRequest(), "startdate");		//受注伝票納期(開始)
		$enddate = $clsCommon->SetParam($this->getRequest(), "enddate");			//受注伝票納期(終了)
		$sstartdate = $clsCommon->SetParam($this->getRequest(), "sstartdate");		//S版伝票納期(開始)
		$senddate = $clsCommon->SetParam($this->getRequest(), "senddate");			//S版伝票納期(終了)
		$outsourcenm = $clsCommon->SetParam($this->getRequest(), "outsourcenm");	//業者名
		$print = $clsCommon->SetParam($this->getRequest(), "print");				//出力フラグ
		$token = $clsCommon->SetParam($this->getRequest(), "token");

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token, '', '', $clsComConst::PGID_PRINTCSV_S_EDITION_LIST))
		{ throw new Exception("", $clsComConst::ERR_CODE_403); }

		//CSV出力ボタン押下時
		if($print == 1)
		{
			//パラメータチェック
			if((!isset($startdate) || !isset($enddate)) && (!isset($sstartdate) || !isset($senddate)))
			{ $msg .= "受注伝票納期かS版伝票納期を入力してください。</br>"; $blnErr = false; }

			if(isset($startdate)) { $clsParamCheck->ChkDate($startdate, "受注伝票納期(開始)"); }
			if(isset($enddate)) { $clsParamCheck->ChkDate($enddate, "受注伝票納期(終了)"); }
			if(isset($sstartdate)) { $clsParamCheck->ChkDate($startdate, "S版伝票納期(開始)"); }
			if(isset($senddate)) { $clsParamCheck->ChkDate($enddate, "S版伝票納期(終了)"); }
			if(isset($outsourcenm)) { $clsParamCheck->ChkStr($outsourcenm, "業者名"); }
			$arrErr = $clsParamCheck->GetErrMsg();

			if(!$blnErr || count($arrErr) > 0)
			{
				foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
				$this->view->ErrMsg = $msg;
			}
			else
			{
				//セッション情報取得
				$session = $clsCommon->GetSession();

				//パラメータ生成
				$arrPram = array("kcd" => $session->kcd, "startdate" => $startdate, "enddate" => $enddate,
								 "sstartdate" => $sstartdate, "senddate" => $senddate, "outsourcenm" => $outsourcenm);

				//S版管理表情報検索
				$blnRet = $clsSqlSEditionList->SelectSEditionList($clsComConst::DB_KIKAN , $arrPram);
				if($blnRet) { $arrRet = $clsSqlSEditionList->GetData(); }

				if(count($arrRet) > 0)
				{ 
					//CSV出力処理
					$clsCommon->SetCsv("S版管理表出力", $arrRet, "品目コード, 品名, 契約主コード, 契約主, 請求先コード, 請求先名, 担当者所属コード, 担当者所属, 担当者コード, 担当者, 受注番号, 受注明細番号, 利益管理番号, 利益管理明細番号, 納期(利益管理表), 納期(受注伝票), 受注金額, 消費税, 税込受注金額, 外注先, 外注金額, 外注金額消費税, 税込外注金額");
					exit;
				}
				else { $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
			}
		}

		//設定処理
		$this->view->StartDate = $clsCommon->ConverDisp($startdate);
		$this->view->EndDate = $clsCommon->ConverDisp($enddate);
		$this->view->SStartDate = $clsCommon->ConverDisp($sstartdate);
		$this->view->SEndDate = $clsCommon->ConverDisp($senddate);
		$this->view->OutSourceName = $clsCommon->ConverDisp($outsourcenm);
		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->List = $clsCommon->GetMenuList();
		$this->view->SubList = $clsCommon->GetSubMenuList();
    }

	/**
	* 事例登録状況出力検索
	* 
	* @param
	* @return 
	*/
    public function caseregistersituationAction()
    {
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlCaseRegisterSituation.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlCaseRegisterSituation = new SqlCaseRegisterSituation();
		$arrErr = array();
		$arrPram = array();
		$csvname = "";
		$header = "";

		//パラメータ設定
		$type = $clsCommon->SetParam($this->getRequest(), "type");					//種別
		$startdate = $clsCommon->SetParam($this->getRequest(), "startdate");		//報告日(開始)
		$enddate = $clsCommon->SetParam($this->getRequest(), "enddate");			//報告日(終了)
		$print = $clsCommon->SetParam($this->getRequest(), "print");				//出力フラグ
		$token = $clsCommon->SetParam($this->getRequest(), "token");
		if(!isset($type)) { $type = 1; }

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token, '', '', $clsComConst::PGID_PRINTCSV_CASE_REGISTERSITUATION))
		{ throw new Exception("", $clsComConst::ERR_CODE_403); }

		//CSV出力ボタン押下時
		if($print == 1)
		{
			//パラメータチェック
			$clsParamCheck->ChkMust($type, "種別");
			$clsParamCheck->ChkMust($startdate, "報告日(開始)");
			$clsParamCheck->ChkMust($enddate, "報告日(終了)");
			if(isset($type)) { $clsParamCheck->ChkNumeric($type, "種別"); }
			if(isset($startdate)) { $clsParamCheck->ChkDate($startdate, "報告日(開始)"); }
			if(isset($enddate)) { $clsParamCheck->ChkDate($enddate, "報告日(終了)"); }
			$arrErr = $clsParamCheck->GetErrMsg();

			if(count($arrErr) > 0)
			{
				foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
				$this->view->ErrMsg = $msg;
			}
			else
			{
				//パラメータ生成
				$arrPram = array("startdate" => $startdate, "enddate" => $enddate);

				switch($type)
				{
					case 1:
						$csvname = "受注事例登録状況出力";
						$header = "事例番号, 案件番号, 報告日, 報告拠点名, 報告部署名, 報告社員コード, 報告社員名, トビックス評価, 受賞日, 契約主コード, 契約主名, 受注担当者部署名, 自社担当者コード, 自社担当者名, 業種名, 業種細分, 新規or更新, 評価, 受失注経緯, トークポイント, その他ポイント";
						$blnRet = $clsSqlCaseRegisterSituation->SelectCaseReceive($clsComConst::DB_KIKAN , $arrPram);
						break;
					case 2:
						$csvname = "反応事例登録状況出力";
						$header = "反響番号, 受注番号, 明細番号, 広告主コード, 広告主名, 広告名, 電話番号, 掲載日, 報告拠点, 報告部署, 報告担当コード, 報告社員名, 業種名, 業種細分, 特集名, サイズ, エリア, ジャンル, カテゴリ, ＣＬ満足度, 問合せ, 来店or成約, 来店or成約単位, 年齢層, 属性, 男女比, 既存客割合, 課題・弱点１, 課題・弱点２, 課題・弱点３, サービス内容, サービス内容詳細, 反応のポイント, 登録日, 更新日, 掲載版, 受注種別, 版下番号, 大分類, 中分類";
						$blnRet = $clsSqlCaseRegisterSituation->SelectCaseReaction($clsComConst::DB_KIKAN , $arrPram);
						break;
					default:
						throw new Exception("種別が不正です。", $clsComConst::ERR_CODE_400);
						break;
				}

				if($blnRet) { $arrRet = $clsSqlCaseRegisterSituation->GetData(); }

				if(count($arrRet) > 0)
				{ 
					//CSV出力処理
					$clsCommon->SetCsv($csvname, $arrRet, $header);
					exit;
				}
				else { $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
			}
		}

		//設定処理
		$this->view->Type = $clsCommon->ConverDisp($type);
		$this->view->StartDate = $clsCommon->ConverDisp($startdate);
		$this->view->EndDate = $clsCommon->ConverDisp($enddate);
		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->List = $clsCommon->GetMenuList();
		$this->view->SubList = $clsCommon->GetSubMenuList();
    }

	/**
	* ぱどPO精算状況出力検索
	* 
	* @param
	* @return 
	*/
    public function pointadjustedsituationAction()
    {
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlPointAdjustedSituation.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlPointAdjustedSituation = new SqlPointAdjustedSituation();
		$arrErr = array();
		$arrPram = array();
		$csvname = "";

		//パラメータ設定
		$type = $clsCommon->SetParam($this->getRequest(), "type");					//種別
		$startdate = $clsCommon->SetParam($this->getRequest(), "startdate");		//納期(開始)
		$enddate = $clsCommon->SetParam($this->getRequest(), "enddate");			//納期(終了)
		$sstartdate = $clsCommon->SetParam($this->getRequest(), "sstartdate");		//精算日(開始)
		$senddate = $clsCommon->SetParam($this->getRequest(), "senddate");			//精算日(終了)
		$print = $clsCommon->SetParam($this->getRequest(), "print");				//出力フラグ
		$token = $clsCommon->SetParam($this->getRequest(), "token");
		if(!isset($type)) { $type = 1; }

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token, '', '', $clsComConst::PGID_PRINTCSV_POINT_ADJUSTEDSITUATION))
		{ throw new Exception("", $clsComConst::ERR_CODE_403); }

		//CSV出力ボタン押下時
		if($print == 1)
		{
			//パラメータチェック
			$clsParamCheck->ChkMust($type, "種別");
			if(isset($type)) { $clsParamCheck->ChkNumeric($type, "種別"); }
			if(isset($startdate) && !empty($startdate)) { $clsParamCheck->ChkMonth($startdate, "納期(開始)"); }
			if(isset($enddate) && !empty($startdate)) { $clsParamCheck->ChkMonth($enddate, "納期(終了)"); }
			if(isset($sstartdate) && !empty($startdate)) { $clsParamCheck->ChkDate($sstartdate, "精算日(開始)"); }
			if(isset($senddate) && !empty($startdate)) { $clsParamCheck->ChkDate($senddate, "精算日(終了)"); }
			$arrErr = $clsParamCheck->GetErrMsg();

			if(count($arrErr) > 0)
			{
				foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
				$this->view->ErrMsg = $msg;
			}
			else
			{
				//セッション情報取得
				$session = $clsCommon->GetSession();

				//パラメータ生成
				$arrPram = array("kcd" => $session->kcd, "startdate" => $startdate, "enddate" => $enddate, "sstartdate" => $sstartdate, "senddate" => $senddate);

				switch($type)
				{
					case 1:
						$csvname = "ぱどPO精算状況出力(請求全体)";
						$blnRet = $clsSqlPointAdjustedSituation->SelectPAdjustedSituationDemand($clsComConst::DB_KIKAN , $arrPram);
						break;
					case 2:
						$csvname = "ぱどPO精算状況出力(ポイント)";
						$blnRet = $clsSqlPointAdjustedSituation->SelectPAdjustedSituationPoint($clsComConst::DB_KIKAN , $arrPram);
						break;
					default:
						throw new Exception("種別が不正です。", $clsComConst::ERR_CODE_400);
						break;
				}

				if($blnRet) { $arrRet = $clsSqlPointAdjustedSituation->GetData(); }

				if(count($arrRet) > 0)
				{ 
					//CSV出力処理
					$clsCommon->SetCsv($csvname, $arrRet, "ポイント対象年月, 請求伝票番号, 請求先コード, 請求先名, 店舗ID, 発行ポイント料, 利用ポイント料, 消込額, 残高, 最新消込日, 最新入金番号, 請求年月以降精算, 入金種別CD, 入金種別, 入金種別=売訂");
					exit;
				}
				else { $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
			}
		}

		//設定処理
		$this->view->Type = $clsCommon->ConverDisp($type);
		$this->view->StartDate = $clsCommon->ConverDisp($startdate);
		$this->view->EndDate = $clsCommon->ConverDisp($enddate);
		$this->view->SStartDate = $clsCommon->ConverDisp($sstartdate);
		$this->view->SEndDate = $clsCommon->ConverDisp($senddate);
		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->List = $clsCommon->GetMenuList();
		$this->view->SubList = $clsCommon->GetSubMenuList();
    }

	/**
	* ぱどPO精算状況出力検索(旧システム)
	* 
	* @param
	* @return 
	*/
    public function oldpointadjustedsituationAction()
    {
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlPointAdjustedSituation.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlPointAdjustedSituation = new SqlPointAdjustedSituation();
		$arrErr = array();
		$arrPram = array();
		$csvname = "";

		//パラメータ設定
		$type = $clsCommon->SetParam($this->getRequest(), "type");					//種別
		$startdate = $clsCommon->SetParam($this->getRequest(), "startdate");		//納期(開始)
		$enddate = $clsCommon->SetParam($this->getRequest(), "enddate");			//納期(終了)
		$sstartdate = $clsCommon->SetParam($this->getRequest(), "sstartdate");		//精算日(開始)
		$senddate = $clsCommon->SetParam($this->getRequest(), "senddate");			//精算日(終了)
		$print = $clsCommon->SetParam($this->getRequest(), "print");				//出力フラグ
		$token = $clsCommon->SetParam($this->getRequest(), "token");
		if(!isset($type)) { $type = 1; }

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token, '', '', $clsComConst::PGID_PRINTCSV_OLD_POINT_ADJUSTEDSITUATION))
		{ throw new Exception("", $clsComConst::ERR_CODE_403); }

		//CSV出力ボタン押下時
		if($print == 1)
		{
			//パラメータチェック
			$clsParamCheck->ChkMust($type, "種別");
			if(isset($type)) { $clsParamCheck->ChkNumeric($type, "種別"); }
			if(isset($startdate) && !empty($startdate)) { $clsParamCheck->ChkMonth($startdate, "納期(開始)"); }
			if(isset($enddate) && !empty($startdate)) { $clsParamCheck->ChkMonth($enddate, "納期(終了)"); }
			if(isset($sstartdate) && !empty($startdate)) { $clsParamCheck->ChkDate($sstartdate, "精算日(開始)"); }
			if(isset($senddate) && !empty($startdate)) { $clsParamCheck->ChkDate($senddate, "精算日(終了)"); }
			$arrErr = $clsParamCheck->GetErrMsg();

			if(count($arrErr) > 0)
			{
				foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
				$this->view->ErrMsg = $msg;
			}
			else
			{
				//セッション情報取得
				$session = $clsCommon->GetSession();

				//パラメータ生成
				$arrPram = array("kcd" => $session->kcd, "startdate" => $startdate, "enddate" => $enddate, "sstartdate" => $sstartdate, "senddate" => $senddate);

				switch($type)
				{
					case 1:
						$csvname = "ぱどPO精算状況出力(請求全体)";
						$blnRet = $clsSqlPointAdjustedSituation->SelectPAdjustedSituationDemand($clsComConst::DB_KIKAN , $arrPram);
						break;
					case 2:
						$csvname = "ぱどPO精算状況出力(ポイント)";
						$blnRet = $clsSqlPointAdjustedSituation->SelectPAdjustedSituationPoint($clsComConst::DB_KIKAN , $arrPram);
						break;
					default:
						throw new Exception("種別が不正です。", $clsComConst::ERR_CODE_400);
						break;
				}

				if($blnRet) { $arrRet = $clsSqlPointAdjustedSituation->GetData(); }

				if(count($arrRet) > 0)
				{ 
					//CSV出力処理
					$clsCommon->SetCsv($csvname, $arrRet, "ポイント対象年月, 請求伝票番号, 請求先コード, 請求先名, 店舗ID, 発行ポイント料, 利用ポイント料, 消込額, 残高, 最新消込日, 最新入金番号, 請求年月以降精算, 入金種別CD, 入金種別, 入金種別=売訂");
					exit;
				}
				else { $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
			}
		}

		//設定処理
		$this->view->Type = $clsCommon->ConverDisp($type);
		$this->view->StartDate = $clsCommon->ConverDisp($startdate);
		$this->view->EndDate = $clsCommon->ConverDisp($enddate);
		$this->view->SStartDate = $clsCommon->ConverDisp($sstartdate);
		$this->view->SEndDate = $clsCommon->ConverDisp($senddate);
		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->List = $clsCommon->GetMenuList();
		$this->view->SubList = $clsCommon->GetSubMenuList();
    }

	/**
	* 前受精算状況出力検索
	* 
	* @param
	* @return 
	*/
    public function advanceadjustedsituationAction()
    {
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlAdvanceAdjustedSituation.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlAdvanceAdjustedSituation = new SqlAdvanceAdjustedSituation();
		$arrErr = array();
		$arrPram = array();
		$csvname = "";

		//パラメータ設定
		$startdate = $clsCommon->SetParam($this->getRequest(), "startdate");		//基準売上日
		$print = $clsCommon->SetParam($this->getRequest(), "print");				//出力フラグ
		$token = $clsCommon->SetParam($this->getRequest(), "token");
		if(!isset($type)) { $type = 1; }

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token, '', '', $clsComConst::PGID_PRINTCSV_POINT_ADJUSTEDSITUATION))
		{ throw new Exception("", $clsComConst::ERR_CODE_403); }

		//CSV出力ボタン押下時
		if($print == 1)
		{
			//パラメータチェック
			$clsParamCheck->ChkMust($startdate, "基準売上日");
			if(isset($startdate) && !empty($startdate)) { $clsParamCheck->ChkDate($startdate, "基準売上日"); }
			$arrErr = $clsParamCheck->GetErrMsg();

			if(count($arrErr) > 0)
			{
				foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
				$this->view->ErrMsg = $msg;
			}
			else
			{
				//パラメータ生成
				$arrPram = array("startdate" => $startdate);

				//前受精算状況出力検索
				$blnRet = $clsSqlAdvanceAdjustedSituation->SelectAdvanceAdjustedSituation($clsComConst::DB_KIKAN , $arrPram);

				if($blnRet) { $arrRet = $clsSqlAdvanceAdjustedSituation->GetData(); }

				if(count($arrRet) > 0)
				{ 
					//CSV出力処理
					$clsCommon->SetCsv("前受精算状況出力", $arrRet, "会社コード, 請求先番号, 明細金額, 明細税, 消し込み日, 納期, 消し込み, 前受, 請求番号, 請求連番");
					exit;
				}
				else { $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
			}
		}

		//設定処理
		$this->view->Type = $clsCommon->ConverDisp($type);
		$this->view->StartDate = $clsCommon->ConverDisp($startdate);
		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->List = $clsCommon->GetMenuList();
		$this->view->SubList = $clsCommon->GetSubMenuList();
    }
}

