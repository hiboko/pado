<?php

/**
 * SqlSEditionList for class
 *
 * S版管理表 SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlSEditionList
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
	 * S版管理表情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, startdate:受注伝票納期(開始), enddate:受注伝票納期(終了), sstartdate:S版伝票納期(開始), 
	 *                                   senddate:S版伝票納期(終了), outsourcenm:業者名, connectcd:担当者コード)
	 * @return ture:成功、false:失敗
	 */
	public function SelectSEditionList($db, $arrParam = array())
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
			{ throw new Exception('SelectBusinessNameList', $clsComConst::ERR_CODE_400); }
			if((!isset($arrParam["startdate"]) || !isset($arrParam["enddate"])) && 
			   (!isset($arrParam["sstartdate"]) || !isset($arrParam["senddate"])))
			{ throw new Exception('SelectBusinessNameList', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT ";
			$sql .= "       C.HINMOKU_NO AS HINMOKU_NO ";
			$sql .= "      ,B.HINMOKU_NM AS HINMOKU_NM ";
			$sql .= "      ,C.TOKUISAKI_SNO AS TOKUISAKI_SNO ";
			$sql .= "      ,C.TORIHIKISAKI_NM AS TAISHO_MONTH ";
			$sql .= "      ,C.SEIKYUSAKI_SNO AS SEIKYUSAKI_SNO ";
			$sql .= "      ,C.SEIKYUSAKI_NM AS SEIKYUSAKI_NM ";
			$sql .= "      ,C.DISP_SMRY_BUSHO_CD AS DISP_SMRY_BUSHO_CD ";
			$sql .= "      ,C.BUSHO_NM AS BUSHO_NM ";
			$sql .= "      ,C.TANTO_SHA_NO AS TANTO_SHA_NO ";
			$sql .= "      ,C.SHAIN_NM AS SHAIN_NM ";
			$sql .= "      ,C.DENPYO_NO AS DENPYO_NO ";
			$sql .= "      ,C.MEISAI_NO AS MEISAI_NO ";
			$sql .= "      ,A.RIEKI_KANRI_NO AS RIEKI_KANRI_NO ";
			$sql .= "      ,B.RIEKI_KANRI_MEISAI_NO AS RIEKI_KANRI_MEISAI_NO ";
			$sql .= "      ,CONVERT(char(10),A.NOUKI, 111) AS ANOUKI ";
			$sql .= "      ,CONVERT(char(10),C.NOUKI_YMD, 111) AS JNOUKI ";
			$sql .= "      ,SHOUKEI AS SHOUKEI ";
			$sql .= "      ,TAX AS TAX ";
			$sql .= "      ,(C.SHOUKEI + C.TAX) AS GOUKEI ";
			$sql .= "      ,B.GAICHUSAKI AS GAICHUSAKI ";
			$sql .= "      ,B.GAICHU_KINGAKU AS GAICHU_KINGAKU ";
			$sql .= "      ,(B.GAICHU_KINGAKU * 0.05) AS GAICHU_KINGAKU_ZEI ";
			$sql .= "      ,(B.GAICHU_KINGAKU * 1.05) AS GAICHU_KINGAKU_ZEIKOMI ";

			$sql .= "  FROM ADSD_TBL_RIEKI_KANRI_DENPYOHDR AS A WITH(NOLOCK) ";

			$sql .= " INNER JOIN ADSD_TBL_RIEKI_KANRI_DENPYODTL AS B WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.RIEKI_KANRI_NO = B.RIEKI_KANRI_NO ";
			$sql .= "   AND ISNULL(B.DEL_FLG, 0) = 0 ";
			if(isset($arrParam["outsourcenm"]) && !empty($arrParam["outsourcenm"]))
			{
				$sql .= "   AND B.GAICHUSAKI like '%" . $arrParam["outsourcenm"] ."%' COLLATE Japanese_CI_AS_KS ";
			}

			$sql .= "  LEFT JOIN ";
			$sql .= "  ( ";
			$sql .= "    SELECT C4.RIEKI_KANRI_NO ";
			$sql .= "          ,C1.KAISHA_CD ";
			$sql .= "          ,C1.DENPYO_NO ";
			$sql .= "          ,C4.MEISAI_NO ";
			$sql .= "          ,CONVERT(char(10),C3.NOUKI_YMD,111)	AS NOUKI_YMD ";
			$sql .= "          ,C2.TOKUISAKI_SNO ";
			$sql .= "          ,C5.TORIHIKISAKI_NM ";
			$sql .= "          ,C2.SEIKYUSAKI_SNO ";
			$sql .= "          ,C6.TORIHIKISAKI_NM AS SEIKYUSAKI_NM ";
			$sql .= "          ,C3.HINMOKU_NO ";
			$sql .= "          ,C3.SHOUKEI ";
			$sql .= "          ,C3.TAX ";
			$sql .= "          ,C3.GENKA + C3.GENKA_SONOTA AS GENKA ";
			$sql .= "          ,C4.BAIKA ";
			$sql .= "          ,C2.KYOTEN_CD ";
			$sql .= "          ,C7.CD_NM ";
			$sql .= "          ,C8.DISP_SMRY_BUSHO_CD ";
			$sql .= "          ,C8.BUSHO_NM ";
			$sql .= "          ,C2.TANTO_SHA_NO ";
			$sql .= "          ,C9.SHAIN_NM ";
			$sql .= "     FROM ADSD_TBL_DENPYOHDR AS C1 WITH(NOLOCK) ";
			$sql .= "    INNER JOIN SD_TBL_DENPYOHDR AS C2 WITH(NOLOCK) ";
			$sql .= "       ON C1.KAISHA_CD = C2.KAISHA_CD ";
			$sql .= "      AND C1.DENPYO_NO = C2.DENPYO_NO ";
			$sql .= "      AND ISNULL(C2.DEL_FLG, 0) = 0 AND ISNULL(C2.TORIKESI_FLG, 0) = 0 ";
			$sql .= "    INNER JOIN SD_TBL_DENPYODTL AS C3 WITH(NOLOCK) ";
			$sql .= "       ON C1.KAISHA_CD = C3.KAISHA_CD ";
			$sql .= "      AND C1.DENPYO_NO = C3.DENPYO_NO ";
			$sql .= "      AND ISNULL(C3.DEL_FLG, 0) = 0 ";
			$sql .= "    INNER JOIN ADSD_TBL_DENPYODTL AS C4 WITH(NOLOCK) ";
			$sql .= "       ON C3.KAISHA_CD = C4.KAISHA_CD ";
			$sql .= "      AND C3.DENPYO_NO = C4.DENPYO_NO ";
			$sql .= "      AND C3.MEISAI_NO = C4.MEISAI_NO ";
			$sql .= "      AND ISNULL(C4.DEL_FLG, 0) = 0 ";
			$sql .= "     LEFT JOIN CM_MST_TORIHIKISAKI AS C5 WITH(NOLOCK) ";
			$sql .= "       ON C2.KAISHA_CD = C5.KAISHA_CD ";
			$sql .= "      AND C2.TOKUISAKI_SNO  = C5.TORIHIKISAKI_CD ";
			$sql .= "     LEFT JOIN CM_MST_TORIHIKISAKI AS C6 WITH(NOLOCK) ";
			$sql .= "       ON C2.KAISHA_CD = C6.KAISHA_CD ";
			$sql .= "      AND C2.SEIKYUSAKI_SNO  = C6.TORIHIKISAKI_CD ";
			$sql .= "     LEFT JOIN BT_VIEW_EIGYO_KYOTEN AS C7 WITH(NOLOCK) ";
			$sql .= "       ON C2.KAISHA_CD = C7.KAISHA_CD ";
			$sql .= "      AND C2.KYOTEN_CD = C7.CD_KEY ";
			$sql .= "     LEFT JOIN BT_VIEW_BUSHO AS C8 WITH(NOLOCK) ";
			$sql .= "       ON C2.KAISHA_CD = C8.KAISHA_CD ";
			$sql .= "      AND C2.TANTO_BUSHO_NO = C8.BUSHO_CD ";
			$sql .= "     LEFT JOIN BT_VIEW_SHAIN AS C9 WITH(NOLOCK) ";
			$sql .= "       ON C2.KAISHA_CD = C9.KAISHA_CD ";
			$sql .= "      AND C2.TANTO_SHA_NO = C9.SHAIN_CD ";
			$sql .= "    WHERE C1.KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "      AND ISNULL(C1.DEL_FLG, 0) = 0 ";
			$sql .= "  ) AS C ";
			$sql .= "    ON B.KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND B.RIEKI_KANRI_NO = C.RIEKI_KANRI_NO ";
			$sql .= " WHERE A.KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "   AND ISNULL(A.DEL_FLG, 0) = 0 AND ISNULL(A.TORIKESI_FLG, 0) = 0 "; 
			if(isset($arrParam["sstartdate"]) && !empty($arrParam["sstartdate"]))
			{
				$sql .= "   AND A.NOUKI >= '" . $arrParam["sstartdate"] . "'";
			}
			if(isset($arrParam["senddate"]) && !empty($arrParam["senddate"]))
			{
				$sql .= "   AND A.NOUKI <= '" . $arrParam["senddate"] . "'";
			}
			if(isset($arrParam["startdate"]) && !empty($arrParam["startdate"]))
			{
				$sql .= "   AND C.NOUKI_YMD >= '" . $arrParam["startdate"] . "'";
			}
			if(isset($arrParam["enddate"]) && !empty($arrParam["enddate"]))
			{
				$sql .= "   AND C.NOUKI_YMD <= '" . $arrParam["enddate"] . "'";
			}
			if(isset($arrParam["connectcd"]) && !empty($arrParam["connectcd"]))
			{
				$sql .= "   AND C.TANTO_SHA_NO = '" . $arrParam["connectcd"] ."'";
			}

			$sql .= " ORDER BY A.RIEKI_KANRI_NO, B.RIEKI_KANRI_MEISAI_NO ";

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

