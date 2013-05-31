<?php

/**
 * SqlMstShain for class
 *
 * 社員情報 SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlMstShain
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
	public function GetShainName()
	{
		$ret = "";

		if(count($this->RetData) > 0)
		{
			$ret = $this->RetData[0]["employeename"];
		}

		return $ret;
	}

	/**
	 * 社員情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, cd:社員コード, serch:検索項目, fhold :営業拠点コード, post:部署)
	 * @return ture:成功、false:失敗
	 */
	public function SelectMstShain($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectMstShain', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT TOP 1000 ";
			$sql .= "       A.SHAIN_CD AS employeeid ";
			$sql .= "      ,A.SHAIN_NM AS employeename ";
			$sql .= "      ,A.SHAIN_NM_KANA AS employeekana ";
			$sql .= "      ,B.CD_NM ";
			$sql .= "      ,C.BUSHO_NM ";
			$sql .= "  FROM BT_VIEW_SHAIN AS A WITH(NOLOCK) ";

			// 営業拠点
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, CD_KEY, CD_NM ";
			$sql .= "        FROM BT_VIEW_EIGYO_KYOTEN WITH(NOLOCK) ";
			$sql .= "       WHERE ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0 ";
			$sql .= "  ) AS B ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.EIGYO_KYOTEN_CD = B.CD_KEY ";

			// 部署
			$sql .= " INNER JOIN  ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, BUSHO_CD, DISP_SMRY_BUSHO_CD, BUSHO_NM ";
			$sql .= "        FROM BT_VIEW_BUSHO WITH(NOLOCK) ";
			$sql .= "       WHERE ISNULL(DEL_FLG, 0) = 0 ";
			$sql .= "         AND START_YMD_DISP <= GETDATE() ";
			$sql .= "         AND END_YMD_DISP >= GETDATE() ";
			$sql .= "  ) AS C ";
			$sql .= "    ON A.KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND A.BUSHO_CD = C.BUSHO_CD ";

			$sql .= " WHERE ISNULL(A.DEL_FLG, 0) = 0 AND ISNULL(A.MUKOU_FLG, 0) = 0 ";
			$sql .= "   AND A.KAISHA_CD = '" . $arrParam["kcd"] . "' ";

			if(isset($arrParam["serch"]) && !empty($arrParam["serch"]))
			{
				//検索項目
				$sql .= "   AND (A.SHAIN_CD like '%" . $arrParam["serch"] . "%'";
				$sql .= "    OR  A.SHAIN_NM like '%" . $arrParam["serch"] . "%')";
			}
			if(isset($arrParam["fhold"]) && !empty($arrParam["fhold"]))
			{
				//営業拠点コード
				$sql .= "   AND A.EIGYO_KYOTEN_CD = '" . $arrParam["fhold"] . "'";
			}
			if(isset($arrParam["post"]) && !empty($arrParam["post"]))
			{
				//部署
				$sql .= "   AND C.DISP_SMRY_BUSHO_CD = '" . $arrParam["post"] . "'";
			}
			if(isset($arrParam["cd"]) && !empty($arrParam["cd"]))
			{
				//社員コード
				$sql .= "   AND A.SHAIN_CD = '" . $arrParam["cd"] . "'";
			}

			$sql .= " ORDER BY A.SHAIN_CD ";

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
	 * 営業拠点検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, cd:営業拠点コード, name :営業拠点名)
	 * @return ture:成功、false:失敗
	 */
	public function SelectMstEigyoKyoten($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectMstEigyoKyoten', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); return false; }

			$sql = " SELECT TOP 1000 ";
			$sql .= "       A.CD_KEY AS cd ";
			$sql .= "      ,A.CD_NM AS name ";
			$sql .= "  FROM BT_VIEW_EIGYO_KYOTEN AS A WITH(NOLOCK) ";
			$sql .= " WHERE ISNULL(A.DEL_FLG, 0) = 0 AND ISNULL(A.MUKOU_FLG, 0) = 0 ";
			$sql .= "   AND A.KAISHA_CD = '" . $arrParam["kcd"] . "' ";

			if(isset($arrParam["cd"]) && !empty($arrParam["cd"]))
			{
				$sql .= "   AND CD_KEY = '" . $arrParam["cd"] . "'";
			}

			if(isset($arrParam["name"]) && !empty($arrParam["name"]))
			{
				$sql .= "   AND CD_NM like '%" . $arrParam["name"] . "%'";
			}

			$sql .= " ORDER BY DISP_ORDER ";

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
	 * 部署検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, fhold:営業拠点コード, post:部署コード, name:部署名)
	 * @return ture:成功、false:失敗
	 */
	public function SelectMstBusho($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectMstBusho', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT TOP 1000 ";
			$sql .= "       A.DISP_SMRY_BUSHO_CD AS cd ";
			$sql .= "      ,A.BUSHO_NM AS name ";
			$sql .= "      ,A.EIGYO_KYOTEN_CD AS ecd ";
			$sql .= "  FROM BT_VIEW_BUSHO AS A WITH(NOLOCK) ";
			$sql .= " INNER JOIN BT_VIEW_EIGYO_KYOTEN AS B WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.EIGYO_KYOTEN_CD = B.CD_KEY ";
			$sql .= "   AND ISNULL(B.DEL_FLG, 0) = 0 AND ISNULL(B.MUKOU_FLG, 0) = 0 ";
			$sql .= " WHERE ISNULL(A.DEL_FLG, 0) = 0 ";
			$sql .= "   AND A.KAISHA_CD = '" . $arrParam["kcd"] . "' ";
			$sql .= "   AND A.START_YMD_DISP <= GETDATE() ";
			$sql .= "   AND A.END_YMD_DISP >= GETDATE() ";

			if(isset($arrParam["fhold"]) && !empty($arrParam["fhold"]))
			{
				//営業拠点コード
				$sql .= "   AND A.EIGYO_KYOTEN_CD = '" . $arrParam["fhold"] . "'";
			}

			if(isset($arrParam["post"]) && !empty($arrParam["post"]))
			{
				//部署コード
				$sql .= "   AND A.EIGYO_KYOTEN_CD = (SELECT EIGYO_KYOTEN_CD FROM BT_VIEW_BUSHO WITH(NOLOCK) ";
				$sql .= "   						  WHERE KAISHA_CD = '" . $arrParam["kcd"] . "' ";
				$sql .= "   						    AND DISP_SMRY_BUSHO_CD = '" . $arrParam["post"] . "' ";
				$sql .= "   						    AND START_YMD_DISP <= GETDATE() ";
				$sql .= "   						    AND END_YMD_DISP >= GETDATE()) ";
			}

			if(isset($arrParam["name"]) && !empty($arrParam["name"]))
			{
				//検索項目
				$sql .= "   AND A.BUSHO_NM like '%" . $arrParam["name"] . "%'";
			}

			$sql .= " ORDER BY A.DISP_SMRY_BUSHO_CD ";

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
	 * ログイン情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, id:ログインID, pass:パスワード)
	 * @return ture:成功、false:失敗
	 */
	public function SelectMstSystemUsr($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectMstSystemUsr', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT TOP 1 ";
			$sql .= "       A.KAISHA_CD ";
			$sql .= "      ,A.SHAIN_CD ";
			$sql .= "      ,A.SHAIN_NM ";
			$sql .= "      ,DATEDIFF(day, B.LAST_PWD_CHG_DATE, GETDATE()) AS LAST_PWD_CHG_DATE ";
			$sql .= "  FROM BC_MST_SHAIN AS A WITH(NOLOCK) ";
			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT USR_ID, USR_UID, USR_NAME ,PWD, LAST_PWD_CHG_DATE ";
			$sql .= "        FROM RWAT_MST_SYSTEM_USR WITH(NOLOCK) ";
			$sql .= "  ) AS B ";
			$sql .= "    ON A.USR_UID = B.USR_UID ";
			$sql .= " WHERE ISNULL(A.DEL_FLG, 0) = 0 AND ISNULL(A.MUKOU_FLG, 0) = 0 ";
			
			if(isset($arrParam["kcd"]) && !empty($arrParam["kcd"]))
			{
				$sql .= "   AND A.KAISHA_CD = '" . $arrParam["kcd"] . "' ";
			}

			if(isset($arrParam["id"]) && !empty($arrParam["id"]))
			{
				$sql .= "   AND A.SHAIN_CD = '" . $arrParam["id"] . "' ";
			}

			if(isset($arrParam["pass"]) && !empty($arrParam["pass"]))
			{
				$sql .= "   AND B.PWD = dbo.PADO_FN_PROC_NINKA('" . $arrParam["pass"] . "')";
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
}

