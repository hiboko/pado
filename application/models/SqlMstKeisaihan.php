<?php

/**
 * SqlMstKeisaihan for class
 *
 * 掲載版情報 SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlMstKeisaihan
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
	 * 掲載版名取得
	 */
	public function GetKeisaihanName()
	{
		$ret = "";

		if(count($this->RetData) > 0)
		{
			$ret = $this->RetData[0]["codename"];
		}

		return $ret;
	}

	/**
	 * 掲載版情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(serch:検索項目, kcd:会社コード)
	 * @return 
	 */
	public function SelectMstKeisaihan($db, $arrParam = array())
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

			$sql = "SELECT KEISAIHAN_CD AS codeid ";
			$sql .= "     ,KEISAIHAN_NM AS codename ";
			$sql .= " FROM ADMS_MST_KEISAIHAN WITH(NOLOCK) ";
			$sql .= "WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "  AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			if(isset($arrParam["serch"]) && !empty($arrParam["serch"]))
			{
				$sql .= "   AND (KEISAIHAN_CD like '%" . $arrParam["serch"] . "%'";
				$sql .= "    OR  KEISAIHAN_NM like '%" . $arrParam["serch"] . "%')";
			}
			$sql .= "ORDER BY KEISAIHAN_CD ";

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

