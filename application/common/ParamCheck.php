<?php

/**
 * ParamCheck for class
 *
 * パラメータチェッククラス
 *
 * @category   ParamCheck
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
final class ParamCheck 
{
	/**
	 * エラーメッセージ
	 */
	private $ErrMsg = array();

	/**
	 * エラーメッセージ取得
	 */
	public function GetErrMsg()
	{
		return $this->ErrMsg;
	}

	/**
	* 共通チェック
	*
	* @param   $value   チェック値
	* @return  true:OK、false:NG
	*/
	public function ChkCommon($value)
	{
		$value = htmlentities($value, ENT_QUOTES, 'UTF-8');
		$value = htmlspecialchars($value);

		return true;
	}

	/**
	* 必須項目が入っているかのチェック
	*
	* @param   $value  チェック値
	* @return  true:OK、false:NG
	*/
	public function ChkMust($value, $name)
	{
		if (strlen($value) > 0) { return true; }
		else
		{ 
			$this->ErrMsg[] = $name . "は必須項目です。"; 
			return false;
		}
	}

	/**
	* 数字チェック
	*
	* @param   $value  チェック値
	* @return  true:OK、false:NG
	*/
	public function ChkNumeric($value, $name)
	{
		if (is_numeric($value)) { return true; }
		else
		{
			$this->ErrMsg[] = $name . "は数値で入力してください。";
			return false;
		}
	}

	/**
	* 文字のみかどうかをチェック(記号以外)
	*
	* @param   $value   チェック値
	* @return  true:OK、false:NG
	*/
	public function ChkStr($value, $name)
	{
		if(preg_match("/^[０-９0-9Ａ-ＺA-Zａ-ｚa-zぁ-んァ-ヴーｦ-ﾟ一-龠]+$/u", $value)) { return true; }
		else
		{
			$this->ErrMsg[] = $name . "の入力が不正です。";
			return false;
		}
	}

	/**
	* カタカナのみかどうかをチェック
	*
	* @param   $value   チェック値
	* @return  true:OK、false:NG
	*/
	public function ChkStrKana($value, $name)
	{
		if(preg_match("/^[ァ-ヴーｦ-ﾟ]+$/u", $value)) { return true; }
		else
		{
			$this->ErrMsg[] = $name . "はカタカナで入力してください。";
			return false;
		}
	}

	/**
	* 日付チェック
	*
	* @param   $value   チェック値
	* @return  true:OK、false:NG
	*/
	public function ChkDate($value, $name)
	{
		if(preg_match("/^\d{4}\/\d{2}\/\d{2}$/", $value))
		{
			try
			{
			    $date = new DateTime($value);
			}
			catch (Exception $e)
			{
				$this->ErrMsg[] = $name . "の入力が不正です。";
				return false;
			}
		}
		else
		{
			$this->ErrMsg[] = $name . "の入力が不正です。";
			return false;
		}

		return true;
	}

	/**
	* 電話番号チェック
	*
	* @param   $value   チェック値
	* @return  true:OK、false:NG
	*/
	public function ChkTel($value, $name)
	{
		if(preg_match("/^[0-9-]+$/u", $value)) { return true; }
		else
		{
			$this->ErrMsg[] = $name . "の入力が不正です。";
			return false;
		}
	}

	/**
	* URLチェック
	*
	* @param   $value   チェック値
	* @return  true:OK、false:NG
	*/
	public function ChkUrl($value, $name)
	{
		if (preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $value)) { return true; }
		else
		{
			$this->ErrMsg[] = $name . "の入力が不正です。";
			return false;
		}
	}
}

