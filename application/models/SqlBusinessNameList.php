<?php

/**
 * SqlBusinessNameList for class
 *
 * 商売名人リスト SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlBusinessNameList
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
	 * 商売名人リスト情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, startdate:納期(開始), enddate:納期(終了))
	 * @return ture:成功、false:失敗
	 */
	public function SelectBusinessNameList($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectBusinessNameList', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["startdate"]) || empty($arrParam["startdate"]))
			{ throw new Exception('SelectBusinessNameList', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["enddate"]) || empty($arrParam["enddate"]))
			{ throw new Exception('SelectBusinessNameList', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT ";
			$sql .= "       A.DENPYO_NO AS DENPYO_NO ";
			$sql .= "      ,D.MEISAI_NO AS MEISAI_NO ";
			$sql .= "      ,D.KEISAIHAN_CD AS KEISAIHAN_CD ";
			$sql .= "      ,CONVERT(CHAR(10), G.TAISHO_MONTH, 111) AS TAISHO_MONTH ";
			$sql .= "      ,CONVERT(CHAR(10), C.NOUKI_YMD, 111) AS NOUKI_YMD ";
			$sql .= "      ,CONVERT(CHAR(10), H.SERVICE_START_YMD, 111) AS SERVICE_START_YMD ";
			$sql .= "      ,CONVERT(CHAR(10), H.SERVICE_END_YMD, 111) AS SERVICE_END_YMD ";
			$sql .= "      ,CONVERT(CHAR(10), H.KAKIN_START_YMD, 111) AS KAKIN_START_YMD ";
			$sql .= "      ,CONVERT(CHAR(10), H.KAKIN_END_YMD, 111) AS KAKIN_END_YMD ";
			$sql .= "      ,ISNULL(H.KAIYAKU_RIYU, '') AS KAIYAKU_RIYU ";
			$sql .= "      ,B.TOKUISAKI_SNO AS TOKUISAKI_SNO ";
			$sql .= "      ,I.TORIHIKISAKI_NM AS TORIHIKISAKI_NM ";
			$sql .= "      ,ISNULL(H.TENPO_NO, '') AS TENPO_NO ";
			$sql .= "      ,ISNULL(J.TENPO_NM, '') AS TENPO_NM ";
			$sql .= "      ,ISNULL(J.POINT_TENPO_ID, '') AS POINT_TENPO_ID ";
			$sql .= "      ,C.DAIBUNRUI_CD AS DAIBUNRUI_CD ";
			$sql .= "      ,K.DAIBUNRUI_NM AS DAIBUNRUI_NM ";
			$sql .= "      ,C.CHUBUNRUI_CD AS CHUBUNRUI_CD ";
			$sql .= "      ,L.CHUBUNRUI_NM AS CHUBUNRUI_NM ";
			$sql .= "      ,C.BUNRUI_CD_1 AS BUNRUI_CD_1 ";
			$sql .= "      ,M.JUCHU_SHUBETU_NM AS JUCHU_SHUBETU_NM ";
			$sql .= "      ,C.SHOUKEI AS SHOUKEI ";
			$sql .= "      ,C.TAX AS TAX ";
			$sql .= "      ,(C.GENKA + C.GENKA_SONOTA) AS GENKA ";
			$sql .= "      ,CASE ISNULL(A.SHOUNINZUMI_FLG, '') ";
			$sql .= "            WHEN '1' THEN '確定' ";
			$sql .= "            ELSE '登録' ";
			$sql .= "       END AS JYOKYO ";
			$sql .= "      ,B.KYOTEN_CD AS KYOTEN_CD ";
			$sql .= "      ,N.CD_NM AS CD_NM ";
			$sql .= "      ,O.DISP_SMRY_BUSHO_CD AS DISP_SMRY_BUSHO_CD ";
			$sql .= "      ,O.BUSHO_NM AS BUSHO_NM ";
			$sql .= "      ,B.TANTO_SHA_NO AS TANTO_SHA_NO ";
			$sql .= "      ,P.SHAIN_NM AS SHAIN_NM ";
			$sql .= "  FROM ADSD_TBL_DENPYOHDR AS A WITH(NOLOCK) ";

			$sql .= " INNER JOIN SD_TBL_DENPYOHDR AS B WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.DENPYO_NO = B.DENPYO_NO ";
			$sql .= "   AND ISNULL(B.DEL_FLG, 0) = 0 AND ISNULL(B.TORIKESI_FLG, 0) = 0 ";

			$sql .= " INNER JOIN SD_TBL_DENPYODTL AS C WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND A.DENPYO_NO = C.DENPYO_NO ";
			$sql .= "   AND ISNULL(C.DEL_FLG, 0) = 0 AND C.DAIBUNRUI_CD = '51' ";
			$sql .= "   AND C.NOUKI_YMD >= '" . $arrParam["startdate"] . "'";
			$sql .= "   AND C.NOUKI_YMD <= '" . $arrParam["enddate"] . "'";

			$sql .= " INNER JOIN ADSD_TBL_DENPYODTL AS D WITH(NOLOCK) ";
			$sql .= "    ON C.KAISHA_CD = D.KAISHA_CD ";
			$sql .= "   AND C.DENPYO_NO = D.DENPYO_NO ";
			$sql .= "   AND C.MEISAI_NO = D.MEISAI_NO ";
			$sql .= "   AND ISNULL(D.DEL_FLG, 0) = 0 ";

			$sql .= " INNER JOIN SD_TBL_DENPYODTL_STATUS AS E WITH(NOLOCK) ";
			$sql .= "    ON C.KAISHA_CD = E.KAISHA_CD ";
			$sql .= "   AND C.DENPYO_NO = E.DENPYO_NO ";
			$sql .= "   AND C.MEISAI_NO = E.MEISAI_NO ";
			$sql .= "   AND ISNULL(E.DEL_FLG, 0) = 0 ";

			$sql .= " INNER JOIN ADSD_TBL_DENPYODTL_STATUS AS F WITH(NOLOCK) ";
			$sql .= "    ON C.KAISHA_CD = F.KAISHA_CD ";
			$sql .= "   AND C.DENPYO_NO = F.DENPYO_NO ";
			$sql .= "   AND C.MEISAI_NO = F.MEISAI_NO ";
			$sql .= "   AND ISNULL(F.DEL_FLG, 0) = 0 ";

			$sql .= " INNER JOIN ADSD_TBL_KIKAN_KEYAKU AS G WITH(NOLOCK) ";
			$sql .= "    ON C.KAISHA_CD = G.KAISHA_CD ";
			$sql .= "   AND C.DENPYO_NO = G.DENPYO_NO ";
			$sql .= "   AND C.MEISAI_NO = G.MEISAI_NO ";
			$sql .= "   AND ISNULL(G.DEL_FLG, 0) = 0 AND NOT G.DENPYO_NO IS NULL ";

			$sql .= " INNER JOIN ADSD_TBL_KIKAN_KEYAKU_HDR AS H WITH(NOLOCK) ";
			$sql .= "    ON G.KAISHA_CD = H.KAISHA_CD ";
			$sql .= "   AND G.DENPYO_NO = H.DENPYO_NO ";
			$sql .= "   AND G.RENBAN = H.RENBAN ";
			$sql .= "   AND ISNULL(H.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN CM_MST_TORIHIKISAKI AS I WITH(NOLOCK) ";
			$sql .= "    ON  B.KAISHA_CD = I.KAISHA_CD ";
			$sql .= "   AND B.TOKUISAKI_SNO = I.TORIHIKISAKI_CD ";

			$sql .= "  LEFT JOIN CM_MST_TOKUISAKI_TENPO_INFO AS J WITH(NOLOCK) ";
			$sql .= "    ON B.KAISHA_CD = J.KAISHA_CD ";
			$sql .= "   AND B.TOKUISAKI_SNO = J.TOKUISAKI_SNO ";
			$sql .= "   AND H.TENPO_NO = J.TENPO_NO ";

			$sql .= "  LEFT JOIN AD_VIEW_MST_DAIBUNRUI AS K WITH(NOLOCK) ";
			$sql .= "    ON C.DAIBUNRUI_CD = K.DAIBUNRUI_CD ";
			$sql .= "   AND K.KAISHA_CD = '999' ";

			$sql .= "  LEFT JOIN AD_VIEW_MST_CHUBUNRUI AS L WITH(NOLOCK) ";
			$sql .= "    ON C.DAIBUNRUI_CD = L.DAIBUNRUI_CD ";
			$sql .= "   AND C.CHUBUNRUI_CD = L.CHUBUNRUI_CD ";
			$sql .= "   AND L.KAISHA_CD = '999' ";

			$sql .= "  LEFT JOIN AD_VIEW_MST_JUCHU_SHUBETU AS M WITH(NOLOCK) ";
			$sql .= "    ON C.DAIBUNRUI_CD = M.DAIBUNRUI_CD ";
			$sql .= "   AND C.CHUBUNRUI_CD = M.CHUBUNRUI_CD ";
			$sql .= "   AND C.BUNRUI_CD_1  = M.JUCHU_SHUBETU_CD ";
			$sql .= "   AND M.KAISHA_CD = '999' ";

			$sql .= "  LEFT JOIN BT_VIEW_EIGYO_KYOTEN AS N WITH(NOLOCK) ";
			$sql .= "    ON B.KAISHA_CD = N.KAISHA_CD ";
			$sql .= "   AND B.KYOTEN_CD = N.CD_KEY ";

			$sql .= "  LEFT JOIN BT_VIEW_BUSHO AS O WITH(NOLOCK) ";
			$sql .= "    ON B.KAISHA_CD = O.KAISHA_CD ";
			$sql .= "   AND B.TANTO_BUSHO_NO = O.BUSHO_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_SHAIN AS P WITH(NOLOCK) ";
			$sql .= "    ON B.KAISHA_CD = P.KAISHA_CD ";
			$sql .= "   AND B.TANTO_SHA_NO = P.SHAIN_CD ";

			$sql .= " WHERE A.KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "   AND ISNULL(A.DEL_FLG, 0) = 0 "; 
			$sql .= " ORDER BY A.DENPYO_NO, D.MEISAI_NO ";

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

