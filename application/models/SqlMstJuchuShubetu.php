<?php

/**
 * SqlMstJuchuShubetu for class
 *
 * 受注種別情報 SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlMstJuchuShubetu
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
	 * 大分類情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(serch:検索項目)
	 * @return 
	 */
	public function SelectMstDaibunrui($db, $arrParam = array())
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

			$sql = " SELECT DAIBUNRUI_CD AS codeid ";
			$sql .= "      ,DAIBUNRUI_NM AS codename ";
			$sql .= "  FROM AD_VIEW_MST_DAIBUNRUI WITH(NOLOCK) ";
			$sql .= " WHERE KAISHA_CD = '999'";
			$sql .= "   AND ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			if(isset($arrParam["serch"]) && !empty($arrParam["serch"]))
			{
				$sql .= "   AND (DAIBUNRUI_CD like '%" . $arrParam["serch"] . "%'";
				$sql .= "    OR  DAIBUNRUI_NM like '%" . $arrParam["serch"] . "%')";
			}

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
	 * 中分類情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(dcd:大分類コード, serch:検索項目)
	 * @return 
	 */
	public function SelectMstChubunrui($db, $arrParam = array())
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

			$sql = " SELECT D.DAIBUNRUI_CD + C.CHUBUNRUI_CD AS codeid ";
			$sql .= "      ,C.CHUBUNRUI_NM AS codename ";
			$sql .= "      ,D.DAIBUNRUI_CD AS dcodeid ";
			$sql .= "      ,D.DAIBUNRUI_NM AS dname ";
			$sql .= "      ,C.CHUBUNRUI_CD AS ccodeid ";
			$sql .= "  FROM AD_VIEW_MST_DAIBUNRUI AS D WITH(NOLOCK) ";
			$sql .= " INNER JOIN AD_VIEW_MST_CHUBUNRUI AS C WITH(NOLOCK)";
			$sql .= "    ON C.DAIBUNRUI_CD = D.DAIBUNRUI_CD";
			$sql .= "   AND C.KAISHA_CD = '999'";
			$sql .= "   AND ISNULL(C.DEL_FLG, 0) = 0 AND ISNULL(C.MUKOU_FLG, 0) = 0 ";
			$sql .= " WHERE D.KAISHA_CD = '999' ";
			$sql .= "   AND ISNULL(D.DEL_FLG, 0) = 0 AND ISNULL(D.MUKOU_FLG, 0) = 0 ";
			if(isset($arrParam["dcd"]) && !empty($arrParam["dcd"]))
			{
				$sql .= "  AND D.DAIBUNRUI_CD = '" . $arrParam["dcd"] . "'";
			}
			if(isset($arrParam["serch"]) && !empty($arrParam["serch"]))
			{
				if(isset($arrParam["dcd"]) && !empty($arrParam["dcd"]))
				{
					$sql .= "   AND (C.CHUBUNRUI_CD like '%" . $arrParam["serch"] . "%'";
					$sql .= "    OR  C.CHUBUNRUI_NM like '%" . $arrParam["serch"] . "%')";
				}
				else
				{
					$sql .= "   AND (D.DAIBUNRUI_CD like '%" . $arrParam["serch"] . "%'";
					$sql .= "    OR  C.CHUBUNRUI_CD like '%" . $arrParam["serch"] . "%'";
					$sql .= "    OR  C.CHUBUNRUI_NM like '%" . $arrParam["serch"] . "%')";
				}
			}
			$sql .= " ORDER BY D.DAIBUNRUI_CD, C.CHUBUNRUI_CD ";

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
	 * 種別情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(dcd:大分類コード, ccd:中分類コード, serch:検索項目)
	 * @return 
	 */
	public function SelectMstJuchuShubetu($db, $arrParam = array())
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

			$sql = " SELECT D.DAIBUNRUI_CD + C.CHUBUNRUI_CD + S.JUCHU_SHUBETU_CD AS codeid ";
			$sql .= "      ,S.JUCHU_SHUBETU_NM AS codename ";
			$sql .= "      ,D.DAIBUNRUI_CD AS dcodeid ";
			$sql .= "      ,D.DAIBUNRUI_NM AS dname ";
			$sql .= "      ,C.CHUBUNRUI_CD AS ccodeid ";
			$sql .= "      ,C.CHUBUNRUI_NM AS cname ";
			$sql .= "      ,S.JUCHU_SHUBETU_CD AS scodeid ";
			$sql .= "  FROM AD_VIEW_MST_DAIBUNRUI AS D WITH(NOLOCK) ";
			$sql .= " INNER JOIN AD_VIEW_MST_CHUBUNRUI AS C WITH(NOLOCK) ";
			$sql .= "    ON C.DAIBUNRUI_CD = D.DAIBUNRUI_CD ";
			$sql .= "   AND C.KAISHA_CD = '999'";
			$sql .= "   AND ISNULL(C.DEL_FLG, 0) = 0 AND ISNULL(C.MUKOU_FLG, 0) = 0 ";
			$sql .= " INNER JOIN AD_VIEW_MST_JUCHU_SHUBETU AS S WITH(NOLOCK) ";
			$sql .= "    ON S.DAIBUNRUI_CD = C.DAIBUNRUI_CD ";
			$sql .= "   AND S.CHUBUNRUI_CD = C.CHUBUNRUI_CD ";
			$sql .= "   AND S.KAISHA_CD = '999'";
			$sql .= "   AND ISNULL(S.DEL_FLG, 0) = 0 AND ISNULL(S.MUKOU_FLG, 0) = 0 ";
			$sql .= " WHERE D.KAISHA_CD = '999' ";
			$sql .= "   AND ISNULL(D.DEL_FLG, 0) = 0 AND ISNULL(D.MUKOU_FLG, 0) = 0 ";
			if(isset($arrParam["dcd"]) && !empty($arrParam["dcd"]))
			{
				$sql .= "   AND D.DAIBUNRUI_CD = '" . $arrParam["dcd"] . "'";
			}
			if(isset($arrParam["ccd"]) && !empty($arrParam["ccd"]))
			{
				$sql .= "   AND C.CHUBUNRUI_CD = '" . $arrParam["ccd"] . "'";
			}
			if(isset($arrParam["serch"]) && !empty($arrParam["serch"]))
			{
				if(isset($arrParam["ccd"]) && !empty($arrParam["ccd"]))
				{
					$sql .= "   AND (S.JUCHU_SHUBETU_CD like '%" . $arrParam["serch"] . "%'";
					$sql .= "    OR  S.JUCHU_SHUBETU_NM like '%" . $arrParam["serch"] . "%')";
				}
				else if(isset($arrParam["dcd"]) && !empty($arrParam["dcd"]))
				{
					$sql .= "   AND (C.CHUBUNRUI_CD like '%" . $arrParam["serch"] . "%'";
					$sql .= "    OR  S.JUCHU_SHUBETU_CD like '%" . $arrParam["serch"] . "%'";
					$sql .= "    OR  S.JUCHU_SHUBETU_NM like '%" . $arrParam["serch"] . "%')";
				}
				else
				{
					$sql .= "   AND (D.DAIBUNRUI_CD like '%" . $arrParam["serch"] . "%'";
					$sql .= "    OR  C.CHUBUNRUI_CD like '%" . $arrParam["serch"] . "%'";
					$sql .= "    OR  S.JUCHU_SHUBETU_CD like '%" . $arrParam["serch"] . "%'";
					$sql .= "    OR  S.JUCHU_SHUBETU_NM like '%" . $arrParam["serch"] . "%')";
				}
			}
			$sql .= " ORDER BY D.DAIBUNRUI_CD, C.CHUBUNRUI_CD, S.JUCHU_SHUBETU_CD ";

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

