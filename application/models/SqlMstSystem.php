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
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, entry:カテゴリ, section:セクション)
	 * @return ture:成功、false:失敗
	 */
	public function SelectSystemMsg($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectCirculationMsg', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["entry"]) || empty($arrParam["entry"]))
			{ throw new Exception('SelectCirculationMsg', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["section"]) || empty($arrParam["section"]))
			{ throw new Exception('SelectCirculationMsg', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT VALUE AS val FROM M_TSYSTEM ";
			$sql .= " WHERE KAISHA_CD = '" . $arrParam["kcd"] . "'";
			$sql .= "   AND ENTRY = '" . $arrParam["entry"] . "' AND SECTION = '" . $arrParam["section"] . "'";

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

