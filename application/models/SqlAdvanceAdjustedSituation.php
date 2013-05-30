<?php

/**
 * SqlAdvanceAdjustedSituation for class
 *
 * 前受精算状況 SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlAdvanceAdjustedSituation
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
	 * 前受精算状況検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社CD, startdate:基準売上日)
	 * @return ture:成功、false:失敗
	 */
	public function SelectAdvanceAdjustedSituation($db, $arrParam = array())
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
			{ throw new Exception('SelectAdvanceAdjustedSituation', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["startdate"]) || empty($arrParam["startdate"]))
			{ throw new Exception('SelectAdvanceAdjustedSituation', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT ";
			$sql .= "       A.KAISHA_CD AS KAISHA_CD ";
			$sql .= "      ,A.SEIKYUSAKI_SNO AS SEIKYUSAKI_SNO ";
			$sql .= "      ,B.MEISAI_KINGAKU AS MEISAI_KINGAKU ";
			$sql .= "      ,B.MEISAI_TAX AS MEISAI_TAX ";
			$sql .= "      ,CONVERT(char(10), C.KESIKOMI_YMD, 111) AS KESIKOMI_YMD ";
			$sql .= "      ,CONVERT(char(10), B.NOUKI_YMD, 111) AS NOUKI_YMD ";
			$sql .= "      ,B.KESIKOMI_FLG AS KESIKOMI_FLG ";
			$sql .= "      ,A.MAEUKE_FLG AS MAEUKE_FLG ";
			$sql .= "      ,A.SEIKYU_NO AS SEIKYU_NO ";
			$sql .= "      ,B.SEIKYU_DTL_RENBAN AS SEIKYU_DTL_RENBAN ";

			$sql .= "  FROM DR_TBL_SEIKYUHDR AS A WITH(NOLOCK) ";

			$sql .= " INNER JOIN DR_TBL_SEIKYUDTL AS B WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.SEIKYU_NO = B.SEIKYU_NO ";
			$sql .= "   AND ISNULL(B.DEL_FLG, 0) = 0 AND ISNULL(B.KESIKOMI_FLG, 0) = 1 ";
			$sql .= "   AND B.NOUKI_YMD >= '" . $arrParam["startdate"] . "'";

			$sql .= " INNER JOIN DR_TBL_KESIKOMI AS C WITH(NOLOCK) ";
			$sql .= "    ON B.KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND B.SEIKYU_NO = C.SEIKYU_NO ";
			$sql .= "   AND B.SEIKYU_DTL_RENBAN = C.SEIKYU_DTL_RENBAN ";
			$sql .= "   AND ISNULL(C.DEL_FLG, 0) = 0 AND ISNULL(C.TORIKESI_FLG, 0) = 0 ";
			$sql .= "   AND C.KESIKOMI_YMD < '" . $arrParam["startdate"] . "'";

			$sql .= " WHERE ISNULL(A.DEL_FLG, 0) = 0 AND ISNULL(A.TORIKESI_FLG, 0) = 0 ";
			$sql .= "   AND A.KAISHA_CD ='" .  $arrParam["kcd"] . "'";

			$sql .= " ORDER BY A.KAISHA_CD, A.SEIKYUSAKI_SNO ";

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

