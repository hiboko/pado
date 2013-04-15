<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initRequest()
	{
		//エラー画面表示
		if(preg_match("/(^192.168.)|(^127.0.0.1)/", $_SERVER['REMOTE_ADDR'])){
			ini_set('display_errors', TRUE);
		}
	}
}

