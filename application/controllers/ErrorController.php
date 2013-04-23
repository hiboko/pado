<?php

/**
* ErrorController for class
*
* エラー処理クラス
*
* @category   Error
* @package    Pado
* @author     Hitomi Aihara
* @author     
* @version    1.0
* @link       http://kikansub.pado.jp/error/
*/
class ErrorController extends Zend_Controller_Action
{

	/**
	* エラー処理
	* 
	* @param
	* @return 
	*/
	public function errorAction()
	{
		$clsComConst = new ComConst();
		$errors = $this->_getParam('error_handler');
		$code = $errors->exception->getCode();
		$logflg = false;

		if (!$errors || !$errors instanceof ArrayObject)
		{
			$this->view->message = 'You have reached the error page';
			return;
		}

		switch ($errors->type)
		{
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				// 404 error
				$this->getResponse()->setHttpResponseCode(404);
				$priority = Zend_Log::NOTICE;
				$this->view->message = 'ページが見つかりませんでした。';
				break;
			default:
				switch ($code)
				{
					case 101:
						$this->getResponse()->setHttpResponseCode(101);
						$priority = Zend_Log::CRIT;
						$this->view->message = 'DBの接続に失敗しました。';
						$logflg = true;
						break;
					case 102:
						$this->getResponse()->setHttpResponseCode(102);
						$priority = Zend_Log::CRIT;
						$this->view->message = 'SQLの実行に失敗しました。';
						$logflg = true;
						break;
					case 400:
						$this->getResponse()->setHttpResponseCode(400);
						$priority = Zend_Log::ERR;
						$this->view->message = 'リクエストが不正です。';
						$logflg = true;
						break;
					case 403:
						$this->getResponse()->setHttpResponseCode(403);
						$priority = Zend_Log::NOTICE;
						$this->view->message = 'アクセスを拒否しました。';
						$logflg = true;
						break;
					case 404:
						$this->getResponse()->setHttpResponseCode(404);
						$priority = Zend_Log::NOTICE;
						$this->view->message = 'ページが見つかりませんでした。';
						break;
					case 405:
						$this->getResponse()->setHttpResponseCode(405);
						$priority = Zend_Log::NOTICE;
						$this->view->message = 'このブラウザでは対応していません。';
						break;
					default:
						// application error
						$this->getResponse()->setHttpResponseCode(500);
						$priority = Zend_Log::CRIT;
						$this->view->message = 'アプリケーションエラーが発生しました。';
						$logflg = true;
						//$this->view->message = $errors->exception->getMessage();
						break;
				}
				break;
		}

		try
		{
			if($logflg)
			{
				//エラーログ出力
				$logFile = $clsComConst::ERROR_LOG_PATH. '/'. strtr('err_#DT#.log', array('#DT#'=>date('Ymd')));
				$logger = new Zend_Log(new Zend_Log_Writer_Stream($logFile));
				$msg = $this->view->message;
				if($errors->exception->getMessage() != ""){ $msg = $msg . "(" . $errors->exception->getMessage() . ")"; }
				$logger->log($msg, $priority);
			}
		}
		catch(Exception $e)
		{
			echo "ログファイルの出力に失敗しました。"; 
		}

		// Log exception, if logger available
		if ($log = $this->getLog())
		{
			$log->log($this->view->message, $priority, $errors->exception);
			$log->log('Request Parameters', $priority, $errors->request->getParams());
		}

		// conditionally display exceptions
		if ($this->getInvokeArg('displayExceptions') == true)
		{
			$this->view->exception = $errors->exception;
		}

		$this->view->request = $errors->request;
	}

	public function getLog()
	{
		$bootstrap = $this->getInvokeArg('bootstrap');

		if (!$bootstrap->hasResource('Log'))
		{
			return false;
		}
		$log = $bootstrap->getResource('Log');

		return $log;
	}
}

