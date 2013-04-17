<?php

/**
 * SqlCirculation for class
 *
 * 部数表 SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlCirculation
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
	 * 部数表掲載版情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, type:種別コード)
	 * @return ture:成功、false:失敗
	 */
	public function SelectCirculationKeisaihan($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectReceiveHistory', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["type"]) || empty($arrParam["type"]))
			{ throw new Exception('SelectReceiveHistory', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT B.KEISAIHAN_CD AS cd, B.KEISAIHAN_NM AS name ";
			$sql .= "  FROM dbo.DR_FUNC_COMMA_SPLITTER( ";
			$sql .= "                                  (SELECT VALUE FROM BC_MST_SYSTEM WITH(NOLOCK) ";
			$sql .= "                                    WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "                                      AND VALUE_NM = '" . $arrParam["type"] . "'";
			$sql .= "                                      AND BUNRUI_NM = 'SD' AND ISNULL(DEL_FLG, 0) = 0 ) ";
			$sql .= "                                 ) AS A ";
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KEISAIHAN_CD, KEISAIHAN_NM ";
			$sql .= "        FROM ADMS_MST_KEISAIHAN WITH(NOLOCK) ";
			$sql .= "       WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "         AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "  ) AS B ";
			$sql .= "    ON A.TMP_NO = B.KEISAIHAN_CD ";

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

	/**
	 * 部数表期間情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, arrcd:掲載版コード[カンマ区切り])
	 * @return ture:成功、false:失敗
	 */
	public function SelectCirculationYmd($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		$date = "2013/01/01";

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectReceiveHistory', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["arrcd"]) || empty($arrParam["arrcd"]))
			{ throw new Exception('SelectReceiveHistory', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT MAX(A.START_YMD) AS name FROM ";
			$sql .= " ( ";
			$sql .= "  SELECT MAX(START_YMD) AS START_YMD FROM ADMS_MST_KUIKI WITH(NOLOCK) ";
			$sql .= "   WHERE KAISHA_CD = '" . $arrParam["kcd"] . "' AND KEISAIHAN_CD IN (" . $arrParam["arrcd"] . ") ";
			$sql .= "     AND ISNULL(DEL_FLG, 0) = 0 AND START_YMD <= GETDATE() ";
			$sql .= "   UNION ";
			$sql .= "  SELECT MAX(START_YMD) AS START_YMD FROM ADMS_MST_TIIKI WITH(NOLOCK) ";
			$sql .= "   WHERE KAISHA_CD = '999' AND KEISAIHAN_CD IN (" . $arrParam["arrcd"] . ") ";
			$sql .= "     AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0  AND START_YMD <= GETDATE() ";
			$sql .= "   UNION ";
			$sql .= "  SELECT MAX(START_YMD) AS START_YMD FROM PADO_MAREA_START_YMD WITH(NOLOCK) ";
			$sql .= "   WHERE KAISHA_CD = '" . $arrParam["kcd"] . "' AND KEISAIHAN_CD IN (" . $arrParam["arrcd"] . ") ";
			$sql .= "     AND ISNULL(DEL_FLG, 0) = 0 AND START_YMD <= GETDATE() ";
			$sql .= " ) AS A ";
			$sql .= "  UNION ";
			$sql .= " SELECT START_YMD AS name FROM ADMS_MST_KUIKI WITH(NOLOCK) ";
			$sql .= "  WHERE KAISHA_CD = '" . $arrParam["kcd"] . "' AND KEISAIHAN_CD IN (" . $arrParam["arrcd"] . ") ";
			$sql .= "    AND ISNULL(DEL_FLG, 0) = 0 AND START_YMD >= GETDATE() ";
			$sql .= "  GROUP BY START_YMD ";
			$sql .= "  UNION ";
			$sql .= " SELECT START_YMD AS name FROM ADMS_MST_TIIKI WITH(NOLOCK) ";
			$sql .= "  WHERE KAISHA_CD = '999' AND KEISAIHAN_CD IN (" . $arrParam["arrcd"] . ") ";
			$sql .= "    AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0  AND START_YMD >= GETDATE() ";
			$sql .= "  GROUP BY START_YMD ";
			$sql .= "  UNION ";
			$sql .= " SELECT START_YMD AS name FROM PADO_MAREA_START_YMD WITH(NOLOCK) ";
			$sql .= "  WHERE KAISHA_CD = '" . $arrParam["kcd"] . "' AND KEISAIHAN_CD IN (" . $arrParam["arrcd"] . ") ";
			$sql .= "    AND ISNULL(DEL_FLG, 0) = 0 AND START_YMD >= GETDATE() ";
			$sql .= "  GROUP BY START_YMD ";
			$sql .= "  ORDER BY name ";

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

	/**
	 * 部数表お知らせ情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード)
	 * @return ture:成功、false:失敗
	 */
	public function SelectCirculationMsg($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectReceiveHistory', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT VALUE FROM M_TSYSTEM ";
			$sql .= " WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "   AND ENTRY = 'BUSUUHYOU' AND SECTION = 'MESSAGE' ";

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

