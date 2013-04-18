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
			{ throw new Exception('SelectCirculationKeisaihan', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["type"]) || empty($arrParam["type"]))
			{ throw new Exception('SelectCirculationKeisaihan', $clsComConst::ERR_CODE_400); }

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
			{ throw new Exception('SelectCirculationYmd', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["arrcd"]) || empty($arrParam["arrcd"]))
			{ throw new Exception('SelectCirculationYmd', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT '' AS cd, MAX(A.START_YMD) AS name FROM ";
			$sql .= " ( ";
			$sql .= "  SELECT MAX(START_YMD) AS START_YMD FROM ADMS_MST_KUIKI WITH(NOLOCK) ";
			$sql .= "   WHERE KAISHA_CD = '" . $arrParam["kcd"] . "' AND KEISAIHAN_CD IN (" . $arrParam["arrcd"] . ") ";
			//$sql .= "     AND ISNULL(DEL_FLG, 0) = 0 AND START_YMD <= GETDATE() ";
			$sql .= "     AND ISNULL(DEL_FLG, 0) = 0 AND START_YMD <= '" . $date . "'";
			$sql .= "   UNION ";
			$sql .= "  SELECT MAX(START_YMD) AS START_YMD FROM ADMS_MST_TIIKI WITH(NOLOCK) ";
			$sql .= "   WHERE KAISHA_CD = '999' AND KEISAIHAN_CD IN (" . $arrParam["arrcd"] . ") ";
			//$sql .= "     AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0  AND START_YMD <= GETDATE() ";
			$sql .= "     AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0  AND START_YMD <= '" . $date . "'";
			$sql .= "   UNION ";
			$sql .= "  SELECT MAX(START_YMD) AS START_YMD FROM PADO_MAREA_START_YMD WITH(NOLOCK) ";
			$sql .= "   WHERE KAISHA_CD = '" . $arrParam["kcd"] . "' AND KEISAIHAN_CD IN (" . $arrParam["arrcd"] . ") ";
			//$sql .= "     AND ISNULL(DEL_FLG, 0) = 0 AND START_YMD <= GETDATE() ";
			$sql .= "     AND ISNULL(DEL_FLG, 0) = 0 AND START_YMD <= '" . $date . "'";
			$sql .= " ) AS A ";
			$sql .= "  UNION ";
			$sql .= " SELECT '' AS cd, START_YMD AS name FROM ADMS_MST_KUIKI WITH(NOLOCK) ";
			$sql .= "  WHERE KAISHA_CD = '" . $arrParam["kcd"] . "' AND KEISAIHAN_CD IN (" . $arrParam["arrcd"] . ") ";
			//$sql .= "    AND ISNULL(DEL_FLG, 0) = 0 AND START_YMD >= GETDATE() ";
			$sql .= "    AND ISNULL(DEL_FLG, 0) = 0 AND START_YMD >= '" . $date . "'";
			$sql .= "  GROUP BY START_YMD ";
			$sql .= "  UNION ";
			$sql .= " SELECT '' AS cd, START_YMD AS name FROM ADMS_MST_TIIKI WITH(NOLOCK) ";
			$sql .= "  WHERE KAISHA_CD = '999' AND KEISAIHAN_CD IN (" . $arrParam["arrcd"] . ") ";
			//$sql .= "    AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0  AND START_YMD >= GETDATE() ";
			$sql .= "    AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0  AND START_YMD >= '" . $date . "'";
			$sql .= "  GROUP BY START_YMD ";
			$sql .= "  UNION ";
			$sql .= " SELECT '' AS cd, START_YMD AS name FROM PADO_MAREA_START_YMD WITH(NOLOCK) ";
			$sql .= "  WHERE KAISHA_CD = '" . $arrParam["kcd"] . "' AND KEISAIHAN_CD IN (" . $arrParam["arrcd"] . ") ";
			//$sql .= "    AND ISNULL(DEL_FLG, 0) = 0 AND START_YMD >= GETDATE() ";
			$sql .= "    AND ISNULL(DEL_FLG, 0) = 0 AND START_YMD >= '" . $date . "'";
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
			{ throw new Exception('SelectCirculationMsg', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT VALUE AS val FROM M_TSYSTEM ";
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

	/**
	 * 部数表詳細情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, arrcd:掲載版コード[カンマ区切り], toymd:期間[開始], fromymd:期間[終了])
	 * @return ture:成功、false:失敗
	 */
	public function SelectCirculationDetail($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectCirculationDetail', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["arrcd"]) || empty($arrParam["arrcd"]))
			{ throw new Exception('SelectCirculationDetail', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["toymd"]) || empty($arrParam["toymd"]))
			{ throw new Exception('SelectCirculationDetail', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT A.RYUTU_KYOTEN_CD + ':' + B.CD_NM AS base ";
			$sql .= "      ,A.BLOCK_NM AS block ";
			$sql .= "      ,C.AREA_CD  + ':' +  C.AREA_NM AS area ";
			$sql .= "      ,A.RYUTU_KYOTEN_CD AS kyocd ";
			$sql .= "      ,A.BLOCK_CD AS blcd ";
			$sql .= "      ,C.AREA_CD AS areacd ";
			$sql .= "  FROM ADMS_MST_BLOCK AS A WITH(NOLOCK) ";
			$sql .= " INNER JOIN AD_VIEW_RYUTU_KYOTEN AS B WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.RYUTU_KYOTEN_CD = B.CD_KEY ";
			$sql .= "   AND ISNULL(B.DEL_FLG, 0) = 0 AND ISNULL(B.MUKOU_FLG, 0) = 0 ";
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, KEISAIHAN_CD, BLOCK_CD, AREA_CD, AREA_NM ";
			$sql .= "        FROM ADMS_MST_AREA WITH(NOLOCK) ";
			$sql .= "       WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "         AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "         AND START_YMD >= '" . $arrParam["toymd"] . "'";
			if(isset($arrParam["fromymd"]) || !empty($arrParam["fromymd"]))
			{
				$sql .= "         AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			}
			$sql .= "  ) AS C ";
			$sql .= "    ON A.KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND A.KEISAIHAN_CD = C.KEISAIHAN_CD ";
			$sql .= "   AND A.BLOCK_CD = C.BLOCK_CD ";
			$sql .= " WHERE A.KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "   AND A.KEISAIHAN_CD IN (" . $arrParam["arrcd"] . ") ";
			$sql .= "   AND ISNULL(A.DEL_FLG, 0) = 0 AND ISNULL(A.MUKOU_FLG, 0) = 0 ";
			$sql .= " GROUP BY A.RYUTU_KYOTEN_CD, B.CD_NM , A.BLOCK_CD, A.BLOCK_NM, C.AREA_CD, C.AREA_NM ";
			$sql .= " ORDER BY A.RYUTU_KYOTEN_CD, A.BLOCK_CD, C.AREA_CD ";

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

