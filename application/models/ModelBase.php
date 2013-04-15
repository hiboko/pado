<?php

/**
 * ModelBase for class
 *
 * 共通クラス
 *
 * @category   Model Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class ModelBase 
{
	/**
	 * コネクション
	 */
	public $Connect;

	/**
	 * 返却結果配列
	 */
	private $RetData = array();

	/**
	 * 返却結果取得
	 */
	public function GetData()
	{
		return $this->RetData;
	}

	/**
	 * DB接続
	 * 
	 * @param  $dbname  データベース
	 * @return true:成功、false:失敗
	 */
	public function initDb($dbname)
	{
		$config = new Zend_Config_ini('application/configs/application.ini', $dbname);

		$this->Connect = mssql_connect($config->db->params->host,
									   $config->db->params->username,
									   $config->db->params->password);

		mssql_select_db($config->db->params->dbname, $this->Connect);

		if (!$this->Connect) { return false; }

		return true;
	}

	/**
	 * クエリー実行(配列返却)
	 * 
	 * @param  $sql   SQLソース
	 * @return array  結果データ配列
	 */
	public function Query($query)
	{
		$query = mb_convert_encoding($query, "SJIS-win", "UTF-8");

		$ret = mssql_query($query, $this->Connect);

		if ($ret === false) { return false; }

		while( $row = mssql_fetch_array($ret)) { $this->RetData[] = $row; }

		mb_convert_variables('UTF-8', 'SJIS-win', $this->RetData);

		return true;
	}

	/**
	 * 接続解除
	 */
	public function Close()
	{
		mssql_close( $this->Connect );
	}
}

