<?php

/**
* AnalysisController for class
*
* 分析情報処理クラス
*
* @category   Analysis
* @package    Pado
* @author     Hitomi Aihara
* @author     
* @version    1.0
* @link       http://kikansub.pado.jp/analysis/
*/
class AnalysisController extends Zend_Controller_Action
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
	* 部数表
	* 
	* @param
	* @return 
	*/
	public function circulationAction()
	{
		// action body
	}

	/**
	* 部数表詳細画面
	* 
	* @param
	* @return 
	*/
	public function circulationdetailAction()
	{
		// action body
	}

	/**
	* 受注履歴検索
	* 
	* @param
	* @return 
	*/
    public function receivehistoryAction()
    {
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlReceiveHistory.php";
		require_once dirname(__FILE__) . "/../models/SqlMstTorihikisaki.php";
		require_once dirname(__FILE__) . "/../models/SqlMstShain.php";
		require_once dirname(__FILE__) . "/../models/SqlMstJuchuShubetu.php";
		require_once dirname(__FILE__) . "/../models/SqlMstKeisaihan.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlRHistory = new SqlReceiveHistory();
		$clsSqlMstKeisaihan = new SqlMstKeisaihan();
		$arrErr = array();
		$arrPram = array();

		//パラメータ設定
		$contractcd = $clsCommon->SetParam($this->getRequest(), "contractcd");			//契約主コード
		$contractnm = $clsCommon->SetParam($this->getRequest(), "contractnm");			//契約主名
		$claimantcd = $clsCommon->SetParam($this->getRequest(), "claimantcd");			//請求先コード
		$claimantnm = $clsCommon->SetParam($this->getRequest(), "claimantnm");			//請求先名
		$advertisercd = $clsCommon->SetParam($this->getRequest(), "advertisercd");		//広告主コード
		$advertisernm = $clsCommon->SetParam($this->getRequest(), "advertisernm");		//広告主名
		$connectcd = $clsCommon->SetParam($this->getRequest(), "connectcd");			//自社取引担当者コード
		$connectnm = $clsCommon->SetParam($this->getRequest(), "connectnm");			//自社取引担当者名
		$claimcd = $clsCommon->SetParam($this->getRequest(), "claimcd");				//自社請求担当者コード
		$claimnm = $clsCommon->SetParam($this->getRequest(), "claimnm");				//自社請求担当者名
		$reportcd = $clsCommon->SetParam($this->getRequest(), "reportcd");				//掲載版コード
		$reportnm = $clsCommon->SetParam($this->getRequest(), "reportnm");				//掲載版名
		$dstartdate = $clsCommon->SetParam($this->getRequest(), "dstartdate");			//納期(開始)
		$denddate = $clsCommon->SetParam($this->getRequest(), "denddate");				//納期(終了)
		$bclasscd = $clsCommon->SetParam($this->getRequest(), "bclasscd");				//大分類コード
		$bclassnm = $clsCommon->SetParam($this->getRequest(), "bclassnm");				//大分類名
		$mclasscd = $clsCommon->SetParam($this->getRequest(), "mclasscd");				//中分類コード
		$mclassnm = $clsCommon->SetParam($this->getRequest(), "mclassnm");				//中分類名
		$kindcd = $clsCommon->SetParam($this->getRequest(), "kindcd");					//種別コード
		$kindnm = $clsCommon->SetParam($this->getRequest(), "kindnm");					//種別名
		$advertisingnm = $clsCommon->SetParam($this->getRequest(), "advertisingnm");	//広告名
		$grid = $clsCommon->SetParam($this->getRequest(), "grid");						//Gridデータ
		$token = $clsCommon->SetParam($this->getRequest(), "token");

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token)) { throw new Exception("", $clsComConst::ERR_CODE_403); }

		//セッション情報取得
		$session = $clsCommon->GetSession();

		//Gridデータデコード
		if(isset($grid)){ $grid = json_decode($grid); } else { $grid = array();}

		//検索ボタン押下時
		if($_POST['btnSerch'])
		{
			//アクセスログ出力処理
			$clsCommon->AccessLog($session);

			//パラメータチェック
			if(isset($contractcd)) { $clsParamCheck->ChkNumeric($contractcd, "契約主コード"); }
			if(isset($claimantcd)) { $clsParamCheck->ChkNumeric($claimantcd, "請求先コード"); }
			if(isset($advertisercd)) { $clsParamCheck->ChkNumeric($advertisercd, "広告主コード"); }
			if(isset($connectcd)) { $clsParamCheck->ChkNumeric($connectcd, "自社取引担当者コード"); }
			if(isset($claimcd)) { $clsParamCheck->ChkNumeric($claimcd, "自社請求担当者コード"); }
			if(isset($reportcd)) { $clsParamCheck->ChkNumeric($reportcd, "掲載版コード"); }
			$clsParamCheck->ChkMust($dstartdate, "納期(開始)");
			$clsParamCheck->ChkMust($denddate, "納期(終了)");
			if(isset($dstartdate)) { $clsParamCheck->ChkDate($dstartdate, "納期(開始)"); }
			if(isset($denddate)) { $clsParamCheck->ChkDate($denddate, "納期(終了)"); }
			if(isset($bclasscd)) { $clsParamCheck->ChkNumeric($bclasscd, "大分類コード"); }
			if(isset($mclasscd)) { $clsParamCheck->ChkNumeric($mclasscd, "中分類コード"); }
			if(isset($kindcd)) { $clsParamCheck->ChkNumeric($kindcd, "種別コード"); }
			if(isset($advertisingnm)) { $clsParamCheck->ChkStr($advertisingnm, "広告名"); }
			$arrErr = $clsParamCheck->GetErrMsg();

			if(count($arrErr) > 0)
			{
				foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
				$this->view->ErrMsg = $msg;
			}
			else
			{
				//パラメータ生成
				$arrPram = array("kcd" => $session->kcd, "contractcd" => $contractcd, "claimantcd" => $claimantcd , 
								 "advertisercd" => $advertisercd, "connectcd" => $connectcd, "claimcd" => $claimcd, 
								 "reportcd" => $reportcd, "dstartdate" => $dstartdate, "denddate" => $denddate, 
								 "bclasscd" => $bclasscd, "mclasscd" => $mclasscd, "kindcd" => $kindcd, "advertisingnm" => $advertisingnm);

				//受注履歴検索
				$blnRet = $clsSqlRHistory->SelectReceiveHistory($clsComConst::DB_KIKAN , $arrPram);
				if($blnRet) { $arrRet = $clsSqlRHistory->GetData(); }
			}

			//Gridデータ表示
			if(count($arrRet) > 0)
			{
				if(count($arrRet) == 1000) { $this->view->ErrMsg = $clsComConst::ERR_MSG_COUNT_OVER; }
			 	$this->view->arrdata = json_encode($arrRet);
			}
			else { $this->view->arrdata = json_encode(array()); }
		}
		//CSV出力ボタン押下時
		else if($_POST['btnCsv'])
		{
			if(count($grid) > 0)
			{
				//CSV出力処理
				$clsCommon->SetCsv("受注履歴", $grid, "契約主コード, 契約主名, 掲載版, 掲載エリア, 掲載日, 掲載号, 商品名, サイズ, 受注番号, 売価, 制作費, 小計, 消費税, 総額, 粗利, 受注担当者コード, 受注担当者名, 入金予定日");
				exit();
			}
			else
			{
				$this->view->ErrMsg = $clsComConst::ERR_MSG_CSV;
				$this->view->arrdata = json_encode($grid);
			}
		}
		else
		{
			//パラメータチェック
			if(isset($contractcd)) { $clsParamCheck->ChkNumeric($contractcd, "契約主コード"); }
			if(isset($claimantcd)) { $clsParamCheck->ChkNumeric($claimantcd, "請求先コード"); }
			if(isset($advertisercd)) { $clsParamCheck->ChkNumeric($advertisercd, "広告主コード"); }
			if(isset($connectcd)) { $clsParamCheck->ChkNumeric($connectcd, "自社取引担当者コード"); }
			if(isset($claimcd)) { $clsParamCheck->ChkNumeric($claimcd, "自社請求担当者コード"); }
			if(isset($reportcd)) { $clsParamCheck->ChkNumeric($reportcd, "掲載版コード"); }
			if(isset($bclasscd)) { $clsParamCheck->ChkNumeric($bclasscd, "大分類コード"); }
			if(isset($mclasscd)) { $clsParamCheck->ChkNumeric($mclasscd, "中分類コード"); }
			if(isset($kindcd)) { $clsParamCheck->ChkNumeric($kindcd, "種別コード"); }
			$arrErr = $clsParamCheck->GetErrMsg();

			if(count($arrErr) > 0)
			{
				foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
				$this->view->ErrMsg = $msg;
			}
			else
			{
				if(isset($contractcd))
				{
					//契約主名取得
					$clsSqlMstTorihikisaki = new SqlMstTorihikisaki();
					$arrPram = array("kcd" => $session->kcd, "clientcd" => $contractcd);
					$blnRet = $clsSqlMstTorihikisaki->SelectTorihikisakiName($clsComConst::DB_KIKAN , $arrPram);
					if($blnRet) { $contractnm = $clsSqlMstTorihikisaki->GetTorihikisakiName(); }
					if(!isset($contractnm) || empty($contractnm)) { $contractcd = ""; }
				}
				else { $contractnm = ""; }

				if(isset($claimantcd))
				{
					//請求先名取得
					$clsSqlMstTorihikisaki = new SqlMstTorihikisaki();
					$arrPram = array("kcd" => $session->kcd, "clientcd" => $claimantcd);
					$blnRet = $clsSqlMstTorihikisaki->SelectTorihikisakiName($clsComConst::DB_KIKAN , $arrPram);
					if($blnRet) { $claimantnm = $clsSqlMstTorihikisaki->GetTorihikisakiName(); }
					if(!isset($claimantnm) || empty($claimantnm)) { $claimantcd = ""; }
				}
				else { $claimantnm = ""; }

				if(isset($advertisercd))
				{
					//請求先名取得
					$clsSqlMstTorihikisaki = new SqlMstTorihikisaki();
					$arrPram = array("kcd" => $session->kcd, "clientcd" => $advertisercd);
					$blnRet = $clsSqlMstTorihikisaki->SelectTorihikisakiName($clsComConst::DB_KIKAN , $arrPram);
					if($blnRet) { $advertisernm = $clsSqlMstTorihikisaki->GetTorihikisakiName(); }
					if(!isset($advertisernm) || empty($advertisernm)) { $advertisercd = ""; }
				}
				else { $advertisernm = ""; }

				if(isset($connectcd))
				{
					//自社取引担当者名取得
					$clsSqlMstShain = new SqlMstShain();
					$arrPram = array("kcd" => $session->kcd, "cd" => $connectcd);
					$blnRet = $clsSqlMstShain->SelectMstShain($clsComConst::DB_KIKAN , $arrPram);
					if($blnRet) { $connectnm = $clsSqlMstShain->GetShainName(); }
					if(!isset($connectnm) || empty($connectnm)) { $connectcd = ""; }
				}
				else { $connectnm = ""; }

				if(isset($claimcd))
				{
					//自社請求担当者名取得
					$clsSqlMstShain = new SqlMstShain();
					$arrPram = array("kcd" => $session->kcd, "cd" => $claimcd);
					$blnRet = $clsSqlMstShain->SelectMstShain($clsComConst::DB_KIKAN , $arrPram);
					if($blnRet) { $claimnm = $clsSqlMstShain->GetShainName(); }
					if(!isset($claimnm) || empty($claimnm)) { $claimcd = ""; }
				}
				else { $claimnm = ""; }

				if(isset($reportcd))
				{
					//掲載版名取得
					$clsSqlMstKeisaihan = new SqlMstKeisaihan();
					$arrPram = array("kcd" => $session->kcd, "serch" => $reportcd);
					$blnRet = $clsSqlMstKeisaihan->SelectMstKeisaihan($clsComConst::DB_KIKAN , $arrPram);
					if($blnRet) { $reportnm = $clsSqlMstKeisaihan->GetKeisaihanName(); }
					if(!isset($reportnm) || empty($reportnm)) { $reportcd = ""; }
				}
				else { $reportnm = ""; }

				if(isset($bclasscd))
				{
					//大分類名取得
					$clsSqlMstJuchuShubetu = new SqlMstJuchuShubetu();
					$arrPram = array("serch" => $bclasscd);
					$blnRet = $clsSqlMstJuchuShubetu->SelectMstDaibunrui($clsComConst::DB_KIKAN , $arrPram);
					if($blnRet) { $arrRet = $clsSqlMstJuchuShubetu->GetData(); }

					if(count($arrRet) > 0)
					{
						$bclasscd = $arrRet[0]["codeid"];
						$bclassnm = $arrRet[0]["codename"];

						if(isset($mclasscd))
						{
							//中分類名取得
							$clsSqlMstJuchuShubetu = new SqlMstJuchuShubetu();
							$arrPram = array("dcd" => $bclasscd, "serch" => $mclasscd);
							$blnRet = $clsSqlMstJuchuShubetu->SelectMstChubunrui($clsComConst::DB_KIKAN , $arrPram);
							if($blnRet) { $arrRet = $clsSqlMstJuchuShubetu->GetData(); }

							if(count($arrRet) > 0)
							{
								$bclasscd = $arrRet[0]["dcodeid"];
								$bclassnm = $arrRet[0]["dname"];
								$mclasscd = $arrRet[0]["ccodeid"];
								$mclassnm = $arrRet[0]["codename"];

								if(isset($kindcd))
								{
									//種別名取得
									$clsSqlMstJuchuShubetu = new SqlMstJuchuShubetu();
									$arrPram = array("dcd" => $bclasscd, "ccd" => $mclasscd, "serch" => $kindcd);
									$blnRet = $clsSqlMstJuchuShubetu->SelectMstJuchuShubetu($clsComConst::DB_KIKAN , $arrPram);
									if($blnRet) { $arrRet = $clsSqlMstJuchuShubetu->GetData(); }

									if(count($arrRet) > 0)
									{
										$bclasscd = $arrRet[0]["dcodeid"];
										$bclassnm = $arrRet[0]["dname"];
										$mclasscd = $arrRet[0]["ccodeid"];
										$mclassnm = $arrRet[0]["cname"];
										$kindcd = $arrRet[0]["scodeid"];
										$kindnm = $arrRet[0]["codename"];
									}
									else { $kindcd = ""; $kindnm = ""; }
								}
								else { $kindnm = ""; }
							}
							else { $mclasscd = ""; $mclassnm = ""; $kindcd = ""; $kindnm = ""; }
						}
						else { $mclassnm = ""; $kindcd = ""; $kindnm = ""; }
					}
					else { $bclasscd = ""; $bclassnm = ""; $mclasscd = ""; $mclassnm = ""; $kindcd = ""; $kindnm = ""; }
				}
				else { $bclassnm = ""; $mclasscd = ""; $mclassnm = ""; $kindcd = ""; $kindnm = ""; }
			}
			$arrRet = array();
			$this->view->arrdata = json_encode($grid);
		}

		//設定処理
		$this->view->cdialog_employee_url = $clsComConst::CDIALOG_EMPLOYEE_URL;
		$this->view->cdialog_client_url = $clsComConst::CDIALOG_CLIENT_URL;
		$this->view->cdialog_code_url = $clsComConst::CDIALOG_CODE_URL;
		$this->view->code_daibunrui = $clsComConst::CODE_DAIBUNRUI;
		$this->view->code_chubunrui = $clsComConst::CODE_CHUBUNRUI;
		$this->view->code_shubetu = $clsComConst::CODE_SHUBETU;
		$this->view->code_keisaihan = $clsComConst::CODE_KEISAIHAN;
 		$this->view->code_contract = $clsComConst::CODE_CONTRACT;
		$this->view->code_claimant = $clsComConst::CODE_CLAIMANT;
		$this->view->code_advertiser = $clsComConst::CODE_ADVERTISER;
		$this->view->code_connect_employee = $clsComConst::CODE_CONNECT_EMPLOYEE;
		$this->view->code_claim_employee = $clsComConst::CODE_CLAIM_EMPLOYEE;

		$this->view->ContractCd = $clsCommon->ConverDisp($contractcd);
		$this->view->ContractName = $clsCommon->ConverDisp($contractnm);
		$this->view->ClaimantCd = $clsCommon->ConverDisp($claimantcd);
		$this->view->ClaimantName = $clsCommon->ConverDisp($claimantnm);
		$this->view->AdvertiserCd = $clsCommon->ConverDisp($advertisercd);
		$this->view->AdvertiserName = $clsCommon->ConverDisp($advertisernm);
		$this->view->ConnectCd = $clsCommon->ConverDisp($connectcd);
		$this->view->ConnectName = $clsCommon->ConverDisp($connectnm);
		$this->view->ClaimCd = $clsCommon->ConverDisp($claimcd);
		$this->view->ClaimName = $clsCommon->ConverDisp($claimnm);
		$this->view->ReportCd = $clsCommon->ConverDisp($reportcd);
		$this->view->ReportName = $clsCommon->ConverDisp($reportnm);
		$this->view->DStartDate = $clsCommon->ConverDisp($dstartdate);
		$this->view->DEndDate = $clsCommon->ConverDisp($denddate);
		$this->view->BClassCd = $clsCommon->ConverDisp($bclasscd);
		$this->view->BClassName = $clsCommon->ConverDisp($bclassnm);
		$this->view->MClassCd = $clsCommon->ConverDisp($mclasscd);
		$this->view->MClassName = $clsCommon->ConverDisp($mclassnm);
		$this->view->KindCd = $clsCommon->ConverDisp($kindcd);
		$this->view->KindName = $clsCommon->ConverDisp($kindnm);
		$this->view->AdvertisingName = $clsCommon->ConverDisp($advertisingnm);
		$this->view->Token = $clsCommon->ConverDisp($token);
    }
}
