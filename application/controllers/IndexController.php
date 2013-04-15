<?php

/**
* IndexController for class
*
* ���ʏ����N���X(TOP)
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
	* ��������
	* 
	* @param
	* @return 
	*/
    public function init()
    {
        /* Initialize action controller here */
    }

	/**
	* TOP�y�[�W
	* 
	* @param
	* @return 
	*/
    public function indexAction()
    {
		//��������
		$clsCommon = new Common();
		$clsComConst = new ComConst();

		//�p�����[�^�ݒ�
		$token = $clsCommon->SetParam($this->getRequest(), "token");

		//�A�N�Z�X�`�F�b�N
		if(!$clsCommon->ChkAccess($token)) { throw new Exception("", $clsComConst::ERR_CODE_403); }

		//�ݒ菈��
		$this->view->Token = $clsCommon->ConverDisp($token);
    }
}

