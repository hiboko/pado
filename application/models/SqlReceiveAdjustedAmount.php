<?php

/**
 * SqlReceiveAdjustedAmount for class
 *
 * 入金精算出力 SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlReceiveAdjustedAmount
{
	/**
	 * 返却結果配列
	 */
	private $RetData;

	/**
	 * 返却結果取得
	 */
	public function GetData()
	{
		return $this->RetData;
	}

	/**
	 * 入金精算出力情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, sstartdate:請求残開始日, nstartdate:入金残開始日)
	 * @return ture:成功、false:失敗
	 */
	public function SelectReceiveAdjustedAmount($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectReceiveAdjustedAmount', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["sstartdate"]) || empty($arrParam["sstartdate"]))
			{ throw new Exception('SelectReceiveAdjustedAmount', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["nstartdate"]) || empty($arrParam["nstartdate"]))
			{ throw new Exception('SelectReceiveAdjustedAmount', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " dbo.DR_SEIKYU_NYUKIN_ZAN_SEARCH '" . $arrParam["kcd"] . "', '" . $arrParam["sstartdate"] . "', '" . $arrParam["nstartdate"] . "', '', '' ";

			//クエリー実行
			$blnRet = $clsModelBase->Query($sql);

			//クエリー実行エラー
			if(!$blnRet) { throw new Exception($sql, $clsComConst::ERR_CODE_102); }

			//データ取得
			$this->RetData = $clsModelBase->GetData();

			//接続解除
			$clsModelBase->Close();

		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage(), $clsComConst::ERR_CODE_500);
		}

		return true;
	}
}

