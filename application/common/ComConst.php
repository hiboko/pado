<?php

/**
 * ComConst for class
 *
 * 共通定義クラス
 *
 * @category   ComConst
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class ComConst 
{
	/**********************************************************************************************
	 * URL
	 **********************************************************************************************/
	//HOME URL
	const HOME_URL = "http://kikansubtest.pado.jp:8081/";
	//LOGIN URL
	const LOGIN_URL = "http://kikansubtest.pado.jp:8081/login/";
	//受注履歴
	const ANALYSIS_RHISTORY_URL = "http://kikansubtest.pado.jp:8081/analysis/receivehistory/";
	//社員検索
	const CDIALOG_EMPLOYEE_URL = "http://kikansubtest.pado.jp:8081/commondialog/employee/";
	//顧客検索
	const CDIALOG_CLIENT_URL = "http://kikansubtest.pado.jp:8081/commondialog/client/";
	//コード情報検索
	const CDIALOG_CODE_URL = "http://kikansubtest.pado.jp:8081/commondialog/code/";
	//部数表
	const ANALYSIS_CIRCULATION_URL = "http://kikansubtest.pado.jp:8081/analysis/circulation/";
	//部数表詳細
	const ANALYSIS_CIRCULATION_DITAIL_URL = "http://kikansubtest.pado.jp:8081/analysis/circulationdetail/";
	//商売名人リスト
	const PRINTCSV_BUSINESSNAME_LIST_URL = "http://kikansubtest.pado.jp:8081/printcsv/businessnamelist/";
	//入金精算出力
	const PRINTCSV_RECIVE_ADJUSTEDAMOUNT_URL = "http://kikansubtest.pado.jp:8081/printcsv/receiveadjustedamount/";
	//カスタマーセンター問合せ集計出力
	const PRINTCSV_CUSTOMERSERVICE_TOTAL_URL = "http://kikansubtest.pado.jp:8081/printcsv/customerservicetotal/";
	//S版管理表出力
	const PRINTCSV_S_EDITION_LIST_URL = "http://kikansubtest.pado.jp:8081/printcsv/seditionlist/";
	//事例登録状況出力
	const PRINTCSV_CASE_REGISTERSITUATION_URL = "http://kikansubtest.pado.jp:8081/printcsv/caseregistersituation/";
	//ぱどPO精算状況出力
	const PRINTCSV_POINT_ADJUSTEDSITUATION_URL = "http://kikansubtest.pado.jp:8081/printcsv/pointadjustedsituation/";
	//ぱどPO精算状況出力(旧システム)
	const PRINTCSV_OLD_POINT_ADJUSTEDSITUATION_URL = "http://kikansubtest.pado.jp:8081/printcsv/oldpointadjustedsituation/";
	//前受精算状況出力
	const PRINTCSV_ADVANCE_ADJUSTED_SITUATION_URL = "http://kikansubtest.pado.jp:8081/printcsv/advanceadjustedsituation/";

	/**********************************************************************************************
	 * DB
	 **********************************************************************************************/
	//VMTEST01
	const DB_VMTEST01 = "VMTEST01";
	//VMTEST02
	const DB_VMTEST02 = "VMTEST02";
	//DB_KIKAN
	const DB_KIKAN = "00kikandb01";
	//DB_KIKAN_SUB
	const DB_KIKAN_SUB = "kikansubdb";

	/**********************************************************************************************
	 * ログ
	 **********************************************************************************************/
	//アクセスログ出力場所
	const ACCESS_LOG_PATH = "/kikan/subtest/log/accesslog";
	//エラーログ出力場所
	const ERROR_LOG_PATH = "/kikan/subtest/log/errlog";

	/**********************************************************************************************
	 * ファイルパス
	 **********************************************************************************************/
	//ブロック表テンプレート場所
	const EXCEL_BLOCK_PATH = "/kikan/subtest/application/layouts/scripts/block.xls";
	//地区表テンプレート場所
	const EXCEL_AREA_PATH = "/kikan/subtest/application/layouts/scripts/area.xls";

	/**********************************************************************************************
	 * 帳票最大行ページ数
	 **********************************************************************************************/
	//地区表最大ページ数
	const EXCEL_AREA_MAX_PAGE = 250;

	/**********************************************************************************************
	 * メニューID
	 **********************************************************************************************/
	//受注履歴
	const PGID_ANALYSIS_RHISTORY = 1;
	//部数表
	const PGID_ANALYSIS_CIRCULATION = 2;
	//商売名人リスト
	const PGID_PRINTCSV_BUSINESSNAME_LIST = 3;
	//入金精算出力
	const PGID_PRINTCSV_RECIVE_ADJUSTEDAMOUNT = 4;
	//カスタマーセンター問合せ集計出力
	const PGID_PRINTCSV_CUSTOMERSERVICE_TOTAL = 5;
	//S版管理表出力
	const PGID_PRINTCSV_S_EDITION_LIST = 6;
	//事例登録状況出力
	const PGID_PRINTCSV_CASE_REGISTERSITUATION = 7;
	//ぱどPO精算状況出力
	const PGID_PRINTCSV_POINT_ADJUSTEDSITUATION = 8;
	//ぱどPO精算状況出力(旧システム)
	const PGID_PRINTCSV_OLD_POINT_ADJUSTEDSITUATION = 9;
	//前受精算状況出力
	const PGID_PRINTCSV_ADVANCE_ADJUSTED_SITUATION = 10;

	/**********************************************************************************************
	 * 判定コード
	 **********************************************************************************************/
	//大分類
	const CODE_DAIBUNRUI = 1;
	//中分類
	const CODE_CHUBUNRUI = 2;
	//受注種別
	const CODE_SHUBETU = 3;
	//掲載版
	const CODE_KEISAIHAN = 4;

	//契約主
	const CODE_CONTRACT = 1;
	//請求先
	const CODE_CLAIMANT = 2;
	//広告主
	const CODE_ADVERTISER = 3;

	//自社取引担当者
	const CODE_CONNECT_EMPLOYEE = 1;
	//自社請求担当者
	const CODE_CLAIM_EMPLOYEE = 2;
	//顧客検索 営業担当者
	const CODE_CLIENT_EMPLOYEE = 3;

	//部数表EXCEL出力コード
	const CODE_CIRCULATION_EXCEL = 'BUSUHYO_XLS_KEISAIHAN_CD';
	//部数表CSV出力コード
	const CODE_CIRCULATION_CSV = 'ORKM_KEISAIHAN_CD';

	//ダイアログコード
	const CODE_DIALOG = 1;

	/**********************************************************************************************
	 * エラーコード
	 **********************************************************************************************/
	//正常
	const ERR_CODE_OK = 200;
	//DB接続エラー
	const ERR_CODE_101 = 101;
	//DB実行エラー
	const ERR_CODE_102 = 102;
	//リクエストエラー
	const ERR_CODE_400 = 400;
	//アクセスエラー
	const ERR_CODE_403 = 403;
	//NOT FOUNDエラー
	const ERR_CODE_404 = 404;
	//ブラウザエラー
	const ERR_CODE_405 = 405;
	//アプリケーションエラー
	const ERR_CODE_500 = 500;

	/**********************************************************************************************
	 * エラーメッセージ
	 **********************************************************************************************/
	//アプリケーションエラー
	const ERR_MSG_COUNT_OVER = "検索件数が1000件を超えました。";
	//ログインエラー
	const ERR_MSG_LOGIN = "ログイン情報が不正です。";
	//パスワードエラー
	const ERR_MSG_PWD = "パスワードの有効期限が過ぎています。新基幹システムよりパスワードの変更を行ってください。";
	//帳票出力エラー
	const ERR_MSG_CSV = "出力データがありません。";

	/**********************************************************************************************
	 * 有効期限
	 **********************************************************************************************/
	//パスワード有効日数
	const ERR_PWD_LIMIT_DAY = 90;
	//セッション有効期間(30分)
	const ERR_SESSION_LIMIT = 1800;
}

