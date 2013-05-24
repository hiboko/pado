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
	 * Excel
	 */
	public $clsComExcel;

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
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlCirculation.php";
		require_once dirname(__FILE__) . "/../models/SqlMstSystem.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlCirculation = new SqlCirculation();
		$clsSqlMstSystem = new SqlMstSystem();
		$arrErr = array();
		$arrPram = array();
		$arrCd = "";

		//パラメータ設定
		$type = $clsCommon->SetParam($this->getRequest(), "type");				//種別
		$report = $clsCommon->SetParam($this->getRequest(), "report");			//掲載版コード
		$between = $clsCommon->SetParam($this->getRequest(), "between");		//期間
		$change = $clsCommon->SetParam($this->getRequest(), "change");			//チェンジイベントフラグ
		$print = $clsCommon->SetParam($this->getRequest(), "print");			//出力イベントフラグ
		$token = $clsCommon->SetParam($this->getRequest(), "token");

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token, "", "", $clsComConst::PGID_ANALYSIS_CIRCULATION))
		{ throw new Exception("", $clsComConst::ERR_CODE_403); }

		//パラメータチェック
		if(isset($report)) { if(!$clsParamCheck->ChkNumeric($report, "掲載版コード"))
		{ throw new Exception("掲載版コードが不正です。", $clsComConst::ERR_CODE_400); break; }}

		//セッション情報取得
		$session = $clsCommon->GetSession();

		//出力ボタン押下時
		if($print == 1)
		{
			//パラメーターチェック
			$elements = split("～", $between);

			foreach( $elements as $val )
			{
				if(!empty($val))
				{
					if(!$clsParamCheck->ChkDate($val, "期間"))
					{ throw new Exception("期間が不正です。", $clsComConst::ERR_CODE_400); break; }
				}
			}
			if(isset($elements[0]) && !empty($elements[0])) { $fromymd = $elements[0]; }
			if(isset($elements[1]) && !empty($elements[1])) { $toymd = $elements[1]; }

			$arrPram = array("kcd" => $session->kcd, "report" => $report, "fromymd" => $fromymd, "toymd" => $toymd);

			switch($type)
			{
				case 1:
					//ブロック表出力処理
					if(!self::printBlockExcel($arrPram)) { $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
					break;
				case 2:
					//地区表
					if(!self::printAreaExcel($arrPram)) { $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
					break;
				case 3:
					//担当地区表(CSV)
					if(!self::printAreaCsv($arrPram)) { $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
					break;
				default:
					throw new Exception("種別が不正です。", $clsComConst::ERR_CODE_400);
					break;
			}
		}

		//初期値設定
		if(!isset($type)) { $type = 1; $change = 1; }

		//種別ラジオボタン
		switch($type)
		{
			case 1:
			case 2:
				//部数表EXCEL出力
				$arrCd = $clsComConst::CODE_CIRCULATION_EXCEL;
				break;
			case 3:
				//部数表CSV出力
				$arrCd = $clsComConst::CODE_CIRCULATION_CSV;
				break;
			default:
				throw new Exception("種別が不正です。", $clsComConst::ERR_CODE_400);
				break;
		}

		//部数表掲載版情報検索
		$arrPram = array("kcd" => $session->kcd, "type" => $arrCd);
		$blnRet = $clsSqlCirculation->SelectCirculationKeisaihan($clsComConst::DB_KIKAN , $arrPram);
		if($blnRet) { $arrRet = $clsSqlCirculation->GetData();
		if(count($arrRet) > 0){ if(!isset($report) || $change == 1) { $report =  $arrRet[0]["cd"]; } }}
		$this->view->report = $clsCommon->ConverArrDropdownList("report", $arrRet, $report, false);

		//掲載版選択処理
		if(isset($report))
		{
			//部数表期間情報検索
			$arrRet = array();
			$arrPram = array("kcd" => $session->kcd, "report" => $report);
			$blnRet = $clsSqlCirculation->SelectCirculationYmd($clsComConst::DB_KIKAN , $arrPram);
			if($blnRet) { $arrRet = $clsSqlCirculation->GetData(); }

			//期間フォーマット生成
			for ($i = 0 ; $i < count($arrRet); $i++)
			{
				$date_from = date_create($arrRet[$i]['name']);

				if(isset($arrRet[$i + 1]['name']))
				{ 
					$date_to = date_create($arrRet[$i + 1]['name']);
					$date = date_format($date_from, 'Y/m/d') . "～" . date('Y/m/d', strtotime(date_format($date_to, 'Y/m/d') . "-1 day"));
				}
				else { $date = date_format($date_from, 'Y/m/d') . "～"; }

				$arrRet[$i]['cd'] = $date;
				$arrRet[$i]['name'] = $date;
			}
			if(count($arrRet) == 0) { $arrRet = array(array("name" => "1990/01/01～")); }
			$this->view->between = $clsCommon->ConverArrDropdownList("between", $arrRet, $between, false, false, false);
		}
		else { $this->view->between = $clsCommon->ConverArrDropdownList("between", array(), $between, true, false, false); }

		//お知らせ情報検索
		$arrRet = array();
		$arrPram = array("kcd" => $session->kcd, "entry" => "BUSUUHYOU", "section" => "MESSAGE");
		$blnRet = $clsSqlMstSystem->SelectSystemMsg($clsComConst::DB_KIKAN_SUB , $arrPram);
		if($blnRet) { $arrRet = $clsSqlMstSystem->GetData(); }
		if(isset($arrRet[0]["val"])) { $this->view->Inform = $clsCommon->ConverDisp($arrRet[0]["val"]); }

		//設定処理
		$this->view->circulation_ditail_url = $clsComConst::ANALYSIS_CIRCULATION_DITAIL_URL;
		$this->view->Type = $clsCommon->ConverDisp($type);
		$this->view->Report = $clsCommon->ConverDisp($report);
		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->List = $clsCommon->GetMenuList();
		$this->view->SubList = $clsCommon->GetSubMenuList();
	}

	/**
	* 部数表詳細画面
	* 
	* @param
	* @return 
	*/
	public function circulationdetailAction()
	{
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlCirculation.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlCirculation = new SqlCirculation();
		$arrErr = array();
		$arrPram = array();

		//パラメータ設定
		$type = $clsCommon->SetParam($this->getRequest(), "type");				//種別
		$report = $clsCommon->SetParam($this->getRequest(), "report");			//掲載版コード
		$fromymd = $clsCommon->SetParam($this->getRequest(), "fromymd");		//期間(開始)
		$toymd = $clsCommon->SetParam($this->getRequest(), "toymd");			//期間(終了)
		$grid = $clsCommon->SetParam($this->getRequest(), "grid");				//Gridデータ
		$check = $clsCommon->SetParam($this->getRequest(), "check");			//Gridチェック行(配列)
		$blcd = $clsCommon->SetParam($this->getRequest(), "blcd");				//ブロックコード(配列)
		$areacd = $clsCommon->SetParam($this->getRequest(), "areacd");			//エリアコード(配列)
		$click = $clsCommon->SetParam($this->getRequest(), "click");			//出力ボタンフラグ
		$token = $clsCommon->SetParam($this->getRequest(), "token");

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token, "", $clsComConst::ANALYSIS_CIRCULATION_URL, $clsComConst::PGID_ANALYSIS_CIRCULATION))
		{ throw new Exception("", $clsComConst::ERR_CODE_403); }

		//パラメータチェック
		if($clsParamCheck->ChkMust($type, "種別")) { $clsParamCheck->ChkNumeric($type, "種別"); }
		if($clsParamCheck->ChkMust($report, "掲載版コード"))
		{
			$elements = split(",", $report);
			foreach( $elements as $val ) { if(!$clsParamCheck->ChkNumeric($val, "掲載版コード")){ break; } }
		}
		if($clsParamCheck->ChkMust($fromymd, "期間(開始)")) { $clsParamCheck->ChkDate($fromymd, "期間(開始)"); }
		if(isset($toymd)) { $clsParamCheck->ChkDate($toymd, "納期(終了)"); }
		if(isset($blcd))
		{
			$elements = split(",", $blcd);
			foreach( $elements as $val ) { if(!$clsParamCheck->ChkNumeric($val, "ブロックコード")){ break; } }
		}
		if(isset($areacd))
		{
			$elements = split(",", $areacd);
			foreach( $elements as $val ) { if(!$clsParamCheck->ChkNumeric($val, "エリアコード")){ break; } }
		}
		$arrErr = $clsParamCheck->GetErrMsg();
		foreach($arrErr as $value) { $msg .= $value; }

		if(count($arrErr) > 0) { throw new Exception($msg, $clsComConst::ERR_CODE_400); }

		//セッション情報取得
		$session = $clsCommon->GetSession();

		//Gridデータデコード
		if(isset($grid)){ $grid = json_decode($grid); } else { $grid = array();}

		//設定処理
		$this->view->circulation_url = $clsComConst::ANALYSIS_CIRCULATION_URL;
		$this->view->circulation_ditail_url = $clsComConst::ANALYSIS_CIRCULATION_DITAIL_URL;
		$this->view->Type = $clsCommon->ConverDisp($type);
		$this->view->Report = $clsCommon->ConverDisp($report);
		$this->view->FromYmd = $clsCommon->ConverDisp($fromymd);
		$this->view->ToYmd = $clsCommon->ConverDisp($toymd);
		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->Check = $clsCommon->ConverDisp($check);
		$this->view->List = $clsCommon->GetMenuList();
		$this->view->SubList = $clsCommon->GetSubMenuList();

		//出力ボタン押下時
		if($click == 1 && isset($check))
		{
			//設定処理
			$this->view->arrdata = json_encode($grid);

			if(count($grid) > 0)
			{
				$arrPram = array("kcd" => $session->kcd, "report" => $report, "fromymd" => $fromymd, "toymd" => $toymd,
				                 "blockcd" => $blockcd, "areacd" => $areacd);

				//種別ラジオボタン
				switch($type)
				{
					case 1:
						//ブロック表出力処理
						if(!self::printBlockExcel($arrPram)) { $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
					case 2:
						//地域表出力処理
						if(!self::printAreaExcel($arrPram)) { $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
						break;
					case 3:
						//担当地区表(CSV)出力
						if(!self::printAreaCsv($arrPram)) { $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
						break;
					default:
						throw new Exception("種別が不正です。", $clsComConst::ERR_CODE_400);
						break;
				}
			}
			else { $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
		}
		else
		{
			//部数表詳細情報検索
			$arrPram = array("kcd" => $session->kcd, "report" => $report, "fromymd" => $fromymd, "toymd" => $toymd);
			$blnRet = $clsSqlCirculation->SelectCirculationDetail($clsComConst::DB_KIKAN , $arrPram);

			if($blnRet) { $arrRet = $clsSqlCirculation->GetData(); }

			//Gridデータ表示
			if(count($arrRet) > 0)
			{
				if(count($arrRet) == 1000) { $this->view->ErrMsg = $clsComConst::ERR_MSG_COUNT_OVER; }
				$this->view->arrdata = json_encode($arrRet);
			}
			else { $this->view->arrdata = json_encode(array()); $this->view->ErrMsg = $clsComConst::ERR_MSG_CSV; }
		}
	}

	/**
	* ブロック表出力処理
	* 
	* @param  $arrParam  パラメータ配列(kcd  :会社コード, arrcd  :掲載版コード  [カンマ区切り], fromymd :期間[開始], 
	*                                   blockcd:ブロックコード[カンマ区切り], areacd:エリアコード[カンマ区切り])
	* @return true:データあり、false:データなし
	*/
    public function printBlockExcel($arrPram)
    {
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlCirculation.php";
		require_once dirname(__FILE__) . "/../models/SqlMstSystem.php";
		require_once dirname(__FILE__) . "/../common/ComExcel.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsSqlCirculation = new SqlCirculation();
		$clsSqlMstSystem = new SqlMstSystem();
		$arrRet = array();
		$arrRetMsg = array();

		//アクセスログ出力処理
		$backtraces = debug_backtrace();
		$msg = $backtraces[1]['class'] . "：" . $backtraces[1]['function'] . "：" . "(" . $arrPram["kcd"] . ") " . $arrPram["scd"] . "：" . $arrPram["snm"] . " [BlockStartPrint]";
		$logFile = $clsComConst::ACCESS_LOG_PATH. '/'. strtr('#DT#.log', array('#DT#'=>date('Ymd')));
		$logger = new Zend_Log(new Zend_Log_Writer_Stream($logFile));
		$logger->log($msg, Zend_Log::INFO);

		//ブロック表情報検索
		$blnRet = $clsSqlCirculation->SelectCirculationBlock($clsComConst::DB_KIKAN , $arrPram);

		if($blnRet) { $arrRet = $clsSqlCirculation->GetData(); }

		if(count($arrRet) > 0)
		{
			try
			{
				//フッターメッセージ検索
				$fmsg = "";
				$arrMstPram = array("kcd" => $arrPram["kcd"], "entry" => "BUSUUHYOU", "section" => "BLOCKHYOFOOTER");
				$blnRet = $clsSqlMstSystem->SelectSystemMsg($clsComConst::DB_KIKAN_SUB , $arrMstPram);
				if($blnRet) { $arrRetMsg = $clsSqlMstSystem->GetData(); }
				if(isset($arrRetMsg[0]["val"])) { $fmsg = $clsCommon->ConverDisp($arrRetMsg[0]["val"]); }

				//Excel生成クラス
				$this->clsComExcel = new ComExcel($clsComConst::EXCEL_BLOCK_PATH, "block", "ブロック表");

				//定数定義
				$maxcol = 15;						//カラム数
				$maxprow = 42;						//ページ最大行数
				$maxdrow = 37;						//明細行最大行数
				$startrow = 4;						//明細開始位置

				//変数定義
				$psrow = 1;							//ページ開始行
				$perow = $maxprow;					//ページ終了行
				$detailrow = $perow - 2;			//明細行
				$datarow = $startrow;				//明細データ現在位置
				$grouprow = $startrow;				//グループ開始行
				$kyonm = $arrRet[0]["KYOTEN_NM"];	//拠点名
				$groupcd = $arrRet[0]["AREA_CD"];	//グループエリアコード

				//ヘッダーフッター情報設定
				self::printBlockHeaderFooterExcel($arrPram, $kyonm, $psrow, $maxcol, $perow, $datarow, $detailrow, $fmsg);

				for ($i = 0 ; $i < count($arrRet); $i++)
				{
					if((int)$arrRet[$i]["CNT"] > $maxdrow) { throw new Exception("出力データ不正", $clsComConst::ERR_CODE_500); }

					if($groupcd != $arrRet[$i]["AREA_CD"])
					{
						//グループ小計情報出力処理
						self::printBlockGroupExcel($groupcd, $datarow, $grouprow);

						$groupcd = $arrRet[$i]["AREA_CD"];
						$grouprow = $datarow + 1;
						$datarow += 1;

						if(($kyonm != $arrRet[$i]["KYOTEN_NM"]) || ((int)$arrRet[$i]["CNT"] > ($detailrow - $datarow)))
						{
							$psrow += $maxprow;					//ページ開始行
							$perow += $maxprow;					//ページ終了行
							$detailrow = $perow - 2;			//明細行
							$datarow = $psrow + $startrow - 1;	//明細データ現在位置
							$grouprow = $datarow;				//グループ開始行
							$kyonm = $arrRet[$i]["KYOTEN_NM"];

							//ヘッダーフッター情報設定
							self::printBlockHeaderFooterExcel($arrPram, $kyonm, $psrow, $maxcol, $perow, $datarow, $detailrow, $fmsg);
						}
					}

					//罫線
					$this->clsComExcel->SetCellsLine('A' . $datarow, 'O' . $datarow, PHPExcel_Style_Border::BORDER_THIN, 4);

					//太線
					$this->clsComExcel->SetCellsLine('F' . $datarow, 'F' . $datarow, PHPExcel_Style_Border::BORDER_MEDIUM, 2);
					$this->clsComExcel->SetCellsLine('F' . $datarow, 'F' . $datarow, PHPExcel_Style_Border::BORDER_MEDIUM, 3);

					//フォントサイズ設定
					$this->clsComExcel->SetCellsFontSize('A' . $datarow, 'E' . $datarow, false, 7);
					$this->clsComExcel->SetCellsFontSize('F' . $datarow, 'F' . $datarow, false, 10);
					$this->clsComExcel->SetCellsFontSize('G' . $datarow, 'K' . $datarow, false, 7);
					$this->clsComExcel->SetCellsFontSize('L' . $datarow, 'O' . $datarow, false, 8);

					//セル内縦配置設定
					$this->clsComExcel->SetCellsVAlign('L' . $datarow, 'O' . $datarow, 1);

					//フォーマット設定
					$this->clsComExcel->SetCellsNumberFormat('E' . $datarow, 'F' . $datarow, '#,##0');
					$this->clsComExcel->SetCellsNumberFormat('H' . $datarow, 'K' . $datarow, '#,##0');
					$this->clsComExcel->SetCellsNumberFormat('G' . $datarow, 'G' . $datarow, '0.0%');

					//折り返して全体を表示
					$this->clsComExcel->SetCellsWrap('L'. $datarow, 'O'. $datarow, true);

					//明細情報設定
					$arrData = array('A' . $datarow => $arrRet[$i]["BLOCK_NM"],
									 'D' . $datarow => '□',
									 'E' . $datarow => '=IF(D'. $datarow . '="□","",F'. $datarow . ')',
									 'F' . $datarow => '=SUM(H'. $datarow . ':K'. $datarow . ')',
									 'G' . $datarow => '=IF(F'. $datarow . '=0,"",H'. $datarow . '/F'. $datarow . ')',
									 'H' . $datarow => $arrRet[$i]["KODATE_SU"],
									 'I' . $datarow => $arrRet[$i]["SHUGOU_SU"],
									 'J' . $datarow => $arrRet[$i]["SONOTA_SU"],
									 'K' . $datarow => $arrRet[$i]["SENBETU_FUKA_SU"],
									 'L' . $datarow => $arrRet[$i]["KUIKI_NM1"],
									 'M' . $datarow => $arrRet[$i]["KUIKI_NM2"],
									 'N' . $datarow => $arrRet[$i]["KUIKI_NM3"],
									 'O' . $datarow => $arrRet[$i]["KUIKI_NM4"]);

					//データ書き込み
					$this->clsComExcel->SetCellValue($arrData);

					//明細情報設定
					$arrData = array('B' . $datarow => $arrRet[$i]["AREA_CD"],
									 'C' . $datarow => $arrRet[$i]["KUIKI_CD"]);

					//データ書き込み
					$this->clsComExcel->SetCellStringValue($arrData);
					$datarow += 1;
				}

				//グループ小計情報出力処理
				self::printBlockGroupExcel($groupcd, $datarow, $grouprow);

				//アクセスログ出力処理
				$backtraces = debug_backtrace();
				$msg = $backtraces[1]['class'] . "：" . $backtraces[1]['function'] . "：" . "(" . $arrPram["kcd"] . ") " . $arrPram["scd"] . "：" . $arrPram["snm"] . " [BlockEndPrint]";
				$logFile = $clsComConst::ACCESS_LOG_PATH. '/'. strtr('#DT#.log', array('#DT#'=>date('Ymd')));
				$logger = new Zend_Log(new Zend_Log_Writer_Stream($logFile));
				$logger->log($msg, Zend_Log::INFO);

				//Excel出力(2003形式)
				$this->clsComExcel->OutputExcel();
				exit;
			}
			catch (Exception $e)
			{
				throw new Exception($e->getMessage(), $clsComConst::ERR_CODE_500);
			}
		}
		else { return false; }

		return true;
	}

	/**
	* ブロックヘッダーフッター情報出力処理
	* 
	* @param  $arrParam  パラメータ配列(kcd  :会社コード, arrcd  :掲載版コード  [カンマ区切り], fromymd :期間[開始], 
	*                                   blockcd:ブロックコード[カンマ区切り], areacd:エリアコード[カンマ区切り])
	* @param  $kyonm      拠点名 
	* @param  $psrow      ページ開始行
	* @param  $maxcol     カラム数
	* @param  $perow      ページ終了行
	* @param  $datarow    明細データ現在位置
	* @param  $detailrow  明細行
	* @param  $fmsg       フッター表示メッセージ
	* @return 
	*/
	public function printBlockHeaderFooterExcel($arrPram, $kyonm, $psrow, $maxcol, $perow, $datarow, $detailrow, $fmsg)
	{
		//--------------------
		// ヘッダー情報設定
		//--------------------
		//行の高さ
		$this->clsComExcel->SetRowHeight($psrow, 10.50);
		$this->clsComExcel->SetRowHeight(($psrow + 1), 12);
		$this->clsComExcel->SetRowHeight(($psrow + 2), 21.75);

		//セルの結合
		$this->clsComExcel->SetMergeCells('A'. ($psrow + 2), 'C'. ($psrow + 2));
		$this->clsComExcel->SetMergeCells('D'. ($psrow + 2), 'E'. ($psrow + 2));
		$this->clsComExcel->SetMergeCells('L'. ($psrow + 2), 'O'. ($psrow + 2));

		//フォントサイズ設定
		$this->clsComExcel->SetCellsFontSize('A' . $psrow, 'O' . $psrow, false, 7);
		$this->clsComExcel->SetCellsFontSize('A' . ($psrow + 2), 'O' . ($psrow + 2), false, 7);

		//折り返して全体を表示
		$this->clsComExcel->SetCellsWrap('A'. ($psrow + 2), 'I'. ($psrow + 2), true);
		$this->clsComExcel->SetCellsWrap('K'. ($psrow + 2), 'K'. ($psrow + 2), true);

		//背景色設定
		$this->clsComExcel->SetCellsBackColor('A' . ($psrow + 2), 'O' . ($psrow + 2), '00ffff');

		//罫線
		$this->clsComExcel->SetCellsLine('A' . ($psrow + 2), 'O' . ($psrow + 2), PHPExcel_Style_Border::BORDER_THIN, 4);

		//太線
		$this->clsComExcel->SetCellsLine('F' . ($psrow + 2), 'F' . ($psrow + 2), PHPExcel_Style_Border::BORDER_MEDIUM, 2);
		$this->clsComExcel->SetCellsLine('F' . ($psrow + 2), 'F' . ($psrow + 2), PHPExcel_Style_Border::BORDER_MEDIUM, 3);

		//右寄せ
		$this->clsComExcel->SetCellsAlign('O' . $psrow, 'O' . $psrow, 2);

		//中央寄せ
		$this->clsComExcel->SetCellsAlign('A' . ($psrow + 2), 'O' . ($psrow + 2), 1);

		$toymd = "";
		if(isset($arrPram["toymd"]) && !empty($arrPram["toymd"])) { $toymd = $arrPram["toymd"] . "号"; }
		$arrData = array('O' . $psrow  => $arrPram["fromymd"] . "号～" . $toymd . "適用 " . $kyonm,
						 'A' . ($psrow + 2) => "ブロック", 
						 'D' . ($psrow + 2) => "配布",
						 'F' . ($psrow + 2) => "チラシ" . PHP_EOL . "部数",
						 'G' . ($psrow + 2) => "戸建" . PHP_EOL . "配布率",
						 'H' . ($psrow + 2) => "戸建",
						 'I' . ($psrow + 2) => "集合",
						 'J' . ($psrow + 2) => "その他",
						 'K' . ($psrow + 2) => "選別" . PHP_EOL . "不可",
						 'L' . ($psrow + 2) => "町名");

		$this->clsComExcel->SetCellValue($arrData);

		//--------------------
		// フッター情報設定
		//--------------------
		//行の高さ
		$this->clsComExcel->SetRowHeight($perow, 25.5);

		//セルの結合
		$this->clsComExcel->SetMergeCells('A'. $perow, 'F'. $perow);
		$this->clsComExcel->SetMergeCells('G'. $perow, 'I'. $perow);
		$this->clsComExcel->SetMergeCells('K'. $perow, 'O'. $perow);

		//フォントサイズ設定
		$this->clsComExcel->SetCellsFontSize('A' . $perow, 'J' . $perow, true, 12);
		$this->clsComExcel->SetCellsFontSize('K' . $perow, 'K' . $perow, false, 5);

		//折り返して全体を表示
		$this->clsComExcel->SetCellsWrap('K'. $perow, 'K'. $perow, true);

		$arrData = array('A' . $perow => '配布部数 ページ合計',
						 'G' . $perow => '=SUM(E' . $datarow . ':E' . $detailrow . ')',
						 'J' . $perow => '部',
						 'K' . $perow => $fmsg);

		$this->clsComExcel->SetCellValue($arrData);

		//改ページ情報設定
		$this->clsComExcel->SetBreakPage('A'. ($perow + 1), 'P'. ($perow + 1));
	}

	/**
	* ブロック表グループ小計情報出力処理
	* 
	* @param  $groupcd    グループコード
	* @param  $datarow    明細データ現在位置
	* @param  $grouprow   グループ開始行
	* @return 
	*/
    public function printBlockGroupExcel($groupcd, $datarow, $grouprow)
    {
		//セル結合
		$this->clsComExcel->SetMergeCells('B'. $datarow, 'C'. $datarow);

		//背景色設定
		$this->clsComExcel->SetCellsBackColor('B' . $datarow, 'B' . $datarow, '00ffff');

		//罫線
		$this->clsComExcel->SetCellsLine('A' . $datarow, 'O' . $datarow, PHPExcel_Style_Border::BORDER_THIN, 4);

		//太線
		$this->clsComExcel->SetCellsLine('F' . $datarow, 'F' . $datarow, PHPExcel_Style_Border::BORDER_MEDIUM, 2);
		$this->clsComExcel->SetCellsLine('F' . $datarow, 'F' . $datarow, PHPExcel_Style_Border::BORDER_MEDIUM, 3);

		//フォントサイズ設定
		$this->clsComExcel->SetCellsFontSize('A' . $datarow, 'E' . $datarow, false, 7);
		$this->clsComExcel->SetCellsFontSize('F' . $datarow, 'F' . $datarow, false, 10);
		$this->clsComExcel->SetCellsFontSize('G' . $datarow, 'K' . $datarow, false, 7);

		//フォーマット設定
		$this->clsComExcel->SetCellsNumberFormat('E' . $datarow, 'F' . $datarow, '#,##0');
		$this->clsComExcel->SetCellsNumberFormat('H' . $datarow, 'K' . $datarow, '#,##0');
		$this->clsComExcel->SetCellsNumberFormat('G' . $datarow, 'G' . $datarow, '0.0%');

		//グループ小計情報設定
		$arrData = array('B' . $datarow => $groupcd . "合計",
						 'F' . $datarow => '=SUM(H'. $datarow . ':K'. $datarow . ')',
						 'D' . $datarow => '□',
						 'E' . $datarow => '=IF(D'. $datarow . '="□","",F'. $datarow . ')',
						 'F' . $datarow => '=SUM(H'. $datarow . ':K'. $datarow . ')',
						 'G' . $datarow => '=IF(F'. $datarow . '=0,"",H'. $datarow . '/F'. $datarow . ')',
						 'H' . $datarow => '=SUM(H'. $grouprow . ':H'. ($datarow - 1) . ')',
						 'I' . $datarow => '=SUM(I'. $grouprow . ':I'. ($datarow - 1) . ')',
						 'J' . $datarow => '=SUM(J'. $grouprow . ':J'. ($datarow - 1) . ')',
						 'K' . $datarow => '=SUM(K'. $grouprow . ':K'. ($datarow - 1) . ')');

		//データ書き込み
		$this->clsComExcel->SetCellValue($arrData);
	}

	/**
	* 地区表出力処理
	* 
	* @param  $arrParam  パラメータ配列(kcd  :会社コード, arrcd  :掲載版コード  [カンマ区切り], fromymd :期間[開始], 
	*                                   blockcd:ブロックコード[カンマ区切り], areacd:エリアコード[カンマ区切り])
	* @return true:データあり、false:データなし
	*/
    public function printAreaExcel($arrPram)
    {
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlCirculation.php";
		require_once dirname(__FILE__) . "/../models/SqlMstSystem.php";
		require_once dirname(__FILE__) . "/../common/ComExcel.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsSqlCirculation = new SqlCirculation();
		$clsSqlMstSystem = new SqlMstSystem();
		$arrRet = array();
		$arrRetMsg = array();

		//アクセスログ出力処理
		$backtraces = debug_backtrace();
		$msg = $backtraces[1]['class'] . "：" . $backtraces[1]['function'] . "：" . "(" . $arrPram["kcd"] . ") " . $arrPram["scd"] . "：" . $arrPram["snm"] . " [AreaStartPrint]";
		$logFile = $clsComConst::ACCESS_LOG_PATH. '/'. strtr('#DT#.log', array('#DT#'=>date('Ymd')));
		$logger = new Zend_Log(new Zend_Log_Writer_Stream($logFile));
		$logger->log($msg, Zend_Log::INFO);

		//地区表情報検索
		$blnRet = $clsSqlCirculation->SelectCirculationArea($clsComConst::DB_KIKAN , $arrPram);

		if($blnRet) { $arrRet = $clsSqlCirculation->GetData(); }

		if(count($arrRet) > 0)
		{
			try
			{
				//フッターメッセージ検索
				$fmsg = "";
				$arrMstPram = array("kcd" => $arrPram["kcd"], "entry" => "BUSUUHYOU", "section" => "CHIKUHYOFOOTER");
				$blnRet = $clsSqlMstSystem->SelectSystemMsg($clsComConst::DB_KIKAN_SUB , $arrMstPram);
				if($blnRet) { $arrRetMsg = $clsSqlMstSystem->GetData(); }
				if(isset($arrRetMsg[0]["val"])) { $fmsg = $clsCommon->ConverDisp($arrRetMsg[0]["val"]); }

				//定数定義
				$maxcol = 10;							//カラム数
				$maxprow = 76;							//ページ最大行数
				$maxdrow = 71;							//明細行最大行数
				$startrow = 4;							//明細開始位置

				//変数定義
				$psrow = 1;								//ページ開始行
				$perow = $maxprow;						//ページ終了行
				$detailrow = $perow - 3;				//明細行
				$datarow = $startrow;					//明細データ現在位置
				$grouprow = $startrow;					//グループ開始行
				$subgrouprow = $startrow;				//サブグループ開始行
				$kyonm = $arrRet[0]["KYOTEN_NM"];		//拠点名
				$groupcd = $arrRet[0]["AREA_CD"];		//グループエリアコード
				$subgroupcd = $arrRet[0]["KUIKI_CD"];	//グループ区域コード
				$groupflg = $false;						//グループ印刷フラグ

				//Excel生成クラス
				$this->clsComExcel = new ComExcel($clsComConst::EXCEL_AREA_PATH, "area", "地区表");

				//ヘッダーフッター情報設定
				self::printAreaHeaderFooterExcel($arrPram, $kyonm, $psrow, $maxcol, $perow, $datarow, $detailrow, $fmsg);

				for ($i = 0 ; $i < count($arrRet); $i++)
				{
					if((int)$arrRet[$i]["CNT"] > $maxdrow) { throw new Exception("出力データ不正", $clsComConst::ERR_CODE_500); }

					if($subgroupcd != $arrRet[$i]["KUIKI_CD"])
					{
						//地区表サブグループ小計情報出力処理
						self::printAreaSubGroupExcel($subgroupcd, $datarow, $subgrouprow);

						$subgroupcd = $arrRet[$i]["KUIKI_CD"];
						$subgrouprow = $datarow + 1;
						$datarow += 1;

						if($groupcd != $arrRet[$i]["AREA_CD"])
						{
							//地区表グループ小計情報出力処理
							self::printAreaGroupExcel($groupcd, $datarow, $grouprow);

							$groupcd = $arrRet[$i]["AREA_CD"];
							$grouprow = $datarow + 1;
							$datarow += 1;
							$groupflg = true;
						}

						if($groupflg ||($kyonm != $arrRet[$i]["KYOTEN_NM"]) || ((int)$arrRet[$i]["CNT"] > ($detailrow - ($datarow + 1))))
						{
							$psrow += $maxprow;					//ページ開始行
							$perow += $maxprow;					//ページ終了行
							$detailrow = $perow - 3;			//明細行
							$datarow = $psrow + $startrow - 1;	//明細データ現在位置
							$subgrouprow = $datarow;			//サブグループ開始行
							$kyonm = $arrRet[$i]["KYOTEN_NM"];
							$groupflg = false;

							//ヘッダーフッター情報設定
							self::printAreaHeaderFooterExcel($arrPram, $kyonm, $psrow, $maxcol, $perow, $datarow, $detailrow, $fmsg);
						}
					}

					//罫線
					$this->clsComExcel->SetCellsLine('A' . $datarow, 'J' . $datarow, PHPExcel_Style_Border::BORDER_THIN, 4);
					$this->clsComExcel->SetCellsLine('B' . $datarow, 'B' . $datarow, PHPExcel_Style_Border::BORDER_DOTTED, 2);

					//フォーマット設定
					$this->clsComExcel->SetCellsNumberFormat('D' . $datarow, 'D' . $datarow, '#,##0');
					$this->clsComExcel->SetCellsNumberFormat('F' . $datarow, 'I' . $datarow, '#,##0');
					$this->clsComExcel->SetCellsNumberFormat('E' . $datarow, 'E' . $datarow, '0.0%');
					$this->clsComExcel->SetFitCellsFontSize('J' . $datarow, 'J' . $datarow);

					//明細情報設定
					$arrData = array('B' . $datarow => '□',
									 'C' . $datarow => '=IF(B'. $datarow . '="□","",D'. $datarow . ')',
									 'D' . $datarow => '=SUM(F'. $datarow . ':I'. $datarow . ')',
									 'E' . $datarow => '=IF(D'. $datarow . '=0,"",F'. $datarow . '/D'. $datarow . ')',
									 'F' . $datarow => $arrRet[$i]["KODATE_SU"],
									 'G' . $datarow => $arrRet[$i]["SHUGOU_SU"],
									 'H' . $datarow => $arrRet[$i]["SONOTA_SU"],
									 'I' . $datarow => $arrRet[$i]["SENBETU_FUKA_SU"],
									 'J' . $datarow => $arrRet[$i]["TIIKI_NM"]);

					//データ書き込み
					$this->clsComExcel->SetCellValue($arrData);

					//明細情報設定
					$arrData = array('A' . $datarow => $arrRet[$i]["TIIKI_CD"]);

					//データ書き込み
					$this->clsComExcel->SetCellStringValue($arrData);
					$datarow += 1;
				}

				//地区表サブグループ小計情報出力処理
				self::printAreaSubGroupExcel($subgroupcd, $datarow, $subgrouprow);

				$datarow += 1;

				//地区表グループ小計情報出力処理
				self::printAreaGroupExcel($groupcd, $datarow, $grouprow);

				//アクセスログ出力処理
				$backtraces = debug_backtrace();
				$msg = $backtraces[1]['class'] . "：" . $backtraces[1]['function'] . "：" . "(" . $arrPram["kcd"] . ") " . $arrPram["scd"] . "：" . $arrPram["snm"] . " [AreaEndPrint]";
				$logFile = $clsComConst::ACCESS_LOG_PATH. '/'. strtr('#DT#.log', array('#DT#'=>date('Ymd')));
				$logger = new Zend_Log(new Zend_Log_Writer_Stream($logFile));
				$logger->log($msg, Zend_Log::INFO);

				//Excel出力(2003形式)
				$this->clsComExcel->OutputExcel();
				exit;
			}
			catch (Exception $e)
			{
				throw new Exception($e->getMessage(), $clsComConst::ERR_CODE_500);
			}
		}
		else { return false; }

		return true;
	}

	/**
	* 地区表ヘッダーフッター情報出力処理
	* 
	* @param  $arrParam  パラメータ配列(kcd  :会社コード, arrcd  :掲載版コード  [カンマ区切り], fromymd :期間[開始], 
	*                                   blockcd:ブロックコード[カンマ区切り], areacd:エリアコード[カンマ区切り])
	* @param  $kyonm      拠点名 
	* @param  $psrow      ページ開始行
	* @param  $maxcol     カラム数
	* @param  $perow      ページ終了行
	* @param  $datarow    明細データ現在位置
	* @param  $detailrow  明細行
	* @param  $fmsg       フッター表示メッセージ
	* @return 
	*/
    public function printAreaHeaderFooterExcel($arrPram, $kyonm, $psrow, $maxcol, $perow, $datarow, $detailrow, $fmsg)
    {
		//--------------------
		// ヘッダー情報設定
		//--------------------
		//行の高さ
		$this->clsComExcel->SetRowHeight(($psrow + 2), 21);

		//セルの結合
		$this->clsComExcel->SetMergeCells('B'. ($psrow + 2), 'C'. ($psrow + 2));

		//背景色設定
		$this->clsComExcel->SetCellsBackColor('A' . ($psrow + 2), 'J' . ($psrow + 2), '00ffff');

		//折り返して全体を表示
		$this->clsComExcel->SetCellsWrap('A'. ($psrow + 2), 'J'. ($psrow + 2), true);

		//罫線
		$this->clsComExcel->SetCellsLine('A' . ($psrow + 2), 'J' . ($psrow + 2), PHPExcel_Style_Border::BORDER_THIN, 4);

		//右寄せ
		$this->clsComExcel->SetCellsAlign('J' . $psrow, 'J' . $psrow, 2);

		//中央寄せ
		$this->clsComExcel->SetCellsAlign('D' . ($psrow + 2), 'J' . ($psrow + 2), 1);

		$toymd = "";
		if(isset($arrPram["toymd"]) && !empty($arrPram["toymd"])) { $toymd = $arrPram["toymd"] . "号"; }
		$arrData = array('J' . $psrow  => $arrPram["fromymd"] . "号～" . $toymd . "適用 " . $kyonm,
						 'D' . ($psrow + 2) => "チラシ" . PHP_EOL . "部数", 
						 'E' . ($psrow + 2) => "戸建" . PHP_EOL . "配布率",
						 'F' . ($psrow + 2) => "戸建",
						 'G' . ($psrow + 2) => "集合",
						 'H' . ($psrow + 2) => "その他",
						 'I' . ($psrow + 2) => "選別" . PHP_EOL . "不可",
						 'J' . ($psrow + 2) => "町名");

		$this->clsComExcel->SetCellValue($arrData);

		//--------------------
		// フッター情報設定
		//--------------------
		//行の高さ
		$this->clsComExcel->SetRowHeight($perow, 42);

		//セルの結合
		$this->clsComExcel->SetMergeCells('A'. $perow, 'D'. $perow);
		$this->clsComExcel->SetMergeCells('E'. $perow, 'F'. $perow);
		$this->clsComExcel->SetMergeCells('H'. $perow, 'J'. $perow);

		//フォントサイズ設定
		$this->clsComExcel->SetCellsFontSize('A' . $perow, 'G' . $perow, true, 12);
		$this->clsComExcel->SetCellsFontSize('H' . $perow, 'H' . $perow, false, 5);

		$arrData = array('A' . $perow => '配布部数 ページ合計', 
						 'E' . $perow => '=SUM(C' . $datarow . ':C' . $detailrow . ')', 
						 'G' . $perow => '部', 
						 'H' . $perow => $fmsg);

		$this->clsComExcel->SetCellValue($arrData);

		//改ページ情報設定
		$this->clsComExcel->SetBreakPage('A'. ($perow + 1), 'K'. ($perow + 1));
	}

	/**
	* 地区表サブグループ小計情報出力処理
	* 
	* @param  $subgroupcd   サブグループコード 
	* @param  $datarow      明細データ現在位置
	* @param  $subgrouprow  サブグループ開始行
	* @return 
	*/
    public function printAreaSubGroupExcel($subgroupcd, $datarow, $subgrouprow)
    {
		//セル結合
		$this->clsComExcel->SetMergeCells('A'. $datarow, 'C'. $datarow);

		//背景色設定
		$this->clsComExcel->SetCellsBackColor('A' . $datarow, 'A' . $datarow, '00ffff');

		//罫線
		$this->clsComExcel->SetCellsLine('A' . $datarow, 'J' . $datarow, PHPExcel_Style_Border::BORDER_THIN, 4);

		//フォーマット設定
		$this->clsComExcel->SetCellsNumberFormat('C' . $datarow, 'D' . $datarow, '#,##0');
		$this->clsComExcel->SetCellsNumberFormat('F' . $datarow, 'I' . $datarow, '#,##0');
		$this->clsComExcel->SetCellsNumberFormat('E' . $datarow, 'E' . $datarow, '0.0%');

		//サブグループ小計情報設定
		$arrData = array('A' . $datarow => $subgroupcd . "合計",
						 'D' . $datarow => '=SUM(F'. $datarow . ':I'. $datarow . ')',
						 'E' . $datarow => '=IF(D'. $datarow . '=0,"",F'. $datarow . '/D'. $datarow . ')',
						 'F' . $datarow => '=SUM(F'. $subgrouprow . ':F'. ($datarow - 1) . ')',
						 'G' . $datarow => '=SUM(G'. $subgrouprow . ':G'. ($datarow - 1) . ')',
						 'H' . $datarow => '=SUM(H'. $subgrouprow . ':H'. ($datarow - 1) . ')',
						 'I' . $datarow => '=SUM(I'. $subgrouprow . ':I'. ($datarow - 1) . ')');

		//データ書き込み
		$this->clsComExcel->SetCellValue($arrData);
	}

	/**
	* 地区表グループ小計情報出力処理
	* 
	* @param  $groupcd     グループコード 
	* @param  $datarow     明細データ現在位置
	* @param  $grouprow    グループ開始行
	* @return 
	*/
    public function printAreaGroupExcel($groupcd, $datarow, $grouprow)
    {
		//セル結合
		$this->clsComExcel->SetMergeCells('A'. $datarow, 'C'. $datarow);

		//背景色設定
		$this->clsComExcel->SetCellsBackColor('A' . $datarow, 'A' . $datarow, '00ffff');

		//罫線
		$this->clsComExcel->SetCellsLine('A' . $datarow, 'J' . $datarow, PHPExcel_Style_Border::BORDER_THIN, 4);

		//フォーマット設定
		$this->clsComExcel->SetCellsNumberFormat('C' . $datarow, 'D' . $datarow, '#,##0');
		$this->clsComExcel->SetCellsNumberFormat('F' . $datarow, 'I' . $datarow, '#,##0');
		$this->clsComExcel->SetCellsNumberFormat('E' . $datarow, 'E' . $datarow, '0.0%');

		//グループ小計情報設定
		$arrData = array('A' . $datarow => $groupcd . "合計",
						 'D' . $datarow => '=SUM(F'. $datarow . ':I'. $datarow . ')',
						 'E' . $datarow => '=IF(D'. $datarow . '=0,"",F'. $datarow . '/D'. $datarow . ')',
						 'F' . $datarow => '=SUMIF(J'. $grouprow . ':J'. ($datarow - 1) . ',"<>",F'. $grouprow . ':F'. ($datarow - 1) . ')',
						 'G' . $datarow => '=SUMIF(J'. $grouprow . ':J'. ($datarow - 1) . ',"<>",G'. $grouprow . ':G'. ($datarow - 1) . ')',
						 'H' . $datarow => '=SUMIF(J'. $grouprow . ':J'. ($datarow - 1) . ',"<>",H'. $grouprow . ':H'. ($datarow - 1) . ')',
						 'I' . $datarow => '=SUMIF(J'. $grouprow . ':J'. ($datarow - 1) . ',"<>",I'. $grouprow . ':I'. ($datarow - 1) . ')');

		//データ書き込み
		$this->clsComExcel->SetCellValue($arrData);
	}

	/**
	* 担当地区表(CSV)出力処理
	* 
	* @param  $arrParam  パラメータ配列(kcd  :会社コード, arrcd  :掲載版コード  [カンマ区切り], fromymd :期間[開始],
	*                                   blockcd:ブロックコード[カンマ区切り], areacd:エリアコード[カンマ区切り])
	* @return true:データあり、false:データなし
	*/
    public function printAreaCsv($arrPram)
    {
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlCirculation.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsSqlCirculation = new SqlCirculation();
		$arrRet = array();

		//担当地区表(CSV)情報検索
		$blnRet = $clsSqlCirculation->SelectCirculationAreaCsv($clsComConst::DB_KIKAN , $arrPram);

		if($blnRet) { $arrRet = $clsSqlCirculation->GetData(); }

		if(count($arrRet) > 0)
		{
			//CSV出力処理
			$clsCommon->SetCsv("担当地区表", $arrRet, "掲載版, ブロック, 担当地区番号, 行政区, 町名１, 町名２, 町名３, 町名４, 戸建, 集合, その他, 選別不可, 戸数合計");
			exit();
		}
		else { return false; }

		return true;
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
		if(!$clsCommon->ChkAccess($token, '', '', $clsComConst::PGID_ANALYSIS_RHISTORY))
		{ throw new Exception("", $clsComConst::ERR_CODE_403); }

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
		$this->view->List = $clsCommon->GetMenuList();
		$this->view->SubList = $clsCommon->GetSubMenuList();
    }
}
