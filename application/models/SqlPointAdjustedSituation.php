<?php

/**
 * SqlPointAdjustedSituation for class
 *
 * ぱどPO精算状況 SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlPointAdjustedSituation
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
	 * ぱどPO精算状況(請求全体)検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, startdate:納期(開始), enddate:納期(終了), 
	 *                                   sstartdate:精算日(開始), senddate:精算日(終了))
	 * @return ture:成功、false:失敗
	 */
	public function SelectPAdjustedSituationDemand($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();
		$blnRet = true;
		$blnSRet = true;

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectPointAdjustedSituation', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT ";
			$sql .= "       SUBSTRING(CONVERT(VARCHAR(10),A.POINT_TAISHO_YM,111),0,8) AS POINT_TAISHO_YM ";
			$sql .= "      ,C.SEIKYU_NO AS SEIKYU_NO ";
			$sql .= "      ,A.SEIKYUSAKI_NO AS SEIKYUSAKI_NO ";
			$sql .= "      ,D.SEIKYUSAKI_NM AS SEIKYUSAKI_NM ";
			$sql .= "      ,A.PADO_NAVI_SHOP_ID AS PADO_NAVI_SHOP_ID ";
			$sql .= "      ,A.KOUKOKU_TESURYO_KINGAKU + A.AZUKARI_KINGAKU + A.ADVCMT_AZKRIKIN_TAX - A.WARIBIKI_KINGAKU ";
			$sql .= "       - A.WARIBIKI_TAX + A.HENBAI_WARIBIKIGAKU + A.HENBAI_WARIBIKI_TAX AS HAKKOU_PNT_GAKU ";
			$sql .= "      ,A.RIYO_PNT_GAKU AS RIYO_PNT_GAKU ";
			$sql .= "      ,SUM(ISNULL(E.KESIKOMI_KINGAKU,0)) AS SUM_KESIKOMI_KINGAKU ";
			$sql .= "      ,(SUM(B.MEISAI_KINGAKU+B.MEISAI_TAX) - SUM(ISNULL(E.KESIKOMI_KINGAKU,0))) AS ZAN_GAKU ";
			$sql .= "      ,ISNULL(F.MX_KESIKOMI_YMD,'') AS MX_KESIKOMI_YMD ";
			$sql .= "      ,ISNULL(F.MX_NYUKIN_NO,'') AS MX_NYUKIN_NO ";
			$sql .= "      ,CASE WHEN DATEDIFF(MONTH, C.SEIKYU_BI_YMD, ISNULL(F.MX_KESIKOMI_YMD,'')) > 0 ";
			$sql .= "            THEN '*' ELSE '' ";
			$sql .= "       END AS SEIKYUIKOU_SEISAN ";
			$sql .= "      ,ISNULL(H.NYUKIN_SHUBETU_CD, '') AS NYUKIN_SHUBETU_CD ";
			$sql .= "      ,ISNULL(I.NYUKIN_SHUBETU_NM, '') AS NYUKIN_SHUBETU_NM ";
			$sql .= "      ,ISNULL(G.URITEI_CNT, 0) AS URITEI_CNT ";

			$sql .= "  FROM PDDR_TBL_PADOPO_POINT AS A WITH(NOLOCK) ";

			$sql .= " INNER JOIN DR_TBL_SEIKYUDTL AS B WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.MNTAMT_DENPYO_NO = B.DENPYO_NO";
			$sql .= "   AND A.PNT_MEISAI_NO = B.MEISAI_NO";
			$sql .= "   AND ISNULL(B.DEL_FLG, 0) = 0 ";

			$sql .= " INNER JOIN DR_TBL_SEIKYUHDR AS C WITH(NOLOCK) ";
			$sql .= "    ON B.KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND B.SEIKYU_NO = C.SEIKYU_NO ";
			$sql .= "   AND ISNULL(C.DEL_FLG, 0) = 0 AND ISNULL(C.TORIKESI_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN DR_TBL_SEIKYUSHO AS D WITH(NOLOCK) ";
			$sql .= "    ON B.KAISHA_CD = D.KAISHA_CD ";
			$sql .= "   AND B.SEIKYU_NO = D.SEIKYU_NO ";
			$sql .= "   AND ISNULL(D.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN ";
			$sql .= "  ( ";
			$sql .= "    SELECT KAISHA_CD, SEIKYU_NO, SEIKYU_DTL_RENBAN ";
			$sql .= "          ,SUM(KESIKOMI_KINGAKU) AS KESIKOMI_KINGAKU ";
			$sql .= "      FROM DR_TBL_KESIKOMI WITH(NOLOCK) ";
			$sql .= "     WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "       AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(TORIKESI_FLG, 0) = 0 ";
			if(isset($arrParam["sstartdate"]) && !empty($arrParam["sstartdate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10), KESIKOMI_YMD, 111) >= '" . $arrParam["sstartdate"] . "'";
			}
			if(isset($arrParam["senddate"]) && !empty($arrParam["senddate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10), KESIKOMI_YMD, 111) <= '" . $arrParam["senddate"] . "'";
			}
			$sql .= "     GROUP BY KAISHA_CD, SEIKYU_NO, SEIKYU_DTL_RENBAN ";
			$sql .= "  ) AS E ";
			$sql .= "    ON B.KAISHA_CD = E.KAISHA_CD ";
			$sql .= "   AND B.SEIKYU_NO = E.SEIKYU_NO ";
			$sql .= "   AND B.SEIKYU_DTL_RENBAN = E.SEIKYU_DTL_RENBAN ";

			$sql .= "  LEFT JOIN ";
			$sql .= "  ( ";
			$sql .= "    SELECT F2.KAISHA_CD, F2.SEIKYU_NO ";
			$sql .= "          ,MAX(CONVERT(VARCHAR(10),KESIKOMI_YMD,111)) AS MX_KESIKOMI_YMD ";
			$sql .= "          ,MAX(NYUKIN_NO) AS MX_NYUKIN_NO ";
			$sql .= "      FROM DR_TBL_KESIKOMI AS F2 WITH(NOLOCK) ";
			$sql .= "     INNER JOIN DR_TBL_SEIKYUDTL AS F3 WITH(NOLOCK) ";
			$sql .= "        ON F2.KAISHA_CD = F3.KAISHA_CD ";
			$sql .= "       AND F2.SEIKYU_NO = F3.SEIKYU_NO ";
			$sql .= "       AND F2.SEIKYU_DTL_RENBAN = F3.SEIKYU_DTL_RENBAN ";
			$sql .= "       AND ISNULL(F3.DEL_FLG, 0) = 0 ";
			$sql .= "     WHERE F2.KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "       AND ISNULL(F2.DEL_FLG, 0) = 0 AND ISNULL(F2.TORIKESI_FLG, 0) = 0 ";
			if(isset($arrParam["sstartdate"]) && !empty($arrParam["sstartdate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10), F2.KESIKOMI_YMD, 111) >= '" . $arrParam["sstartdate"] . "'";
			}
			if(isset($arrParam["senddate"]) && !empty($arrParam["senddate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10), F2.KESIKOMI_YMD, 111) <= '" . $arrParam["senddate"] . "'";
			}
			$sql .= "     GROUP BY F2.KAISHA_CD, F2.SEIKYU_NO ";
			$sql .= "  ) AS F ";
			$sql .= "    ON B.KAISHA_CD = F.KAISHA_CD ";
			$sql .= "   AND B.SEIKYU_NO = F.SEIKYU_NO ";

			$sql .= "  LEFT JOIN ";
			$sql .= "  ( ";
			$sql .= "    SELECT G2.KAISHA_CD, G2.SEIKYU_NO, COUNT(*) AS URITEI_CNT ";
			$sql .= "      FROM DR_TBL_KESIKOMI AS G2 WITH(NOLOCK) ";
			$sql .= "     INNER JOIN DR_TBL_SEIKYUDTL AS G3 WITH(NOLOCK) ";
			$sql .= "        ON G2.KAISHA_CD = G3.KAISHA_CD ";
			$sql .= "       AND G2.SEIKYU_NO = G3.SEIKYU_NO ";
			$sql .= "       AND G2.SEIKYU_DTL_RENBAN = G3.SEIKYU_DTL_RENBAN ";
			$sql .= "       AND ISNULL(G3.DEL_FLG, 0) = 0 ";
			$sql .= "     INNER JOIN DR_TBL_NYUKIN AS G4 WITH(NOLOCK) ";
			$sql .= "        ON G2.KAISHA_CD = G4.KAISHA_CD ";
			$sql .= "       AND G2.NYUKIN_NO = G4.NYUKIN_NO ";
			$sql .= "       AND G4.NYUKIN_SHUBETU_CD = '14' ";
			$sql .= "       AND ISNULL(G4.DEL_FLG, 0) = 0 AND ISNULL(G4.TORIKESI_FLG, 0) = 0 ";
			
			$sql .= "     WHERE G2.KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "       AND ISNULL(G2.DEL_FLG, 0) = 0 AND ISNULL(G2.TORIKESI_FLG, 0) = 0 ";
			if(isset($arrParam["sstartdate"]) && !empty($arrParam["sstartdate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10), G2.KESIKOMI_YMD, 111) >= '" . $arrParam["sstartdate"] . "'";
			}
			if(isset($arrParam["senddate"]) && !empty($arrParam["senddate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10), G2.KESIKOMI_YMD, 111) <= '" . $arrParam["senddate"] . "'";
			}
			$sql .= "     GROUP BY G2.KAISHA_CD, G2.SEIKYU_NO ";
			$sql .= "  ) AS G ";
			$sql .= "    ON B.KAISHA_CD = G.KAISHA_CD ";
			$sql .= "   AND B.SEIKYU_NO = G.SEIKYU_NO ";

			$sql .= "  LEFT JOIN DR_TBL_NYUKIN AS H WITH(NOLOCK) ";
			$sql .= "    ON F.KAISHA_CD = H.KAISHA_CD ";
			$sql .= "   AND F.MX_NYUKIN_NO = H.NYUKIN_NO ";

			$sql .= "  LEFT JOIN BT_VIEW_NYUKIN_SHUBETU_M AS I WITH(NOLOCK) ";
			$sql .= "    ON H.NYUKIN_SHUBETU_CD = I.NYUKIN_SHUBETU_CD ";

			$sql .= " WHERE A.KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "   AND ISNULL(A.DEL_FLG, 0) = 0 "; 
			if(isset($arrParam["startdate"]) && !empty($arrParam["startdate"]))
			{
				$sql .= "   AND SUBSTRING(CONVERT(VARCHAR(10),A.POINT_TAISHO_YM,111),0,8) >= '" . $arrParam["startdate"] . "'";
			}
			if(isset($arrParam["enddate"]) && !empty($arrParam["enddate"]))
			{
				$sql .= "   AND SUBSTRING(CONVERT(VARCHAR(10),A.POINT_TAISHO_YM,111),0,8) <= '" . $arrParam["enddate"] . "'";
			}
			if(isset($arrParam["sstartdate"]) && !empty($arrParam["sstartdate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10),F.MX_KESIKOMI_YMD,111) >= '" . $arrParam["sstartdate"] . "'";
			}
			if(isset($arrParam["senddate"]) && !empty($arrParam["senddate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10),F.MX_KESIKOMI_YMD,111) <= '" . $arrParam["senddate"] . "'";
			}
			$sql .= " GROUP BY A.POINT_TAISHO_YM, C.SEIKYU_NO, C.SEIKYU_BI_YMD, A.SEIKYUSAKI_NO, D.SEIKYUSAKI_NM ";
			$sql .= "         ,A.PADO_NAVI_SHOP_ID, A.KOUKOKU_TESURYO_KINGAKU, A.AZUKARI_KINGAKU, A.ADVCMT_AZKRIKIN_TAX ";
			$sql .= "         ,A.WARIBIKI_KINGAKU, A.WARIBIKI_TAX, A.HENBAI_WARIBIKIGAKU, A.HENBAI_WARIBIKI_TAX ";
			$sql .= "         ,A.RIYO_PNT_GAKU, F.MX_KESIKOMI_YMD, F.MX_NYUKIN_NO, H.NYUKIN_SHUBETU_CD ";
			$sql .= "         ,I.NYUKIN_SHUBETU_NM, URITEI_CNT ";

			$sql .= " ORDER BY A.POINT_TAISHO_YM, A.SEIKYUSAKI_NO, A.PADO_NAVI_SHOP_ID, C.SEIKYU_NO ";

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
	 * ぱどPO精算状況(ポイント)検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, startdate:納期(開始), enddate:納期(終了), 
	 *                                   sstartdate:精算日(開始), senddate:精算日(終了))
	 * @return ture:成功、false:失敗
	 */
	public function SelectPAdjustedSituationPoint($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();
		$blnRet = true;
		$blnSRet = true;

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectPointAdjustedSituation', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT ";
			$sql .= "       SUBSTRING(CONVERT(VARCHAR(10),A.POINT_TAISHO_YM,111),0,8) AS POINT_TAISHO_YM ";
			$sql .= "      ,C.SEIKYU_NO AS SEIKYU_NO ";
			$sql .= "      ,A.SEIKYUSAKI_NO AS SEIKYUSAKI_NO ";
			$sql .= "      ,D.SEIKYUSAKI_NM AS SEIKYUSAKI_NM ";
			$sql .= "      ,A.PADO_NAVI_SHOP_ID AS PADO_NAVI_SHOP_ID ";
			$sql .= "      ,A.KOUKOKU_TESURYO_KINGAKU + A.AZUKARI_KINGAKU + A.ADVCMT_AZKRIKIN_TAX - A.WARIBIKI_KINGAKU ";
			$sql .= "       - A.WARIBIKI_TAX + A.HENBAI_WARIBIKIGAKU + A.HENBAI_WARIBIKI_TAX AS HAKKOU_PNT_GAKU ";
			$sql .= "      ,A.RIYO_PNT_GAKU AS RIYO_PNT_GAKU ";
			$sql .= "      ,SUM(ISNULL(E.KESIKOMI_KINGAKU,0)) AS SUM_KESIKOMI_KINGAKU ";
			$sql .= "      ,(SUM(B.MEISAI_KINGAKU+B.MEISAI_TAX) - SUM(ISNULL(E.KESIKOMI_KINGAKU,0))) AS ZAN_GAKU ";
			$sql .= "      ,ISNULL(F.MX_KESIKOMI_YMD,'') AS MX_KESIKOMI_YMD ";
			$sql .= "      ,ISNULL(F.MX_NYUKIN_NO,'') AS MX_NYUKIN_NO ";
			$sql .= "      ,CASE WHEN DATEDIFF(MONTH, C.SEIKYU_BI_YMD, ISNULL(F.MX_KESIKOMI_YMD,'')) > 0 ";
			$sql .= "            THEN '*' ELSE '' ";
			$sql .= "       END AS SEIKYUIKOU_SEISAN ";
			$sql .= "      ,ISNULL(H.NYUKIN_SHUBETU_CD, '') AS NYUKIN_SHUBETU_CD ";
			$sql .= "      ,ISNULL(I.NYUKIN_SHUBETU_NM, '') AS NYUKIN_SHUBETU_NM ";
			$sql .= "      ,ISNULL(G.URITEI_CNT, 0) AS URITEI_CNT ";

			$sql .= "  FROM PDDR_TBL_PADOPO_POINT AS A WITH(NOLOCK) ";

			$sql .= " INNER JOIN DR_TBL_SEIKYUDTL AS B WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.MNTAMT_DENPYO_NO = B.DENPYO_NO";
			$sql .= "   AND A.PNT_MEISAI_NO = B.MEISAI_NO";
			$sql .= "   AND ISNULL(B.DEL_FLG, 0) = 0 ";

			$sql .= " INNER JOIN DR_TBL_SEIKYUHDR AS C WITH(NOLOCK) ";
			$sql .= "    ON B.KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND B.SEIKYU_NO = C.SEIKYU_NO ";
			$sql .= "   AND ISNULL(C.DEL_FLG, 0) = 0 AND ISNULL(C.TORIKESI_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN DR_TBL_SEIKYUSHO AS D WITH(NOLOCK) ";
			$sql .= "    ON B.KAISHA_CD = D.KAISHA_CD ";
			$sql .= "   AND B.SEIKYU_NO = D.SEIKYU_NO ";
			$sql .= "   AND ISNULL(D.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN ";
			$sql .= "  ( ";
			$sql .= "    SELECT KAISHA_CD, SEIKYU_NO, SEIKYU_DTL_RENBAN ";
			$sql .= "          ,SUM(KESIKOMI_KINGAKU) AS KESIKOMI_KINGAKU ";
			$sql .= "      FROM DR_TBL_KESIKOMI WITH(NOLOCK) ";
			$sql .= "     WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "       AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(TORIKESI_FLG, 0) = 0 ";
			if(isset($arrParam["sstartdate"]) && !empty($arrParam["sstartdate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10), KESIKOMI_YMD, 111) >= '" . $arrParam["sstartdate"] . "'";
			}
			if(isset($arrParam["senddate"]) && !empty($arrParam["senddate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10), KESIKOMI_YMD, 111) <= '" . $arrParam["senddate"] . "'";
			}
			$sql .= "     GROUP BY KAISHA_CD, SEIKYU_NO, SEIKYU_DTL_RENBAN ";
			$sql .= "  ) AS E ";
			$sql .= "    ON B.KAISHA_CD = E.KAISHA_CD ";
			$sql .= "   AND B.SEIKYU_NO = E.SEIKYU_NO ";
			$sql .= "   AND B.SEIKYU_DTL_RENBAN = E.SEIKYU_DTL_RENBAN ";

			$sql .= "  LEFT JOIN ";
			$sql .= "  ( ";
			$sql .= "    SELECT F2.KAISHA_CD, F3.DENPYO_NO, F3.MEISAI_NO ";
			$sql .= "          ,MAX(CONVERT(VARCHAR(10),KESIKOMI_YMD,111)) AS MX_KESIKOMI_YMD ";
			$sql .= "          ,MAX(NYUKIN_NO) AS MX_NYUKIN_NO ";
			$sql .= "      FROM DR_TBL_KESIKOMI AS F2 WITH(NOLOCK) ";
			$sql .= "     INNER JOIN DR_TBL_SEIKYUDTL AS F3 WITH(NOLOCK) ";
			$sql .= "        ON F2.KAISHA_CD = F3.KAISHA_CD ";
			$sql .= "       AND F2.SEIKYU_NO = F3.SEIKYU_NO ";
			$sql .= "       AND F2.SEIKYU_DTL_RENBAN = F3.SEIKYU_DTL_RENBAN ";
			$sql .= "       AND ISNULL(F3.DEL_FLG, 0) = 0 ";
			$sql .= "     WHERE F2.KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "       AND ISNULL(F2.DEL_FLG, 0) = 0 AND ISNULL(F2.TORIKESI_FLG, 0) = 0 ";
			if(isset($arrParam["sstartdate"]) && !empty($arrParam["sstartdate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10), F2.KESIKOMI_YMD, 111) >= '" . $arrParam["sstartdate"] . "'";
			}
			if(isset($arrParam["senddate"]) && !empty($arrParam["senddate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10), F2.KESIKOMI_YMD, 111) <= '" . $arrParam["senddate"] . "'";
			}
			$sql .= "     GROUP BY F2.KAISHA_CD, F3.DENPYO_NO, F3.MEISAI_NO ";
			$sql .= "  ) AS F ";
			$sql .= "    ON B.KAISHA_CD = F.KAISHA_CD ";
			$sql .= "   AND B.DENPYO_NO = F.DENPYO_NO ";
			$sql .= "   AND B.MEISAI_NO = F.MEISAI_NO ";

			$sql .= "  LEFT JOIN ";
			$sql .= "  ( ";
			$sql .= "    SELECT G2.KAISHA_CD, G3.DENPYO_NO, G3.MEISAI_NO, COUNT(*) AS URITEI_CNT ";
			$sql .= "      FROM DR_TBL_KESIKOMI AS G2 WITH(NOLOCK) ";
			$sql .= "     INNER JOIN DR_TBL_SEIKYUDTL AS G3 WITH(NOLOCK) ";
			$sql .= "        ON G2.KAISHA_CD = G3.KAISHA_CD ";
			$sql .= "       AND G2.SEIKYU_NO = G3.SEIKYU_NO ";
			$sql .= "       AND G2.SEIKYU_DTL_RENBAN = G3.SEIKYU_DTL_RENBAN ";
			$sql .= "       AND ISNULL(G3.DEL_FLG, 0) = 0 ";
			$sql .= "     INNER JOIN DR_TBL_NYUKIN AS G4 WITH(NOLOCK) ";
			$sql .= "        ON G2.KAISHA_CD = G4.KAISHA_CD ";
			$sql .= "       AND G2.NYUKIN_NO = G4.NYUKIN_NO ";
			$sql .= "       AND G4.NYUKIN_SHUBETU_CD = '14' ";
			$sql .= "       AND ISNULL(G4.DEL_FLG, 0) = 0 AND ISNULL(G4.TORIKESI_FLG, 0) = 0 ";
			
			$sql .= "     WHERE G2.KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "       AND ISNULL(G2.DEL_FLG, 0) = 0 AND ISNULL(G2.TORIKESI_FLG, 0) = 0 ";
			if(isset($arrParam["sstartdate"]) && !empty($arrParam["sstartdate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10), G2.KESIKOMI_YMD, 111) >= '" . $arrParam["sstartdate"] . "'";
			}
			if(isset($arrParam["senddate"]) && !empty($arrParam["senddate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10), G2.KESIKOMI_YMD, 111) <= '" . $arrParam["senddate"] . "'";
			}
			$sql .= "     GROUP BY G2.KAISHA_CD, G3.DENPYO_NO, G3.MEISAI_NO ";
			$sql .= "  ) AS G ";
			$sql .= "    ON B.KAISHA_CD = G.KAISHA_CD ";
			$sql .= "   AND B.DENPYO_NO = G.DENPYO_NO ";
			$sql .= "   AND B.MEISAI_NO = G.MEISAI_NO ";

			$sql .= "  LEFT JOIN DR_TBL_NYUKIN AS H WITH(NOLOCK) ";
			$sql .= "    ON F.KAISHA_CD = H.KAISHA_CD ";
			$sql .= "   AND F.MX_NYUKIN_NO = H.NYUKIN_NO ";

			$sql .= "  LEFT JOIN BT_VIEW_NYUKIN_SHUBETU_M AS I WITH(NOLOCK) ";
			$sql .= "    ON H.NYUKIN_SHUBETU_CD = I.NYUKIN_SHUBETU_CD ";

			$sql .= " WHERE A.KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "   AND ISNULL(A.DEL_FLG, 0) = 0 "; 
			if(isset($arrParam["startdate"]) && !empty($arrParam["startdate"]))
			{
				$sql .= "   AND SUBSTRING(CONVERT(VARCHAR(10),A.POINT_TAISHO_YM,111),0,8) >= '" . $arrParam["startdate"] . "'";
			}
			if(isset($arrParam["enddate"]) && !empty($arrParam["enddate"]))
			{
				$sql .= "   AND SUBSTRING(CONVERT(VARCHAR(10),A.POINT_TAISHO_YM,111),0,8) <= '" . $arrParam["enddate"] . "'";
			}
			if(isset($arrParam["sstartdate"]) && !empty($arrParam["sstartdate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10),F.MX_KESIKOMI_YMD,111) >= '" . $arrParam["sstartdate"] . "'";
			}
			if(isset($arrParam["senddate"]) && !empty($arrParam["senddate"]))
			{
				$sql .= "   AND CONVERT(VARCHAR(10),F.MX_KESIKOMI_YMD,111) <= '" . $arrParam["senddate"] . "'";
			}
			$sql .= " GROUP BY A.POINT_TAISHO_YM, C.SEIKYU_NO, C.SEIKYU_BI_YMD, A.SEIKYUSAKI_NO, D.SEIKYUSAKI_NM ";
			$sql .= "         ,A.PADO_NAVI_SHOP_ID, A.KOUKOKU_TESURYO_KINGAKU, A.AZUKARI_KINGAKU, A.ADVCMT_AZKRIKIN_TAX ";
			$sql .= "         ,A.WARIBIKI_KINGAKU, A.WARIBIKI_TAX, A.HENBAI_WARIBIKIGAKU, A.HENBAI_WARIBIKI_TAX ";
			$sql .= "         ,A.RIYO_PNT_GAKU, F.MX_KESIKOMI_YMD, F.MX_NYUKIN_NO, H.NYUKIN_SHUBETU_CD ";
			$sql .= "         ,I.NYUKIN_SHUBETU_NM, URITEI_CNT ";

			$sql .= " ORDER BY A.POINT_TAISHO_YM, A.SEIKYUSAKI_NO, A.PADO_NAVI_SHOP_ID, C.SEIKYU_NO ";

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

