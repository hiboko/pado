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
	* 変数定義
	*/
	public $CompanyCd = "";

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

	public function preDispatch()
	{
		/* ディスパッチ直前に呼ばれるメソッド */
	}

	public function postDispatch()
	{
		/* ディスパッチで呼ばれたメインの処理終了後に呼ばれるメソッド */
	}

	/**
	* 会社マスタ情報処理
	* 
	* @param
	* @return 
	*/
	public function companyAction()
	{
		//ファイル読み込み
		require_once dirname(__FILE__) . "/../models/SqlMaster.php";

		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();
		$clsParamCheck = new ParamCheck();
		$clsSqlMaster = new SqlMaster();
		$arrErr = array();

		//アクセスチェック
		if(!$clsCommon->ChkBrowser()) { throw new Exception("", $clsComConst::ERR_CODE_500); }

		//パラメータ設定
		$cd = $this->getRequest()->getParam('cd');

		//パラメータチェック
		$clsParamCheck->ChkMust($cd, "会社コード");
		$clsParamCheck->ChkNumeric($cd, "会社コード");
		$arrErr = $clsParamCheck->GetErrMsg();

		if(count($arrErr) > 0)
		{
			foreach($arrErr as $value) { $msg .= $value  ."</br>" ; }
			$this->view->ErrMsg = $msg;
		}

		//パラメータ設定
		$arrParam = array("cd" => $cd);

		//社員マスタ検索
		$blnRet = $clsSqlMaster->SELECT_M_TSYAIN($clsComConst::DB_VMTEST02, $arrParam);

		if(!$blnRet) { throw new Exception("", $clsComConst::ERR_CODE_500); }

		$arrRet = $clsSqlMaster->GetData();

		if(count($arrRet) > 0)
		{
			//設定処理
			$this->view->CompanyCd = $arrRet[0]["SYAIN_NM"];
		}
	}

}

