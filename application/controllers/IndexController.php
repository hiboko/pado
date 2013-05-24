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

		$this->view->Token = $clsCommon->ConverDisp($token);
		$this->view->List = $clsCommon->GetMenuList();
		$this->view->SubList = $clsCommon->GetSubMenuList();
		$this->view->Active = 'class="active"';
    }
}

