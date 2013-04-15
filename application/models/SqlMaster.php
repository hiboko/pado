<?php

/**
 * SqlMaster for class
 *
 * SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlMaster
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
	 * 会社マスタ取得
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(cd:会社コード)
	 * @return 
	 */
	public function SELECT_M_TSYAIN($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();
		
		try
		{
			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT ";
			$sql .= "       SYAIN_CD ";
			$sql .= "      ,SYAIN_NM ";
			$sql .= "      ,SYAIN_KANA_NM ";
			$sql .= "  FROM M_TSYAIN ";
			$sql .= " WHERE SYAIN_CD = '" . $arrParam["cd"] . "'";

			//クエリー実行
			$blnRet = $clsModelBase->Query($sql, $arrParam);

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

