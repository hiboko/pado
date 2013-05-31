<?php

/**
 * SqlMstSystem for class
 *
 * システムマスタ SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlMstSystem
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
	 * システムメッセージ情報検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, entry:カテゴリ(カンマ区切り可), section:セクション)
	 * @return ture:成功、false:失敗
	 */
	public function SelectSystemMsg($db, $arrParam = array())
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

			$sql = " SELECT KAISHA_CD AS kcd ";
			$sql .= "      ,ENTRY AS entry ";
			$sql .= "      ,SECTION AS section ";
			$sql .= "      ,VALUE AS val ";
			$sql .= "      ,BIKO AS biko ";
			$sql .= "  FROM M_TSYSTEM ";
			$sql .= " WHERE 0=0";
			if(isset($arrParam["entry"]) || !empty($arrParam["entry"]))
			{
				$sql .= "   AND ENTRY IN (" . $arrParam["entry"] . ")";
			}
			if(isset($arrParam["kcd"]) || !empty($arrParam["kcd"]))
			{
				$sql .= "   AND KAISHA_CD = '" . $arrParam["kcd"] . "'";
			}
			if(isset($arrParam["section"]) || !empty($arrParam["section"]))
			{
				$sql .= "   AND SECTION = '" . $arrParam["section"] . "'";
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
	 * システム情報登録
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, scd:会社コード, entry:カテゴリ, section:セクション, value:値, biko:備考)
	 * @return ture:成功、false:失敗
	 */
	public function InsertSystem($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('UpdateSystem', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["scd"]) || empty($arrParam["scd"]))
			{ throw new Exception('UpdateSystem', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["entry"]) || empty($arrParam["entry"]))
			{ throw new Exception('UpdateSystem', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["section"]) || empty($arrParam["section"]))
			{ throw new Exception('UpdateSystem', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " INSERT INTO M_TSYSTEM ";
			$sql .= "(";
			$sql .= "       KAISHA_CD ";
			$sql .= "      ,ENTRY ";
			$sql .= "      ,SECTION ";
			$sql .= "      ,VALUE ";
			$sql .= "      ,BIKO ";
			$sql .= "      ,INS_TM ";
			$sql .= "      ,INS_SHAIN_CD ";
			$sql .= "      ,INS_PGM_CD ";
			$sql .= "      ,UPD_TM ";
			$sql .= "      ,UPD_SHAIN_CD ";
			$sql .= "      ,UPD_PGM_CD ";
			$sql .= ")";
			$sql .= "VALUES ";
			$sql .= "(";
			$sql .= "'" . $arrParam["kcd"] . "'";
			$sql .= ",'" . $arrParam["entry"] . "'";
			$sql .= ",'" . $arrParam["section"] . "'";
			$sql .= ",'" . $arrParam["value"] . "'";
			$sql .= ",'" . $arrParam["biko"] . "'";
			$sql .= ",GETDATE() ";
			$sql .= ",'" . $arrParam["scd"] . "'";
			$sql .= ",999999 ";
			$sql .= ",GETDATE() ";
			$sql .= ",'" . $arrParam["scd"] . "'";
			$sql .= ",999999 ";
			$sql .= ")";

			//クエリー実行
			$blnRet = $clsModelBase->Query($sql, false);

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
	 * システム情報更新
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, scd:会社コード, entry:カテゴリ, section:セクション, value:値)
	 * @return ture:成功、false:失敗
	 */
	public function UpdateSystem($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('UpdateSystem', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["scd"]) || empty($arrParam["scd"]))
			{ throw new Exception('UpdateSystem', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["entry"]) || empty($arrParam["entry"]))
			{ throw new Exception('UpdateSystem', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["section"]) || empty($arrParam["section"]))
			{ throw new Exception('UpdateSystem', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " UPDATE M_TSYSTEM ";
			$sql .= "   SET VALUE = '" . $arrParam["value"] . "'";
			$sql .= "      ,UPD_TM = GETDATE() ";
			$sql .= "      ,UPD_SHAIN_CD =  '" . $arrParam["scd"] . "'";
			$sql .= "      ,UPD_PGM_CD = 999999 ";
			$sql .= " WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "   AND ENTRY = '" . $arrParam["entry"] . "'";
			$sql .= "   AND SECTION = '" . $arrParam["section"] . "'";

			//クエリー実行
			$blnRet = $clsModelBase->Query($sql, false);

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
	 * システム情報削除
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, scd:会社コード, entry:カテゴリ, section:セクション)
	 * @return ture:成功、false:失敗
	 */
	public function DeleteSystem($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('UpdateSystem', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["scd"]) || empty($arrParam["scd"]))
			{ throw new Exception('UpdateSystem', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["entry"]) || empty($arrParam["entry"]))
			{ throw new Exception('UpdateSystem', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["section"]) || empty($arrParam["section"]))
			{ throw new Exception('UpdateSystem', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " DELETE FROM M_TSYSTEM ";
			$sql .= " WHERE KAISHA_CD =  '" . $arrParam["kcd"] . "'";
			$sql .= "   AND ENTRY = '" . $arrParam["entry"] . "'";
			$sql .= "   AND SECTION = '" . $arrParam["section"] . "'";

			//クエリー実行
			$blnRet = $clsModelBase->Query($sql, false);

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

