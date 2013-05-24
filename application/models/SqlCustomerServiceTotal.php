<?php

/**
 * SqlCustomerServiceTotal for class
 *
 * カスタマーセンター問合せ集計 SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlCustomerServiceTotal
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
	 * カスタマーセンター問合せ集計情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(startdate:カスタマー対応日(開始), enddate:カスタマー対応日(終了))
	 * @return ture:成功、false:失敗
	 */
	public function SelectCustomerServiceTotal($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["startdate"]) || empty($arrParam["startdate"]))
			{ throw new Exception('SelectCustomerServiceTotal', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["enddate"]) || empty($arrParam["enddate"]))
			{ throw new Exception('SelectCustomerServiceTotal', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT ";
			$sql .= "       A.KAISHA_CD AS KAISHA_CD ";
			$sql .= "      ,CASE WHEN A.KAISHA_CD = '001' THEN '首都圏' ";
			$sql .= "            WHEN A.KAISHA_CD = '002' THEN '仙台' ";
			$sql .= "            WHEN A.KAISHA_CD = '003' THEN '福岡' ";
			$sql .= "            WHEN A.KAISHA_CD = '004' THEN '浜松' ";
			$sql .= "       END AS KAISHA_NAME ";
			$sql .= "      ,A.ANKEN_NO AS ANKEN_NO ";
			$sql .= "      ,B.ANKEN_TYPE_CD AS ANKEN_TYPE_CD ";
			$sql .= "      ,M.CD_NM AS CD_NM ";
			$sql .= "      ,A.SETTEN_NO AS SETTEN_NO ";
			$sql .= "      ,C.DISP_SMRY_BUSHO_CD AS DISP_SMRY_BUSHO_CD ";
			$sql .= "      ,C.BUSHO_NM AS BUSHO_NM ";
			$sql .= "      ,A.TAIOU_SHAIN_CD AS TAIOU_SHAIN_CD ";
			$sql .= "      ,D.SHAIN_NM AS SHAIN_NM ";
			$sql .= "      ,CONVERT(char(10), A.SETTEN_YMD,111) AS SETTEN_YMD ";
			$sql .= "      ,A.HOUHOU_CD AS HOUHOU_CD ";
			$sql .= "      ,K.CD_NM AS CD_NM ";
			$sql .= "      ,A.NAIYOU_CD AS NAIYOU_CD ";
			$sql .= "      ,L.CD_NM AS CD_NM ";
			$sql .= "      ,B.TORIHIKISAKI_CD AS TORIHIKISAKI_CD ";
			$sql .= "      ,E.TORIHIKISAKI_NM AS TORIHIKISAKI_NM ";
			$sql .= "      ,REPLACE(REPLACE(ISNULL(E.BIKO, ''), CHAR(13), ''), CHAR(10), '') AS BIKO ";
			$sql .= "      ,CONVERT(char(10), E.INS_TM, 111) AS INS_TM ";
			$sql .= "      ,E.JUSHO_1 AS JUSHO_1 ";
			$sql .= "      ,ISNULL(E.JUSHO_2, '') AS JUSHO_2 ";
			$sql .= "      ,E.GYOUSHU_CD AS GYOUSHU_CD ";
			$sql .= "      ,N.CD_NM AS CD_NM ";
			$sql .= "      ,E.GYOUSYU_DTL_CD AS GYOUSYU_DTL_CD ";
			$sql .= "      ,O.CD_NM AS CD_NM ";
			$sql .= "      ,G.EIGYO_KYOTEN_CD AS EIGYO_KYOTEN_CD ";
			$sql .= "      ,I.CD_NM AS CD_NM ";
			$sql .= "      ,F.BUSHO_CD AS BUSHO_CD ";
			$sql .= "      ,G.BUSHO_NM AS BUSHO_NM ";
			$sql .= "      ,F.SHAIN_CD AS SHAIN_CD ";
			$sql .= "      ,H.SHAIN_NM AS SHAIN_NM ";
			$sql .= "      ,ISNULL(J.INS_TM, '') AS INS_TM ";
			$sql .= "      ,ISNULL(J.NOUKI_YMD, '') AS NOUKI_YMD ";
			$sql .= "      ,ISNULL(J.SHOUKEI_SUM, 0) AS SHOUKEI_SUM ";
			$sql .= "      ,ISNULL(J.SHOUKEI_SUM, 0) - ISNULL(J.GENKA, 0) AS ARARI ";
			$sql .= "      ,ISNULL(J.MEISAI_CNT, 0) AS MEISAI_CNT ";
			$sql .= "      ,REPLACE(REPLACE(A.SHOUSAI, CHAR(13), ''), CHAR(10), '') AS SHOUSAI ";

			$sql .= "  FROM CM_TBL_SETTEN AS A WITH(NOLOCK) ";

			$sql .= "  LEFT JOIN CM_TBL_ANKEN_TORIHIKI AS B WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.ANKEN_NO = B.ANKEN_NO ";

			$sql .= "  LEFT JOIN BT_VIEW_BUSHO AS C WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND A.TAIOU_BUSHO_CD = C.BUSHO_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_SHAIN AS D WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = D.KAISHA_CD ";
			$sql .= "   AND A.TAIOU_SHAIN_CD = D.SHAIN_CD ";

			$sql .= "  LEFT JOIN CM_MST_TORIHIKISAKI AS E WITH(NOLOCK) ";
			$sql .= "    ON B.KAISHA_CD = E.KAISHA_CD ";
			$sql .= "   AND B.TORIHIKISAKI_CD = E.TORIHIKISAKI_CD ";

			$sql .= "  LEFT JOIN BC_MST_TORI_JI_TANTO AS F WITH(NOLOCK) ";
			$sql .= "    ON E.KAISHA_CD = F.KAISHA_CD ";
			$sql .= "   AND E.TORIHIKISAKI_CD = F.TORIHIKISAKI_CD ";
			$sql .= "   AND F.TORIHIKISAKI_TANTOUSHA_KBN = '01' ";
			$sql .= "   AND F.RENBAN =(SELECT MIN(RENBAN)";
			$sql .= "                    FROM BC_MST_TORI_JI_TANTO AS F2 WITH(NOLOCK) ";
			$sql .= "                   WHERE F2.KAISHA_CD = F.KAISHA_CD ";
			$sql .= "                     AND F2.TORIHIKISAKI_CD = F.TORIHIKISAKI_CD ";
			$sql .= "                     AND F2.TORIHIKISAKI_TANTOUSHA_KBN = '01' ";
			$sql .= "                     AND ISNULL(F2.SHUTANTOU_FLG, 0) = 1 AND ISNULL(F2.DEL_FLG, 0) = 0 ";
			$sql .= "                   GROUP BY F2.KAISHA_CD, F2.TORIHIKISAKI_CD) ";

			$sql .= "  LEFT JOIN BT_VIEW_BUSHO AS G WITH(NOLOCK) ";
			$sql .= "    ON F.KAISHA_CD = G.KAISHA_CD ";
			$sql .= "   AND F.BUSHO_CD = G.BUSHO_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_SHAIN AS H WITH(NOLOCK) ";
			$sql .= "    ON F.KAISHA_CD = H.KAISHA_CD ";
			$sql .= "   AND F.SHAIN_CD = H.SHAIN_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_EIGYO_KYOTEN AS I WITH(NOLOCK) ";
			$sql .= "    ON G.KAISHA_CD = I.KAISHA_CD ";
			$sql .= "   AND G.EIGYO_KYOTEN_CD = I.CD_KEY ";

			$sql .= "  LEFT JOIN ";
			$sql .= "  ( ";
			$sql .= "    SELECT J.KAISHA_CD, J.TOKUISAKI_SNO ";
			$sql .= "          ,MIN(CONVERT(char(10), J.INS_TM, 111)) AS INS_TM ";
			$sql .= "          ,MIN(CONVERT(char(10), J2.NOUKI_YMD, 111)) AS NOUKI_YMD ";
			$sql .= "          ,SUM(SHOUKEI) AS SHOUKEI_SUM ";
			$sql .= "          ,SUM(ISNULL(GENKA,0) + ISNULL(GENKA_SONOTA,0)) AS GENKA ";
			$sql .= "          ,COUNT(*) AS MEISAI_CNT ";
			$sql .= "      FROM SD_TBL_DENPYOHDR AS J WITH(NOLOCK) ";
			$sql .= "      LEFT JOIN SD_TBL_DENPYODTL  AS J2 WITH(NOLOCK) ";
			$sql .= "        ON J.KAISHA_CD = J2.KAISHA_CD ";
			$sql .= "       AND J.DENPYO_NO = J2.DENPYO_NO ";
			$sql .= "       AND ISNULL(J2.DEL_FLG, 0) = 0 AND ISNULL(J2.TORIKESI_FLG, 0) = 0 ";
			$sql .= "     WHERE J.INS_TM >= '" . $arrParam["startdate"] . "'";
			$sql .= "       AND ISNULL(J.DEL_FLG, 0) = 0 AND ISNULL(J.TORIKESI_FLG, 0) = 0 ";
			$sql .= "     GROUP BY J.KAISHA_CD, J.TOKUISAKI_SNO";
			$sql .= "  ) AS J ";
			$sql .= "    ON B.KAISHA_CD = J.KAISHA_CD ";
			$sql .= "   AND B.TORIHIKISAKI_CD = J.TOKUISAKI_SNO ";

			$sql .= "  LEFT JOIN BT_VIEW_SETTEN_HOUHOU AS K WITH(NOLOCK) ";
			$sql .= "    ON K.KAISHA_CD = '999' ";
			$sql .= "   AND K.CD_KEY = A.HOUHOU_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_SETTEN_NAIYOU AS L WITH(NOLOCK) ";
			$sql .= "    ON L.KAISHA_CD = '999' ";
			$sql .= "   AND L.JOI_CD_KEY = '03' ";
			$sql .= "   AND L.CD_KEY = A.NAIYOU_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_TOIAWASE_TYPE AS M WITH(NOLOCK) ";
			$sql .= "    ON M.KAISHA_CD = '999' ";
			$sql .= "   AND M.CD_KEY = B.ANKEN_TYPE_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_GYOUSHU AS N WITH(NOLOCK) ";
			$sql .= "    ON N.KAISHA_CD = '999' ";
			$sql .= "   AND N.CD_KEY = E.GYOUSHU_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_GYOUSYU_DTL AS O WITH(NOLOCK) ";
			$sql .= "    ON O.KAISHA_CD = '999' ";
			$sql .= "   AND O.JOI_CD_KEY = E.GYOUSHU_CD ";
			$sql .= "   AND O.CD_KEY = E.GYOUSYU_DTL_CD ";

			$sql .= " WHERE ISNULL(A.DEL_FLG, 0) = 0 ";
			$sql .= "   AND A.HOUHOU_CD = '03' ";
			$sql .= "   AND A.NAIYOU_CD = '01' ";
			$sql .= "   AND A.SETTEN_YMD >= '" . $arrParam["startdate"] . "'";
			$sql .= "   AND A.SETTEN_YMD <= '" . $arrParam["enddate"] . "'";
			$sql .= "   AND B.ANKEN_TYPE_CD = '03' ";

			$sql .= " ORDER BY A.KAISHA_CD, A.ANKEN_NO ";

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

