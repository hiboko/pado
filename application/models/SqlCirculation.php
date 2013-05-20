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
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, report:掲載版コード)
	 * @return ture:成功、false:失敗
	 */
	public function SelectCirculationYmd($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectCirculationYmd', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["report"]) || empty($arrParam["report"]))
			{ throw new Exception('SelectCirculationYmd', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }
			$sql = " SELECT '' AS cd, MAX(A.START_YMD) AS name FROM ";
			$sql .= " ( ";
			$sql .= "  SELECT MAX(START_YMD) AS START_YMD FROM ADMS_MST_KUIKI WITH(NOLOCK) ";
			$sql .= "   WHERE KAISHA_CD = '" . $arrParam["kcd"] . "' AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "     AND ISNULL(DEL_FLG, 0) = 0 AND START_YMD <= GETDATE() ";

			$sql .= "   UNION ";
			$sql .= "  SELECT MAX(START_YMD) AS START_YMD FROM ADMS_MST_TIIKI WITH(NOLOCK) ";
			$sql .= "   WHERE KAISHA_CD = '999' AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "     AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0  AND START_YMD <= GETDATE() ";

			$sql .= "   UNION ";
			$sql .= "  SELECT MAX(START_YMD) AS START_YMD FROM PADO_MAREA_START_YMD WITH(NOLOCK) ";
			$sql .= "   WHERE KAISHA_CD = '" . $arrParam["kcd"] . "' AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "     AND ISNULL(DEL_FLG, 0) = 0 AND START_YMD <= GETDATE() ";

			$sql .= " ) AS A ";
			$sql .= "  UNION ";
			$sql .= " SELECT '' AS cd, START_YMD AS name FROM ADMS_MST_KUIKI WITH(NOLOCK) ";
			$sql .= "  WHERE KAISHA_CD = '" . $arrParam["kcd"] . "' AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "    AND ISNULL(DEL_FLG, 0) = 0 AND START_YMD >= GETDATE() ";
			$sql .= "  GROUP BY START_YMD ";
			
			$sql .= "  UNION ";
			$sql .= " SELECT '' AS cd, START_YMD AS name FROM ADMS_MST_TIIKI WITH(NOLOCK) ";
			$sql .= "  WHERE KAISHA_CD = '999' AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "    AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0  AND START_YMD >= GETDATE() ";
			$sql .= "  GROUP BY START_YMD ";
			
			$sql .= "  UNION ";
			$sql .= " SELECT '' AS cd, START_YMD AS name FROM PADO_MAREA_START_YMD WITH(NOLOCK) ";
			$sql .= "  WHERE KAISHA_CD = '" . $arrParam["kcd"] . "' AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
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
	 * 部数表詳細情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, report:掲載版コード, fromymd:期間[開始])
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
			if(!isset($arrParam["report"]) || empty($arrParam["report"]))
			{ throw new Exception('SelectCirculationDetail', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["fromymd"]) || empty($arrParam["fromymd"]))
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
			$sql .= "  FROM ";

			// ブロックマスタ
			$sql .= "  (";
			$sql .= "      SELECT KAISHA_CD, KEISAIHAN_CD, RYUTU_KYOTEN_CD, BLOCK_CD, BLOCK_NM ";
			$sql .= "        FROM ADMS_MST_BLOCK WITH(NOLOCK) ";
			$sql .= "       WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "         AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "         AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "         AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_BLOCK WITH(NOLOCK) ";
			$sql .= "                            WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "                              AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                              AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                              AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                            GROUP BY KEISAIHAN_CD,KAISHA_CD) ";
			$sql .= "  ) AS A ";

			// 拠点マスタ
			$sql .= " INNER JOIN AD_VIEW_RYUTU_KYOTEN AS B WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.RYUTU_KYOTEN_CD = B.CD_KEY ";
			$sql .= "   AND ISNULL(B.DEL_FLG, 0) = 0 AND ISNULL(B.MUKOU_FLG, 0) = 0 ";

			// エリアマスタ
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, KEISAIHAN_CD, BLOCK_CD, AREA_CD, AREA_NM ";
			$sql .= "        FROM ADMS_MST_AREA WITH(NOLOCK) ";
			$sql .= "       WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "         AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "         AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "         AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_AREA WITH(NOLOCK) ";
			$sql .= "                            WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "                              AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                              AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                              AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                            GROUP BY KAISHA_CD, KEISAIHAN_CD) ";
			$sql .= "  ) AS C ";
			$sql .= "    ON A.KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND A.KEISAIHAN_CD = C.KEISAIHAN_CD ";
			$sql .= "   AND A.BLOCK_CD = C.BLOCK_CD ";

			$sql .= " ORDER BY A.RYUTU_KYOTEN_CD, A.BLOCK_CD, C.AREA_CD ";

			//クエリー実行
			$blnRet = $clsModelBase->Query($sql);

			//クエリー実行エラー
			if(!$blnRet) { throw new Exception($sql, $clsComConst::ERR_CODE_102); }

			//データ取得
			if(count($this->RetData) == 0) { $this->RetData = $clsModelBase->GetData(); }
			else { $this->RetData = array_push($clsModelBase->GetData()); }

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
	 * ブロック表情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd  :会社コード, report  :掲載版コード, fromymd :期間[開始], 
	 *                                   blockcd:ブロックコード[カンマ区切り], areacd:エリアコード[カンマ区切り])
	 * @return ture:成功、false:失敗
	 */
	public function SelectCirculationBlock($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectCirculationBlock', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["report"]) || empty($arrParam["report"]))
			{ throw new Exception('SelectCirculationBlock', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["fromymd"]) || empty($arrParam["fromymd"]))
			{ throw new Exception('SelectCirculationBlock', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT A.RYUTU_KYOTEN_CD AS KYOTEN_CD ";
			$sql .= "      ,B.CD_NM AS KYOTEN_NM ";
			$sql .= "      ,A.BLOCK_CD ";
			$sql .= "      ,A.BLOCK_NM ";
			$sql .= "      ,C.AREA_CD ";
			$sql .= "      ,D.KUIKI_CD ";
			$sql .= "      ,D2.CNT ";
			$sql .= "      ,E.KODATE_SU ";
			$sql .= "      ,E.SHUGOU_SU ";
			$sql .= "      ,E.SONOTA_SU ";
			$sql .= "      ,E.SENBETU_FUKA_SU ";
			$sql .= "      ,D.KUIKI_NM1 ";
			$sql .= "      ,D.KUIKI_NM2 ";
			$sql .= "      ,D.KUIKI_NM3 ";
			$sql .= "      ,D.KUIKI_NM4 ";

			// ブロックマスタ
			$sql .= "  FROM ADMS_MST_BLOCK AS A WITH(NOLOCK) ";

			// 拠点マスタ
			$sql .= " INNER JOIN AD_VIEW_RYUTU_KYOTEN AS B WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.RYUTU_KYOTEN_CD = B.CD_KEY ";
			$sql .= "   AND ISNULL(B.DEL_FLG, 0) = 0 AND ISNULL(B.MUKOU_FLG, 0) = 0 ";

			// エリアマスタ
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, KEISAIHAN_CD, BLOCK_CD, AREA_CD, AREA_NM ";
			$sql .= "        FROM ADMS_MST_AREA WITH(NOLOCK) ";
			$sql .= "       WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "         AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "         AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "         AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_AREA WITH(NOLOCK) ";
			$sql .= "                            WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "                              AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                              AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                              AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                            GROUP BY KAISHA_CD, KEISAIHAN_CD) ";
			if(isset($arrParam["areacd"]) || !empty($arrParam["areacd"]))
			{
				$sql .= "         AND AREA_CD IN (" . $arrParam["areacd"] . ")";
			}
			$sql .= "  ) AS C ";
			$sql .= "    ON A.KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND A.KEISAIHAN_CD = C.KEISAIHAN_CD ";
			$sql .= "   AND A.BLOCK_CD = C.BLOCK_CD ";

			// 区域マスタ
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, KEISAIHAN_CD, BLOCK_CD, AREA_CD, KUIKI_CD, ";
			$sql .= "             KUIKI_NM1, KUIKI_NM2, KUIKI_NM3, KUIKI_NM4 ";
			$sql .= "        FROM ADMS_MST_KUIKI WITH(NOLOCK) ";
			$sql .= "       WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "         AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "         AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "         AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_KUIKI WITH(NOLOCK) ";
			$sql .= "                            WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "                              AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                              AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                              AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                            GROUP BY KAISHA_CD, KEISAIHAN_CD) ";
			$sql .= "  ) AS D ";
			$sql .= "    ON C.KAISHA_CD = D.KAISHA_CD ";
			$sql .= "   AND C.KEISAIHAN_CD = D.KEISAIHAN_CD ";
			$sql .= "   AND C.BLOCK_CD = D.BLOCK_CD ";
			$sql .= "   AND C.AREA_CD = D.AREA_CD ";

			// 区域データ数
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, KEISAIHAN_CD, BLOCK_CD, AREA_CD, COUNT(*) AS CNT ";
			$sql .= "        FROM ADMS_MST_KUIKI WITH(NOLOCK) ";
			$sql .= "       WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "         AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "         AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "         AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_KUIKI WITH(NOLOCK) ";
			$sql .= "                            WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "                              AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                              AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                              AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                            GROUP BY KAISHA_CD, KEISAIHAN_CD) ";
			$sql .= "       GROUP BY KAISHA_CD, KEISAIHAN_CD, BLOCK_CD, AREA_CD ";
			$sql .= "  ) AS D2 ";
			$sql .= "    ON C.KAISHA_CD = D2.KAISHA_CD ";
			$sql .= "   AND C.KEISAIHAN_CD = D2.KEISAIHAN_CD ";
			$sql .= "   AND C.BLOCK_CD = D2.BLOCK_CD ";
			$sql .= "   AND C.AREA_CD = D2.AREA_CD ";

			// 地域マスタ
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KEISAIHAN_CD, BLOCK_CD, AREA_CD, KUIKI_CD, ";
			$sql .= "             SUM(KODATE_SU) AS KODATE_SU, SUM(SHUGOU_SU) AS SHUGOU_SU, ";
			$sql .= "             SUM(SONOTA_SU) AS SONOTA_SU, SUM(SENBETU_FUKA_SU) AS SENBETU_FUKA_SU ";
			$sql .= "        FROM ADMS_MST_TIIKI WITH(NOLOCK) ";
			$sql .= "       WHERE KAISHA_CD = '999'";
			$sql .= "         AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "         AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "         AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_TIIKI WITH(NOLOCK) ";
			$sql .= "                            WHERE KAISHA_CD = '999'";
			$sql .= "                              AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                              AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                              AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                            GROUP BY KAISHA_CD, KEISAIHAN_CD) ";
			$sql .= "       GROUP BY KAISHA_CD, KEISAIHAN_CD, BLOCK_CD, AREA_CD, KUIKI_CD ";
			$sql .= "  ) AS E ";
			$sql .= "    ON D.KEISAIHAN_CD = E.KEISAIHAN_CD ";
			$sql .= "   AND D.BLOCK_CD = E.BLOCK_CD ";
			$sql .= "   AND D.AREA_CD = E.AREA_CD ";
			$sql .= "   AND D.KUIKI_CD = E.KUIKI_CD ";

			$sql .= " WHERE A.KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "   AND A.KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "   AND ISNULL(A.DEL_FLG, 0) = 0 AND ISNULL(A.MUKOU_FLG, 0) = 0 ";
			$sql .= "   AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_BLOCK WITH(NOLOCK) ";
			$sql .= "                      WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "                        AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                        AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                        AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                      GROUP BY KEISAIHAN_CD, KAISHA_CD) ";
			if(isset($arrParam["blockcd"]) || !empty($arrParam["blockcd"]))
			{
				$sql .= "   AND A.BLOCK_CD IN (" . $arrParam["blockcd"] . ")";
			}
			$sql .= " ORDER BY A.RYUTU_KYOTEN_CD, A.BLOCK_CD, C.AREA_CD, D.KUIKI_CD ";

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
	 * 地区表情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd  :会社コード, report  :掲載版コード, fromymd :期間[開始]
	 *                                   blockcd:ブロックコード[カンマ区切り], areacd:エリアコード[カンマ区切り])
	 * @return ture:成功、false:失敗
	 */
	public function SelectCirculationArea($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectCirculationArea', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["report"]) || empty($arrParam["report"]))
			{ throw new Exception('SelectCirculationArea', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["fromymd"]) || empty($arrParam["fromymd"]))
			{ throw new Exception('SelectCirculationArea', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT A.RYUTU_KYOTEN_CD AS KYOTEN_CD ";
			$sql .= "      ,B.CD_NM AS KYOTEN_NM ";
			$sql .= "      ,C.AREA_CD ";
			$sql .= "      ,C.AREA_CD + D.KUIKI_CD AS KUIKI_CD ";
			$sql .= "      ,C.AREA_CD + D.KUIKI_CD + E.TIIKI_CD AS TIIKI_CD ";
			$sql .= "      ,E2.CNT ";
			$sql .= "      ,E.KODATE_SU ";
			$sql .= "      ,E.SHUGOU_SU ";
			$sql .= "      ,E.SONOTA_SU ";
			$sql .= "      ,E.SENBETU_FUKA_SU ";
			$sql .= "      ,E.GYOSEIKU_NM + E.TIIKI_NM1 + E.TIIKI_NM2 + E.TIIKI_NM3 + E.TIIKI_NM4 AS TIIKI_NM  ";

			// ブロックマスタ
			$sql .= "  FROM ADMS_MST_BLOCK AS A WITH(NOLOCK) ";

			// 拠点マスタ
			$sql .= " INNER JOIN AD_VIEW_RYUTU_KYOTEN AS B WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.RYUTU_KYOTEN_CD = B.CD_KEY ";
			$sql .= "   AND ISNULL(B.DEL_FLG, 0) = 0 AND ISNULL(B.MUKOU_FLG, 0) = 0 ";

			// エリアマスタ
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, KEISAIHAN_CD, BLOCK_CD, AREA_CD, AREA_NM ";
			$sql .= "        FROM ADMS_MST_AREA WITH(NOLOCK) ";
			$sql .= "       WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "         AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "         AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "         AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_AREA WITH(NOLOCK) ";
			$sql .= "                            WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "                              AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                              AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                              AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                            GROUP BY KAISHA_CD, KEISAIHAN_CD) ";
			if(isset($arrParam["areacd"]) || !empty($arrParam["areacd"]))
			{
				$sql .= "         AND AREA_CD IN (" . $arrParam["areacd"] . ")";
			}
			$sql .= "  ) AS C ";
			$sql .= "    ON A.KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND A.KEISAIHAN_CD = C.KEISAIHAN_CD ";
			$sql .= "   AND A.BLOCK_CD = C.BLOCK_CD ";

			// 区域マスタ
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, KEISAIHAN_CD, BLOCK_CD, AREA_CD, KUIKI_CD, ";
			$sql .= "             KUIKI_NM1, KUIKI_NM2, KUIKI_NM3, KUIKI_NM4 ";
			$sql .= "        FROM ADMS_MST_KUIKI WITH(NOLOCK) ";
			$sql .= "       WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "         AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "         AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "         AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_KUIKI WITH(NOLOCK) ";
			$sql .= "                            WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "                              AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                              AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                              AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                            GROUP BY KAISHA_CD, KEISAIHAN_CD) ";
			$sql .= "  ) AS D ";
			$sql .= "    ON C.KAISHA_CD = D.KAISHA_CD ";
			$sql .= "   AND C.KEISAIHAN_CD = D.KEISAIHAN_CD ";
			$sql .= "   AND C.BLOCK_CD = D.BLOCK_CD ";
			$sql .= "   AND C.AREA_CD = D.AREA_CD ";

			// 地域マスタ
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KEISAIHAN_CD, BLOCK_CD, AREA_CD, KUIKI_CD, TIIKI_CD, ";
			$sql .= "             KODATE_SU, SHUGOU_SU, SONOTA_SU, SENBETU_FUKA_SU, GYOSEIKU_NM, ";
			$sql .= "             TIIKI_NM1, TIIKI_NM2, TIIKI_NM3, TIIKI_NM4 ";
			$sql .= "        FROM ADMS_MST_TIIKI WITH(NOLOCK) ";
			$sql .= "       WHERE KAISHA_CD = '999'";
			$sql .= "         AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "         AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "         AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_TIIKI WITH(NOLOCK) ";
			$sql .= "                            WHERE KAISHA_CD = '999'";
			$sql .= "                              AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                              AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                              AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                            GROUP BY KAISHA_CD, KEISAIHAN_CD) ";
			$sql .= "  ) AS E ";
			$sql .= "    ON D.KEISAIHAN_CD = E.KEISAIHAN_CD ";
			$sql .= "   AND D.BLOCK_CD = E.BLOCK_CD ";
			$sql .= "   AND D.AREA_CD = E.AREA_CD ";
			$sql .= "   AND D.KUIKI_CD = E.KUIKI_CD ";

			// 地域データ数
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KEISAIHAN_CD, BLOCK_CD, AREA_CD, KUIKI_CD, COUNT(*) AS CNT ";
			$sql .= "        FROM ADMS_MST_TIIKI WITH(NOLOCK) ";
			$sql .= "       WHERE KAISHA_CD = '999'";
			$sql .= "         AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "         AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "         AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_TIIKI WITH(NOLOCK) ";
			$sql .= "                            WHERE KAISHA_CD = '999'";
			$sql .= "                              AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                              AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                              AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                            GROUP BY KAISHA_CD, KEISAIHAN_CD) ";
			$sql .= "       GROUP BY KEISAIHAN_CD, BLOCK_CD, AREA_CD, KUIKI_CD";
			$sql .= "  ) AS E2 ";
			$sql .= "    ON D.KEISAIHAN_CD = E2.KEISAIHAN_CD ";
			$sql .= "   AND D.BLOCK_CD = E2.BLOCK_CD ";
			$sql .= "   AND D.AREA_CD = E2.AREA_CD ";
			$sql .= "   AND D.KUIKI_CD = E2.KUIKI_CD ";

			$sql .= " WHERE A.KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "   AND A.KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "   AND ISNULL(A.DEL_FLG, 0) = 0 AND ISNULL(A.MUKOU_FLG, 0) = 0 ";
			$sql .= "   AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_BLOCK WITH(NOLOCK) ";
			$sql .= "                      WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "                        AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                        AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                        AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                      GROUP BY KEISAIHAN_CD, KAISHA_CD) ";
			if(isset($arrParam["blockcd"]) || !empty($arrParam["blockcd"]))
			{
				$sql .= "   AND A.BLOCK_CD IN (" . $arrParam["blockcd"] . ")";
			}
			$sql .= " ORDER BY A.RYUTU_KYOTEN_CD, C.AREA_CD + D.KUIKI_CD + E.TIIKI_CD ";

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
	 * 担当地区表(CSV)情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd  :会社コード, report  :掲載版コード, fromymd :期間[開始], 
	 *                                   blockcd:ブロックコード[カンマ区切り], areacd:エリアコード[カンマ区切り])
	 * @return ture:成功、false:失敗
	 */
	public function SelectCirculationAreaCsv($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectCirculationArea', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["report"]) || empty($arrParam["report"]))
			{ throw new Exception('SelectCirculationArea', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["fromymd"]) || empty($arrParam["fromymd"]))
			{ throw new Exception('SelectCirculationArea', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT B.KEISAIHAN_NM ";
			$sql .= "      ,E.AREA_CD + E.KUIKI_CD AS KUIKI_CD ";
			$sql .= "      ,E.AREA_CD + E.KUIKI_CD + E.TIIKI_CD AS TIIKI_CD ";
			$sql .= "      ,E.GYOSEIKU_NM ";
			$sql .= "      ,E.TIIKI_NM1 ";
			$sql .= "      ,E.TIIKI_NM2 ";
			$sql .= "      ,E.TIIKI_NM3 ";
			$sql .= "      ,E.TIIKI_NM4 ";
			$sql .= "      ,E.KODATE_SU ";
			$sql .= "      ,E.SHUGOU_SU ";
			$sql .= "      ,E.SONOTA_SU ";
			$sql .= "      ,E.SENBETU_FUKA_SU ";
			$sql .= "      ,E.KODATE_SU + E.SHUGOU_SU + E.SONOTA_SU + E.SENBETU_FUKA_SU AS GOUKEI ";

			// ブロックマスタ
			$sql .= "  FROM ADMS_MST_BLOCK AS A WITH(NOLOCK) ";

			// 掲載版マスタ
			$sql .= " INNER JOIN ADMS_MST_KEISAIHAN AS B WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.KEISAIHAN_CD = B.KEISAIHAN_CD ";
			$sql .= "   AND ISNULL(B.DEL_FLG, 0) = 0 AND ISNULL(B.MUKOU_FLG, 0) = 0 ";

			// エリアマスタ
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, KEISAIHAN_CD, BLOCK_CD, AREA_CD, AREA_NM ";
			$sql .= "        FROM ADMS_MST_AREA WITH(NOLOCK) ";
			$sql .= "       WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "         AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "         AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "         AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_AREA WITH(NOLOCK) ";
			$sql .= "                            WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "                              AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                              AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                              AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                            GROUP BY KAISHA_CD, KEISAIHAN_CD) ";
			if(isset($arrParam["areacd"]) || !empty($arrParam["areacd"]))
			{
				$sql .= "         AND AREA_CD IN (" . $arrParam["areacd"] . ")";
			}
			$sql .= "  ) AS C ";
			$sql .= "    ON A.KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND A.KEISAIHAN_CD = C.KEISAIHAN_CD ";
			$sql .= "   AND A.BLOCK_CD = C.BLOCK_CD ";

			// 区域マスタ
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, KEISAIHAN_CD, BLOCK_CD, AREA_CD, KUIKI_CD ";
			$sql .= "        FROM ADMS_MST_KUIKI WITH(NOLOCK) ";
			$sql .= "       WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "         AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "         AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "         AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_KUIKI WITH(NOLOCK) ";
			$sql .= "                            WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "                              AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                              AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                              AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                            GROUP BY KAISHA_CD, KEISAIHAN_CD) ";
			$sql .= "  ) AS D ";
			$sql .= "    ON C.KAISHA_CD = D.KAISHA_CD ";
			$sql .= "   AND C.KEISAIHAN_CD = D.KEISAIHAN_CD ";
			$sql .= "   AND C.BLOCK_CD = D.BLOCK_CD ";
			$sql .= "   AND C.AREA_CD = D.AREA_CD ";

			// 地域マスタ
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KEISAIHAN_CD, BLOCK_CD, AREA_CD, KUIKI_CD, TIIKI_CD, ";
			$sql .= "             KODATE_SU, SHUGOU_SU, SONOTA_SU, SENBETU_FUKA_SU, GYOSEIKU_NM, ";
			$sql .= "             TIIKI_NM1, TIIKI_NM2, TIIKI_NM3, TIIKI_NM4 ";
			$sql .= "        FROM ADMS_MST_TIIKI WITH(NOLOCK) ";
			$sql .= "       WHERE KAISHA_CD = '999'";
			$sql .= "         AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "         AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "         AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_TIIKI WITH(NOLOCK) ";
			$sql .= "                            WHERE KAISHA_CD = '999'";
			$sql .= "                              AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                              AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                              AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                            GROUP BY KAISHA_CD, KEISAIHAN_CD) ";
			$sql .= "  ) AS E ";
			$sql .= "    ON D.KEISAIHAN_CD = E.KEISAIHAN_CD ";
			$sql .= "   AND D.BLOCK_CD = E.BLOCK_CD ";
			$sql .= "   AND D.AREA_CD = E.AREA_CD ";
			$sql .= "   AND D.KUIKI_CD = E.KUIKI_CD ";

			$sql .= " WHERE A.KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "   AND A.KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "   AND ISNULL(A.DEL_FLG, 0) = 0 AND ISNULL(A.MUKOU_FLG, 0) = 0 ";
			$sql .= "   AND START_YMD IN (SELECT MAX(START_YMD) FROM ADMS_MST_BLOCK WITH(NOLOCK) ";
			$sql .= "                      WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "                        AND KEISAIHAN_CD = '" . $arrParam["report"] . "'";
			$sql .= "                        AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "                        AND START_YMD <= '" . $arrParam["fromymd"] . "'";
			$sql .= "                      GROUP BY KEISAIHAN_CD, KAISHA_CD) ";
			if(isset($arrParam["blockcd"]) || !empty($arrParam["blockcd"]))
			{
				$sql .= "   AND A.BLOCK_CD IN (" . $arrParam["blockcd"] . ")";
			}
			$sql .= " ORDER BY B.KEISAIHAN_NM, E.AREA_CD + E.KUIKI_CD + E.TIIKI_CD ";

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

