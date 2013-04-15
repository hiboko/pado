<?php

/**
 * SqlMstTorihikisaki for class
 *
 * 顧客情報 SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlMstTorihikisaki
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
	 * 社員名取得
	 */
	public function GetTorihikisakiName()
	{
		$ret = "";

		if(count($this->RetData) > 0)
		{
			$ret = $this->RetData[0]["TORIHIKISAKI_NM"];
		}

		return $ret;
	}

	/**
	 * 顧客名検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, clientcd:顧客コード)
	 * @return ture:成功、false:失敗
	 */
	public function SelectTorihikisakiName($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectTorihikisakiName', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["clientcd"]) || empty($arrParam["clientcd"]))
			{ throw new Exception('SelectTorihikisakiName', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT TOP 1 ";
			$sql .= "       TORIHIKISAKI_CD ";
			$sql .= "      ,TORIHIKISAKI_NM ";
			$sql .= "  FROM CM_MST_TORIHIKISAKI WITH(NOLOCK) ";
			$sql .= " WHERE ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "   AND KAISHA_CD = '" . $arrParam["kcd"] . "' ";
			$sql .= "   AND TORIHIKISAKI_CD = '" . $arrParam["clientcd"] . "' ";

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
	 * 顧客情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd     :会社コード,       cname   :顧客名,
	 *                                   ckana   :顧客名カナ,       ecd     :営業担当者コード,
	 *                                   tel     :電話番号,         address :住所,
	 *                                   conname :客先取引担当者名, claname :客先請求担当者名)
	 * @return ture:成功、false:失敗
	 */
	public function SelectMstTorihikisaki($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectMstTorihikisaki', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT TOP 1000 ";
			$sql .= "       A.TORIHIKISAKI_CD AS clientcd ";
			$sql .= "      ,A.TORIHIKISAKI_NM + ';' + TORIHIKISAKI_KANA AS clientname ";
			$sql .= "      ,A.TODOFUKEN +  A.JUSHO_1 + A.JUSHO_2 + ';' + A.TEL AS adress ";
			$sql .= "      ,B.CD_NM AS type ";
			$sql .= "      ,C.SHAIN_NM + ';' + D.SHAIN_NM AS employee ";
			$sql .= "      ,E.TANTOU_NM + ';' + F.TANTOU_NM AS client ";
			$sql .= "      ,dbo.PADO_FN_TASHA_TORIHIKISAKI_NM_STR('" . $arrParam["kcd"] . "', A.TORIHIKISAKI_CD) AS oclient";
			$sql .= "      ,A.TORIHIKISAKI_NM AS cname";
			$sql .= "  FROM CM_MST_TORIHIKISAKI AS A WITH(NOLOCK) ";

			// 業種細分名
			$sql .= "  LEFT JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT JOI_CD_KEY, CD_KEY, CD_NM ";
			$sql .= "        FROM BT_VIEW_GYOUSYU_DTL WITH(NOLOCK) ";
			$sql .= "       WHERE ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "         AND KAISHA_CD = '999' ";
			$sql .= "  ) AS B ";
			$sql .= "    ON A.GYOUSHU_CD = B.JOI_CD_KEY ";
			$sql .= "   AND A.GYOUSYU_DTL_CD = B.CD_KEY ";

			// 営業担当者名
			$sql .= "  LEFT JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, TORIHIKISAKI_CD, SHAIN_CD ";
			$sql .= "        FROM BC_MST_TORI_JI_TANTO WITH(NOLOCK) ";
			$sql .= "       WHERE ISNULL(DEL_FLG, 0) = 0 ";
			$sql .= "         AND TORIHIKISAKI_TANTOUSHA_KBN = '01' ";
			$sql .= "         AND SHUTANTOU_FLG = 1 ";
			$sql .= "  ) AS ETANTO ";
			$sql .= "    ON A.KAISHA_CD = ETANTO.KAISHA_CD ";
			$sql .= "   AND A.TORIHIKISAKI_CD = ETANTO.TORIHIKISAKI_CD ";
			$sql .= "  INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, SHAIN_CD ,SHAIN_NM ";
			$sql .= "        FROM BT_VIEW_SHAIN WITH(NOLOCK) ";
			$sql .= "       WHERE ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "  ) AS C ";
			$sql .= "    ON ETANTO.KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND ETANTO.SHAIN_CD = C.SHAIN_CD ";

			// 自社請求担当者名
			$sql .= "  LEFT JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, TORIHIKISAKI_CD, SHAIN_CD ";
			$sql .= "        FROM BC_MST_TORI_JI_TANTO WITH(NOLOCK) ";
			$sql .= "       WHERE ISNULL(DEL_FLG, 0) = 0 ";
			$sql .= "         AND TORIHIKISAKI_TANTOUSHA_KBN = '02' ";
			$sql .= "         AND SHUTANTOU_FLG = 1 ";
			$sql .= "  ) AS JTANTO ";
			$sql .= "    ON A.KAISHA_CD = JTANTO.KAISHA_CD ";
			$sql .= "   AND A.TORIHIKISAKI_CD = JTANTO.TORIHIKISAKI_CD ";
			$sql .= "  INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, SHAIN_CD ,SHAIN_NM ";
			$sql .= "        FROM BT_VIEW_SHAIN WITH(NOLOCK) ";
			$sql .= "       WHERE ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "  ) AS D ";
			$sql .= "    ON JTANTO.KAISHA_CD = D.KAISHA_CD ";
			$sql .= "   AND JTANTO.SHAIN_CD = D.SHAIN_CD ";

			// 客先取引担当者
			$sql .= "  LEFT JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, TORIHIKISAKI_CD, TANTOU_NM, TEL ";
			$sql .= "        FROM BC_MST_TORI_KY_TANTO WITH(NOLOCK) ";
			$sql .= "       WHERE ISNULL(DEL_FLG, 0) = 0 ";
			$sql .= "         AND TORIHIKISAKI_TANTOUSHA_KBN = '01' ";
			$sql .= "         AND SHUTANTOU_FLG = 1 ";
			$sql .= "  ) AS E ";
			$sql .= "    ON A.KAISHA_CD = E.KAISHA_CD ";
			$sql .= "   AND A.TORIHIKISAKI_CD = E.TORIHIKISAKI_CD ";

			// 客先請求担当者
			$sql .= "  LEFT JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, TORIHIKISAKI_CD, TANTOU_NM, TEL ";
			$sql .= "        FROM BC_MST_TORI_KY_TANTO WITH(NOLOCK) ";
			$sql .= "       WHERE ISNULL(DEL_FLG, 0) = 0 ";
			$sql .= "         AND TORIHIKISAKI_TANTOUSHA_KBN = '02' ";
			$sql .= "         AND SHUTANTOU_FLG = 1 ";
			$sql .= "  ) AS F ";
			$sql .= "    ON A.KAISHA_CD = F.KAISHA_CD ";
			$sql .= "   AND A.TORIHIKISAKI_CD = F.TORIHIKISAKI_CD ";

			$sql .= " WHERE ISNULL(A.DEL_FLG, 0) = 0 AND ISNULL(A.MUKOU_FLG, 0) = 0 ";
			$sql .= "   AND A.KAISHA_CD = '" . $arrParam["kcd"] . "' ";

			if(isset($arrParam["cname"]) && !empty($arrParam["cname"]))
			{
				//顧客名
				$sql .= "   AND A.TORIHIKISAKI_NM like  '%" . $arrParam["cname"] . "%'";
			}
			if(isset($arrParam["ckana"]) && !empty($arrParam["ckana"]))
			{
				//顧客名カナ
				$sql .= "   AND A.TORIHIKISAKI_KANA like '%" . $arrParam["ckana"] . "%'";
			}
			if(isset($arrParam["ecd"]) && !empty($arrParam["ecd"]))
			{
				//営業担当者コード
				$sql .= "   AND C.SHAIN_CD = '" . $arrParam["ecd"] . "'";
			}
			if(isset($arrParam["tel"]) && !empty($arrParam["tel"]))
			{
				//電話番号
				$sql .= "   AND (A.TEL = '" . $arrParam["tel"] . "'";
				$sql .= "    OR  E.TEL = '" . $arrParam["tel"] . "'";
				$sql .= "    OR  F.TEL = '" . $arrParam["tel"] . "')";
			}
			if(isset($arrParam["address"]) && !empty($arrParam["address"]))
			{
				//住所
				$sql .= "   AND A.TODOFUKEN +  A.JUSHO_1 + A.JUSHO_2 like '%" . $arrParam["address"] . "%'";
			}
			if(isset($arrParam["conname"]) && !empty($arrParam["conname"]))
			{
				//客先取引担当者名
				$sql .= "   AND E.TANTOU_NM like '%" . $arrParam["conname"] . "%'";
			}
			if(isset($arrParam["claname"]) && !empty($arrParam["claname"]))
			{
				//客先請求担当者名
				$sql .= "   AND F.TANTOU_NM like '%" . $arrParam["claname"] . "%'";
			}

			$sql .= " ORDER BY A.TORIHIKISAKI_CD ";

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

