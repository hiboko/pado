<?php

/**
 * SqlCaseRegisterSituation for class
 *
 * 事例登録状況 SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlCaseRegisterSituation
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
	 * 受注事例登録状況検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(startdate:受注伝票納期(開始), enddate:受注伝票納期(終了))
	 * @return ture:成功、false:失敗
	 */
	public function SelectCaseReceive($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();
		$blnRet = true;
		$blnSRet = true;

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["startdate"]) || empty($arrParam["startdate"]))
			{ throw new Exception('SelectCaseReceive', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["enddate"]) || empty($arrParam["enddate"]))
			{ throw new Exception('SelectCaseReceive', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT DISTINCT ";
			$sql .= "       A.JIREI_NO AS JIREI_NO ";
			$sql .= "      ,A.ANKEN_NO AS ANKEN_NO ";
			$sql .= "      ,CONVERT(char(10), A.HOKOKU_YMD, 111) AS HOKOKU_YMD ";
			$sql .= "      ,A.HOKOKU_KYOTEN_NM AS HOKOKU_KYOTEN_NM ";
			$sql .= "      ,A.HOKOKU_BUSHO_NM AS HOKOKU_BUSHO_NM ";
			$sql .= "      ,A.HOKOKU_SHAIN_CD AS HOKOKU_SHAIN_CD ";
			$sql .= "      ,ISNULL(R.SHAIN_NM, '') AS SHAIN_NM ";
			$sql .= "      ,ISNULL(O.CD_NM, '') AS TOPICS_NM ";
			$sql .= "      ,ISNULL(CONVERT(char(10), B.JUSHO_YMD, 111), '') AS JUSHO_YMD ";
			$sql .= "      ,C.TORIHIKISAKI_CD AS TORIHIKISAKI_CD ";
			$sql .= "      ,F.TORIHIKISAKI_NM AS TORIHIKISAKI_NM ";
			$sql .= "      ,H.BUSHO_NM AS BUSHO_NM ";
			$sql .= "      ,G.SHAIN_CD AS SHAIN_CD ";
			$sql .= "      ,I.SHAIN_NM AS SHAIN_NM ";
			$sql .= "      ,J.CD_NM AS GYOUSHU_NM ";
			$sql .= "      ,K.CD_NM AS GYOUSHU_SAIBUN_NM ";
			$sql .= "      ,L.CD_NM AS SINKYU_KOSIN ";
			$sql .= "      ,M.CD_NM AS HYOKA_NM ";
			$sql .= "      ,REPLACE(REPLACE(ISNULL(A.JUCHU_SICHU_KEII, ''), CHAR(13), ''), CHAR(10), '') AS JUCHU_SICHU_KEII ";
			$sql .= "      ,REPLACE(REPLACE(ISNULL(A.TALK_POINT, ''), CHAR(13), ''), CHAR(10), '') AS TALK_POINT ";
			$sql .= "      ,REPLACE(REPLACE(ISNULL(A.SONOTA_POINT, ''), CHAR(13), ''), CHAR(10), '') AS SONOTA_POINT ";

			$sql .= "  FROM ADCM_TBL_JIREI AS A WITH(NOLOCK) ";

			$sql .= "  LEFT JOIN ADCM_TBL_TOPIX AS B WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.TOPIX_NO = B.TOPIX_NO ";
			$sql .= "   AND ISNULL(B.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN CM_TBL_ANKEN_TORIHIKI AS C WITH(NOLOCK) ";
			$sql .= "    ON A.JOHOMOTO_KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND A.ANKEN_NO = C.ANKEN_NO ";
			$sql .= "   AND ISNULL(C.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN BT_VIEW_SETTEN_JIREI AS D WITH(NOLOCK) ";
			$sql .= "    ON C.KAISHA_CD = D.KAISHA_CD ";
			$sql .= "   AND C.ANKEN_NO = D.ANKEN_NO ";
			$sql .= "   AND ISNULL(D.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN ";
			$sql .= "  (";
			$sql .= "     SELECT '02' AS DENPYO_ORG_KBN, KAISHA_CD, DENPYO_NO ";
			$sql .= "       FROM SD_TBL_DENPYOHDR AS E2 WITH(NOLOCK) ";
			$sql .= "      WHERE ISNULL(E2.DEL_FLG, 0) = 0 AND ISNULL(E2.TORIKESI_FLG, 0) = 0 ";
			$sql .= "      UNION ";
			$sql .= "     SELECT '01' AS DENPYO_ORG_KBN, KAISHA_CD, DENPYO_NO ";
			$sql .= "       FROM SD_TBL_M_DENPYOHDR AS E3 WITH(NOLOCK) ";
			$sql .= "      WHERE ISNULL(E3.DEL_FLG, 0) = 0 AND ISNULL(E3.TORIKESI_FLG, 0) = 0 ";
			$sql .= "  ) AS E ";
			$sql .= "    ON D.KAISHA_CD = E.KAISHA_CD ";
			$sql .= "   AND D.DENPYO_NO = E.DENPYO_NO ";
			$sql .= "   AND D.TEJI_DENPYO_SHBT_CD = E.DENPYO_ORG_KBN ";

			$sql .= "  LEFT JOIN CM_MST_TORIHIKISAKI AS F WITH(NOLOCK) ";
			$sql .= "    ON C.KAISHA_CD = F.KAISHA_CD ";
			$sql .= "   AND C.TORIHIKISAKI_CD = F.TORIHIKISAKI_CD ";
			$sql .= "   AND ISNULL(F.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN CM_TBL_JISHA_TANTOUSHA AS G WITH(NOLOCK) ";
			$sql .= "    ON C.KAISHA_CD = G.KAISHA_CD ";
			$sql .= "   AND C.ANKEN_NO = G.ANKEN_NO ";
			$sql .= "   AND G.TORIHIKISAKI_TANTOUSHA_KBN = '01' ";
			$sql .= "   AND ISNULL(G.DEL_FLG, 0) = 0 AND ISNULL(G.MAIN_TANTOU_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN BT_VIEW_BUSHO AS H WITH(NOLOCK) ";
			$sql .= "    ON G.KAISHA_CD = H.KAISHA_CD ";
			$sql .= "   AND G.BUSHO_CD = H.BUSHO_CD ";

			$sql .= "  LEFT JOIN BC_MST_SHAIN AS I WITH(NOLOCK) ";
			$sql .= "    ON G.KAISHA_CD = I.KAISHA_CD ";
			$sql .= "   AND G.SHAIN_CD = I.SHAIN_CD ";
			$sql .= "   AND ISNULL(I.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN BT_VIEW_GYOUSHU_M AS J WITH(NOLOCK) ";
			$sql .= "    ON F.GYOUSHU_CD = J.CD_KEY ";
			$sql .= "   AND ISNULL(J.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN BT_VIEW_GYOUSYU_DTL_M AS K WITH(NOLOCK) ";
			$sql .= "    ON F.GYOUSHU_CD = K.JOI_CD_KEY ";
			$sql .= "   AND F.GYOUSYU_DTL_CD = K.CD_KEY ";
			$sql .= "   AND ISNULL(K.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN BC_MST_CODE_SYSTEM AS L WITH(NOLOCK) ";
			$sql .= "    ON L.KAISHA_CD = '999' ";
			$sql .= "   AND A.SINKYU_KOSIN_CD = L.CD_KEY ";
			$sql .= "   AND L.CD_SECTION = 'SINKYU_KOSIN' ";
			$sql .= "   AND L.JOI_CD_SECTION = '0' AND L.JOI_CD_KEY = '0' ";
			$sql .= "   AND ISNULL(L.DEL_FLG, 0) = 0 AND ISNULL(L.MUKOU_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN BC_MST_CODE_SYSTEM AS M WITH(NOLOCK) ";
			$sql .= "    ON  M.KAISHA_CD = '999' ";
			$sql .= "   AND A.HYOKA_CD = M.CD_KEY ";
			$sql .= "   AND M.CD_SECTION = 'HYOKA' ";
			$sql .= "   AND M.JOI_CD_SECTION = '0' AND M.JOI_CD_KEY = '0' ";
			$sql .= "   AND ISNULL(M.DEL_FLG, 0) = 0 AND ISNULL(M.MUKOU_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN BC_MST_CODE_KAISHA_BETU AS N WITH(NOLOCK) ";
			$sql .= "    ON C.KAISHA_CD = N.KAISHA_CD ";
			$sql .= "   AND H.EIGYO_KYOTEN_CD = N.CD_KEY ";
			$sql .= "   AND N.CD_SECTION = 'EIGYO_KYOTEN' ";
			$sql .= "   AND N.JOI_CD_SECTION = '0' AND N.JOI_CD_KEY = '0' ";
			$sql .= "   AND ISNULL(N.DEL_FLG, 0) = 0 AND ISNULL(N.MUKOU_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN BC_MST_CODE_SYSTEM AS O WITH(NOLOCK) ";
			$sql .= "    ON B.HYOKA_KBN = O.CD_KEY ";
			$sql .= "   AND O.CD_SECTION = 'TOPICS_HYOKA_KBN' ";
			$sql .= "   AND O.JOI_CD_SECTION = '0' AND O.JOI_CD_KEY = '0' ";
			$sql .= "   AND ISNULL(O.DEL_FLG, 0) = 0 AND ISNULL(O.MUKOU_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN ";
			$sql .= "  (";
			$sql .= "     SELECT '02' AS DENPYO_ORG_KBN, KAISHA_CD, DENPYO_NO, MEISAI_NO, ";
			$sql .= "            DAIBUNRUI_CD, CHUBUNRUI_CD, BUNRUI_CD_1 ";
			$sql .= "       FROM SD_TBL_DENPYODTL WITH(NOLOCK) ";
			$sql .= "      WHERE ISNULL(DEL_FLG, 0) = 0 ";
			$sql .= "      UNION ";
			$sql .= "     SELECT '01' AS DENPYO_ORG_KBN, KAISHA_CD, DENPYO_NO, MEISAI_NO, ";
			$sql .= "            DAIBUNRUI_CD, CHUBUNRUI_CD, BUNRUI_CD_1 ";
			$sql .= "       FROM SD_TBL_M_DENPYODTL WITH(NOLOCK) ";
			$sql .= "      WHERE ISNULL(DEL_FLG, 0) = 0 ";
			$sql .= "  ) AS P ";
			$sql .= "    ON E.KAISHA_CD = P.KAISHA_CD ";
			$sql .= "   AND E.DENPYO_NO = P.DENPYO_NO ";
			$sql .= "   AND E.DENPYO_ORG_KBN = P.DENPYO_ORG_KBN ";

			$sql .= "  LEFT JOIN ADMS_MST_JUCHU_SHUBETU AS Q WITH(NOLOCK) ";
			$sql .= "    ON P.DAIBUNRUI_CD = Q.DAIBUNRUI_CD ";
			$sql .= "   AND P.CHUBUNRUI_CD = Q.CHUBUNRUI_CD ";
			$sql .= "   AND P.BUNRUI_CD_1 = Q.JUCHU_SHUBETU_CD ";
			$sql .= "   AND ISNULL(Q.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN BT_VIEW_SHAIN AS R WITH(NOLOCK) ";
			$sql .= "    ON A.HOKOKU_KAISHA_CD = R.KAISHA_CD ";
			$sql .= "   AND A.HOKOKU_SHAIN_CD = R.SHAIN_CD ";

			$sql .= " WHERE ISNULL(A.DEL_FLG, 0) = 0 AND A.ANKEN_NO IS NOT NULL ";
			$sql .= "   AND CONVERT(char(10),A.HOKOKU_YMD,111) >= '" . $arrParam["startdate"] . "'";
			$sql .= "   AND CONVERT(char(10),A.HOKOKU_YMD,111) <= '" . $arrParam["enddate"] . "'";

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
	 * 反応事例登録状況検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(startdate:受注伝票納期(開始), enddate:受注伝票納期(終了))
	 * @return ture:成功、false:失敗
	 */
	public function SelectCaseReaction($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();
		$blnRet = true;
		$blnSRet = true;

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["startdate"]) || empty($arrParam["startdate"]))
			{ throw new Exception('SelectCaseReaction', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["enddate"]) || empty($arrParam["enddate"]))
			{ throw new Exception('SelectCaseReaction', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT ";
			$sql .= "       A.HANKYO_NO AS HANKYO_NO ";
			$sql .= "      ,A.DENPYO_NO AS DENPYO_NO ";
			$sql .= "      ,A.MEISAI_NO AS MEISAI_NO ";
			$sql .= "      ,E.KOUKOKUNUSHI_NO AS KOUKOKUNUSHI_NO ";
			$sql .= "      ,N.TORIHIKISAKI_NM AS TORIHIKISAKI_NM ";
			$sql .= "      ,CASE ";
			$sql .= "          WHEN L.HINMOKU_TYPE_CD = '1' THEN ISNULL(D.KOUKOKU_NM, '') ";
			$sql .= "          WHEN L.HINMOKU_TYPE_CD = '2' THEN ISNULL(E.TIRASI_NM, '') ";
			$sql .= "          ELSE '' ";
			$sql .= "       END AS KOUKOKU_NM ";
			$sql .= "      ,ISNULL(N.TEL, '') AS TEL ";
			$sql .= "      ,CONVERT(char(10),B.NOUKI_YMD,111) AS NOUKI_YMD ";
			$sql .= "      ,A.HOKOKU_KYOTEN_NM AS HOKOKU_KYOTEN_NM ";
			$sql .= "      ,A.HOKOKU_BUSHO_NM AS HOKOKU_BUSHO_NM ";
			$sql .= "      ,A.HOKOKU_SHAIN_NO AS HOKOKU_SHAIN_NO ";
			$sql .= "      ,ISNULL(H.SHAIN_NM, '') AS SHAIN_NM ";
			$sql .= "      ,ISNULL(V.CD_NM, '') AS GYOSYU_NM ";
			$sql .= "      ,ISNULL(W.CD_NM, '') AS GYOSYU_SAIBUN ";
			$sql .= "      ,ISNULL(G.TOKUSHU_NM, '') AS TOKUSHU_NM ";
			$sql .= "      ,ISNULL(X.CD_NM, '') AS SIZE_NM ";
			$sql .= "      ,dbo.PADO_MK_AREA_CD_STR(A.JOHOMOTO_KAISHA_CD, A.DENPYO_NO, A.MEISAI_NO, A.HANSITA_RENBAN, L.HINMOKU_TYPE_CD) AS AREA ";
			$sql .= "      ,ISNULL(Y.CD_NM, '') AS JYANRU_NM ";
			$sql .= "      ,ISNULL(Z.CD_NM, '') AS CATEGORY_NM ";
			$sql .= "      ,ISNULL(P.CD_NM, '') AS CL_NM ";
			$sql .= "      ,ISNULL(A.TOIAWASE_CNT, '') AS TOIAWASE_CNT ";
			$sql .= "      ,ISNULL(A.RAITEN_SEIYAKU_CNT, '') AS RAITEN_SEIYAKU_CNT ";
			$sql .= "      ,ISNULL(Q.CD_NM, '') AS RAITEN_SEIYAKU_NM ";
			$sql .= "      ,ISNULL(AA.CD_NM, '') AS NENREISOU_NM ";
			$sql .= "      ,ISNULL(BB.CD_NM, '') AS ZOKUSEI_NM ";
			$sql .= "      ,ISNULL(CC.CD_NM, '') AS DANJOHI_NM ";
			$sql .= "      ,ISNULL(DD.CD_NM, '') AS KIZONKYAKU_NM ";
			$sql .= "      ,ISNULL(R.CD_NM, '') AS KADAI_JAKUTEN1_NM ";
			$sql .= "      ,ISNULL(S.CD_NM, '') AS KADAI_JAKUTEN2_NM ";
			$sql .= "      ,ISNULL(T.CD_NM, '') AS KADAI_JAKUTEN3_NM ";
			$sql .= "      ,ISNULL(U.CD_NM, '') AS SERVICE_NAIYO_NM ";
			$sql .= "      ,REPLACE(REPLACE(ISNULL(A.SERVICE_NAIYO, ''), CHAR(13), ''), CHAR(10), '') AS SERVICE_NAIYO ";
			$sql .= "      ,REPLACE(REPLACE(ISNULL(A.HAMNO_POINT, ''), CHAR(13), ''), CHAR(10), '') AS HAMNO_POINT ";
			$sql .= "      ,CONVERT(char(10), A.INS_TM, 111) AS INS_TM ";
			$sql .= "      ,CONVERT(char(10), A.UPD_TM, 111) AS UPD_TM ";
			$sql .= "      ,M.KEISAIHAN_NM AS KEISAIHAN_NM ";
			$sql .= "      ,L.JUCHU_SHUBETU_NM AS JUCHU_SHUBETU_NM ";
			$sql .= "      ,ISNULL(A.HANSHITA_NO, '') AS HANSHITA_NO ";
			$sql .= "      ,J.DAIBUNRUI_NM AS DAIBUNRUI_NM ";
			$sql .= "      ,K.CHUBUNRUI_NM AS CHUBUNRUI_NM ";

			$sql .= "  FROM ADCM_TBL_HANKYO AS A WITH(NOLOCK) ";

			$sql .= " INNER JOIN SD_TBL_DENPYODTL AS B WITH(NOLOCK) ";
			$sql .= "    ON A.JOHOMOTO_KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.DENPYO_NO = B.DENPYO_NO ";
			$sql .= "   AND A.MEISAI_NO = B.MEISAI_NO ";
			$sql .= "   AND ISNULL(B.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN ADSD_TBL_KOUKOKUKEISAI AS C WITH(NOLOCK) ";
			$sql .= "    ON A.JOHOMOTO_KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND A.DENPYO_NO = C.DENPYO_NO ";
			$sql .= "   AND A.MEISAI_NO = C.MEISAI_NO ";
			$sql .= "   AND ISNULL(C.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN ADSD_TBL_KOUKOKUKEISAI_HANSITA AS D WITH(NOLOCK) ";
			$sql .= "    ON A.JOHOMOTO_KAISHA_CD = D.KAISHA_CD ";
			$sql .= "   AND A.DENPYO_NO = D.DENPYO_NO ";
			$sql .= "   AND A.MEISAI_NO = D.MEISAI_NO ";
			$sql .= "   AND A.HANSITA_RENBAN = D.HANSITA_RENBAN ";
			$sql .= "   AND ISNULL(D.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN ADSD_TBL_DENPYODTL AS E WITH(NOLOCK) ";
			$sql .= "    ON A.JOHOMOTO_KAISHA_CD = E.KAISHA_CD ";
			$sql .= "   AND A.DENPYO_NO = E.DENPYO_NO ";
			$sql .= "   AND A.MEISAI_NO = E.MEISAI_NO ";
			$sql .= "   AND ISNULL(E.DEL_FLG, 0) = 0 ";

			$sql .= " INNER JOIN SD_TBL_DENPYOHDR AS F WITH(NOLOCK) ";
			$sql .= "    ON B.KAISHA_CD = F.KAISHA_CD ";
			$sql .= "   AND B.DENPYO_NO = F.DENPYO_NO ";
			$sql .= "   AND ISNULL(F.DEL_FLG, 0) = 0 AND ISNULL(F.TORIKESI_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN AD_VIEW_MST_TOKUSHU AS G WITH(NOLOCK) ";
			$sql .= "    ON  C.KAISHA_CD = G.KAISHA_CD ";
			$sql .= "   AND E.KEISAIHAN_CD = G.KEISAIHAN_CD ";
			$sql .= "   AND C.TOKUSHU_NO = G.TOKUSHU_NO ";
			$sql .= "   AND ISNULL(G.DEL_FLG, 0) = 0 AND ISNULL(G.MUKOU_FLG, 0) = 0 ";

			$sql .= " INNER JOIN BT_VIEW_SHAIN AS H WITH(NOLOCK) ";
			$sql .= "    ON A.HOKOKU_KAISHA_CD = H.KAISHA_CD ";
			$sql .= "   AND A.HOKOKU_SHAIN_NO = H.SHAIN_CD ";

			$sql .= "  LEFT JOIN ADCM_TBL_TOPIX AS I WITH(NOLOCK) ";
			$sql .= "    ON A.TOPIX_NO = I.TOPIX_NO ";
			$sql .= "   AND ISNULL(I.DEL_FLG, 0) = 0 ";

			$sql .= " INNER JOIN AD_VIEW_MST_DAIBUNRUI AS J WITH(NOLOCK) ";
			$sql .= "    ON  J.KAISHA_CD = '999' ";
			$sql .= "   AND B.DAIBUNRUI_CD = J.DAIBUNRUI_CD ";

			$sql .= "  LEFT JOIN AD_VIEW_MST_CHUBUNRUI AS K WITH(NOLOCK) ";
			$sql .= "    ON K.KAISHA_CD = '999' ";
			$sql .= "   AND B.DAIBUNRUI_CD = K.DAIBUNRUI_CD ";
			$sql .= "   AND B.CHUBUNRUI_CD = K.CHUBUNRUI_CD ";

			$sql .= "  LEFT JOIN AD_VIEW_MST_JUCHU_SHUBETU AS L WITH(NOLOCK) ";
			$sql .= "    ON L.KAISHA_CD = '999' ";
			$sql .= "   AND B.DAIBUNRUI_CD = L.DAIBUNRUI_CD ";
			$sql .= "   AND B.CHUBUNRUI_CD = L.CHUBUNRUI_CD ";
			$sql .= "   AND B.BUNRUI_CD_1  = L.JUCHU_SHUBETU_CD ";

			$sql .= "  LEFT JOIN ADMS_MST_KEISAIHAN AS M WITH(NOLOCK) ";
			$sql .= "    ON M.KAISHA_CD = E.KAISHA_CD ";
			$sql .= "   AND M.KEISAIHAN_CD = E.KEISAIHAN_CD ";
			$sql .= "   AND ISNULL(M.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN CM_MST_TORIHIKISAKI AS N WITH(NOLOCK) ";
			$sql .= "    ON E.KAISHA_CD = N.KAISHA_CD ";
			$sql .= "   AND E.KOUKOKUNUSHI_NO = N.TORIHIKISAKI_CD ";
			$sql .= "   AND ISNULL(N.DEL_FLG, 0) = 0 ";

			$sql .= " INNER JOIN BC_MST_SYSTEM AS O WITH(NOLOCK) ";
			$sql .= "    ON O.KAISHA_CD = A.JOHOMOTO_KAISHA_CD ";
			$sql .= "   AND O.BUNRUI_NM = 'CO' AND O.VALUE_NM = 'PUBLIC_KAISHA_CD' ";
			$sql .= "   AND ISNULL(O.DEL_FLG, 0) = 0 ";

			$sql .= "  LEFT JOIN BT_VIEW_MANZOKUDO_M AS P WITH(NOLOCK) ";
			$sql .= "    ON P.KAISHA_CD = O.VALUE ";
			$sql .= "   AND P.CD_KEY = A.MANZOKUDO_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_RAITEN_SEIYAKU_M AS Q WITH(NOLOCK) ";
			$sql .= "    ON Q.KAISHA_CD = O.VALUE ";
			$sql .= "   AND Q.CD_KEY = A.RAITEN_SEIYAKU_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_KADAI_JAKUTEN_M AS R WITH(NOLOCK) ";
			$sql .= "    ON R.KAISHA_CD = O.VALUE ";
			$sql .= "   AND R.CD_KEY = A.KADAI_JAKUTEN_CD_1 ";

			$sql .= "  LEFT JOIN BT_VIEW_KADAI_JAKUTEN_M AS S WITH(NOLOCK) ";
			$sql .= "    ON S.KAISHA_CD = O.VALUE ";
			$sql .= "   AND S.CD_KEY = A.KADAI_JAKUTEN_CD_2 ";

			$sql .= "  LEFT JOIN BT_VIEW_KADAI_JAKUTEN_M AS T WITH(NOLOCK) ";
			$sql .= "    ON T.KAISHA_CD = O.VALUE ";
			$sql .= "   AND T.CD_KEY    = A.KADAI_JAKUTEN_CD_3 ";

			$sql .= "  LEFT JOIN BT_VIEW_SERVICE_NAIYO_M AS U WITH(NOLOCK) ";
			$sql .= "    ON U.KAISHA_CD = O.VALUE ";
			$sql .= "   AND U.CD_KEY = A.SERVICE_NAIYO_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_GYOUSHU_M AS V WITH(NOLOCK) ";
			$sql .= "    ON V.KAISHA_CD = O.VALUE ";
			$sql .= "   AND V.CD_KEY = N.GYOUSHU_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_GYOUSYU_DTL_M AS W WITH(NOLOCK) ";
			$sql .= "    ON W.KAISHA_CD = O.VALUE ";
			$sql .= "   AND W.JOI_CD_KEY = N.GYOUSHU_CD ";
			$sql .= "   AND W.CD_KEY = N.GYOUSYU_DTL_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_SIZE AS X WITH(NOLOCK) ";
			$sql .= "    ON X.KAISHA_CD = O.VALUE ";
			$sql .= "   AND X.CD_KEY = E.SIZE_CD ";

			$sql .= "  LEFT JOIN AD_VIEW_GENRE_M AS Y WITH(NOLOCK) ";
			$sql .= "    ON Y.KAISHA_CD = O.VALUE ";
			$sql .= "   AND Y.CD_KEY = E.GENRE_CD ";

			$sql .= "  LEFT JOIN AD_VIEW_CATEGORY AS Z WITH(NOLOCK) ";
			$sql .= "    ON  Z.KAISHA_CD = O.VALUE ";
			$sql .= "   AND Z.CD_KEY = E.CATEGORY_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_NENREISOU AS AA WITH(NOLOCK) ";
			$sql .= "    ON AA.KAISHA_CD = O.VALUE ";
			$sql .= "   AND AA.CD_KEY = A.NENREISOU_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_ZOKUSEI AS BB WITH(NOLOCK) ";
			$sql .= "    ON BB.KAISHA_CD = O.VALUE ";
			$sql .= "   AND BB.CD_KEY = A.NENREISOU_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_DANJOHI AS CC WITH(NOLOCK) ";
			$sql .= "    ON CC.KAISHA_CD = O.VALUE ";
			$sql .= "   AND CC.CD_KEY = A.DANJOHI_CD ";

			$sql .= "  LEFT JOIN BT_VIEW_KIZONKYAKU AS DD WITH(NOLOCK) ";
			$sql .= "    ON DD.KAISHA_CD = O.VALUE ";
			$sql .= "   AND DD.CD_KEY = A.KIZONKYAKU_CD ";

			$sql .= " WHERE ISNULL(A.DEL_FLG, 0) = 0 ";
			$sql .= "   AND CONVERT(char(10), A.HOKOKU_YMD, 111) >= '" . $arrParam["startdate"] . "'";
			$sql .= "   AND CONVERT(char(10), A.HOKOKU_YMD, 111) <= '" . $arrParam["enddate"] . "'";

			$sql .= " ORDER BY A.HANKYO_NO, A.DENPYO_NO, A.MEISAI_NO ";

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

