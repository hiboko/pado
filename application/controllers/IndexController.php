<?php

/**
* IndexController for class
*
* 共通処理クラス(TOP)
*
* @category   Index
* @package    Pado
* @author     Hitomi Aihara
* @author     
* @version    1.0
* @link       http://kikansub.pado.jp/
*/
class IndexController extends Zend_Controller_Action
{
	/**
	* 初期処理
	* 
	* @param
	* @return 
	*/
    public function init()
    {
        /* Initialize action controller here */
    }

	/**
	* TOPページ
	* 
	* @param
	* @return 
	*/
    public function indexAction()
    {
		//初期処理
		$clsCommon = new Common();
		$clsComConst = new ComConst();

		//パラメータ設定
		$token = $clsCommon->SetParam($this->getRequest(), "token");

		//アクセスチェック
		if(!$clsCommon->ChkAccess($token)) { throw new Exception("", $clsComConst::ERR_CODE_403); }

		//セッション情報取得
		$session = $clsCommon->GetSession();

		//設定処理
		$list = "";
		$sublist = "";

		//メニューリスト生成
		foreach ($session->menu as $val)
		{
			switch($val)
			{
				case $clsComConst::PGID_ANALYSIS_RHISTORY:
					//受注履歴検索
					$list .= '<li><a href="' . $clsComConst::ANALYSIS_RHISTORY_URL . '?token=' . $token . '"><span>受注履歴検索</span></a></li>';
					$sublist .= '<li><a href="' . $clsComConst::ANALYSIS_RHISTORY_URL . '?token=' . $token . '">受注履歴検索</a></li>';
					break;
				case $clsComConst::PGID_ANALYSIS_CIRCULATION:
					//"部数表"
					$list .= '<li><a href="' . $clsComConst::ANALYSIS_CIRCULATION_URL . '?token=' . $token . '"><span>部数表</span></a></li>';
					$sublist .= '<li><a href="' . $clsComConst::ANALYSIS_CIRCULATION_URL . '?token=' . $token . '">部数表</a></li>';
					break;
			}
		}

		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->List = $list;
		$this->view->SubList = $sublist;
    }
}

