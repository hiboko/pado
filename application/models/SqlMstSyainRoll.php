<?php

/**
 * SqlMstSyainRoll for class
 *
 * 社員権限情報 SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlMstSyainRoll
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
	 * 表示メニューID取得
	 */
	public function GetMenuID()
	{
		foreach ($this->RetData as $arr)
		{
		    $ret[] = $arr['MENU_ID'];
		}

		return $ret;
	}

	/**
	 * 権限名取得
	 */
	public function GetRoleName()
	{
		$ret = "";

		if(count($this->RetData) > 0)
		{
			$ret = $this->RetData[0]["codename"];
		}

		return $ret;
	}

	/**
	 * 表示メニュー情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, cd:社員ID)
	 * @return ture:成功、false:失敗
	 */
	public function SelectMenuId($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectMenuId', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["cd"]) || empty($arrParam["cd"]))
			{ throw new Exception('SelectMenuId', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT A.MENU_ID ";
			$sql .= "  FROM M_MROLL AS A WITH(NOLOCK) ";
			$sql .= " WHERE ISNULL(A.DEL_FLG, 0) = 0 ";
			$sql .= "   AND A.KAISHA_CD = '" . $arrParam["kcd"] . "' ";
			$sql .= "   AND A.ROLE_ID = 0";
			$sql .= " UNION ";
			$sql .= " SELECT B.MENU_ID ";
			$sql .= "  FROM M_TSYAIN_ROLL AS A WITH(NOLOCK) ";
			$sql .= " INNER JOIN M_MROLL AS B WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.ROLE_ID = B.ROLE_ID ";
			$sql .= "   AND ISNULL(B.DEL_FLG, 0) = 0  ";
			$sql .= " WHERE ISNULL(A.DEL_FLG, 0) = 0 ";
			$sql .= "   AND A.KAISHA_CD = '" . $arrParam["kcd"] . "' ";
			$sql .= "   AND A.SHAIN_CD = '" . $arrParam["cd"] . "' ";

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
	 * 社員権限情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, cd:社員コード)
	 * @return ture:成功、false:失敗
	 */
	public function SelectMstSyainRoll($db, $arrParam = array())
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

			$sql = " SELECT TOP 1000 ";
			$sql .= "       A.KAISHA_CD AS kcd ";
			$sql .= "      ,A.SHAIN_CD AS employeecd ";
			$sql .= "      ,A.ROLE_ID AS roleid ";
			$sql .= "      ,B.VALUE AS rolenm ";
			$sql .= "  FROM M_TSYAIN_ROLL AS A WITH(NOLOCK) ";

			$sql .= " INNER JOIN ";
			$sql .= "  ( ";
			$sql .= "      SELECT KAISHA_CD, SECTION, ENTRY, VALUE ";
			$sql .= "        FROM M_TSYSTEM WITH(NOLOCK) ";
			$sql .= "  ) AS B ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.ROLE_ID = B.SECTION ";
			$sql .= "   AND B.ENTRY = 'ROLE_NM' ";

			$sql .= " WHERE ISNULL(A.DEL_FLG, 0) = 0 ";

			if(isset($arrParam["kcd"]) && !empty($arrParam["kcd"]))
			{
				//会社コード
				$sql .= "   AND A.KAISHA_CD = '" . $arrParam["kcd"] . "'";
			}
			if(isset($arrParam["cd"]) && !empty($arrParam["cd"]))
			{
				//社員コード
				$sql .= "   AND A.SHAIN_CD = '" . $arrParam["cd"] . "'";
			}

			$sql .= " ORDER BY A.KAISHA_CD, A.SHAIN_CD ";

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
	 * 社員権限情報更新
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, employeecd:社員コード, roleid:ロールID)
	 * @return ture:成功、false:失敗
	 */
	public function UpdateMstSyainRoll($db, $arrParam = array())
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

			$sql = " UPDATE M_TSYAIN_ROLL ";
			$sql .= "   SET ROLE_ID  = '" . $arrParam["roleid"] . "'";
			$sql .= "      ,UPD_TM  = GETDATE() ";
			$sql .= "      ,UPD_SHAIN_CD = '" . $arrParam["employeecd"] . "'";
			$sql .= "      ,UPD_PGM_CD = '-1'";
			$sql .= " WHERE ISNULL(DEL_FLG, 0) = 0 ";
			$sql .= "   AND KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "   AND SHAIN_CD = '" . $arrParam["employeecd"] . "'";

			//クエリー実行
			$blnRet = $clsModelBase->Query($sql, false);

			//クエリー実行エラー
			if(!$blnRet) { throw new Exception($sql, $clsComConst::ERR_CODE_102); }

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
	 * システム情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, entry:種類, section:コード, serch:検索項目)
	 * @return ture:成功、false:失敗
	 */
	public function SelectMstSystemData($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectMstSystemData', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["entry"]) || empty($arrParam["entry"]))
			{ throw new Exception('SelectMstSystemData', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT ";
			$sql .= "       SECTION AS codeid ";
			$sql .= "      ,VALUE AS codename ";
			$sql .= "  FROM M_TSYSTEM AS A WITH(NOLOCK) ";
			$sql .= " WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "   AND ENTRY = '" . $arrParam["entry"] . "'";
			if(isset($arrParam["section"]) && !empty($arrParam["section"]))
			{
				$sql .= "   AND SECTION = '" . $arrParam["section"] . "'";
			}
			if(isset($arrParam["serch"]) && !empty($arrParam["serch"]))
			{
				$sql .= "   AND (SECTION like '%" . $arrParam["serch"] . "%'";
				$sql .= "    OR  VALUE like '%" . $arrParam["serch"] . "%')";
			}
			$sql .= " ORDER BY SECTION ";

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

