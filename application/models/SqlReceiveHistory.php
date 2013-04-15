<?php

/**
 * SqlReceiveHistory for class
 *
 * 受注履歴 SQLクラス
 *
 * @category   Sql Class
 * @package    Pado
 * @author     Hitomi Aihara
 * @author     
 * @version    1.0
 */
class SqlReceiveHistory
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
	 * 受注履歴検索
	 * 
	 * @param  $db        データベース
	 * @param  $arrParam  パラメータ配列(kcd:会社コード, contractcd:契約主コード, claimantcd:請求先コード,
	 *                                   advertisercd:広告主コード, connectcd:自社取引担当者コード, claimcd:自社請求担当者コード,
	 *                                   reportcd:掲載版コード, dstartdate:納期(開始),denddate:納期(終了), bclasscd:大分類コード,
	 *                                   mclasscd:中分類コード, kindcd:種別コード, advertisingnm:広告名)
	 * @return ture:成功、false:失敗
	 */
	public function SelectReceiveHistory($db, $arrParam = array())
	{
		require_once dirname(__FILE__) . "/../models/ModelBase.php";

		$clsModelBase = new ModelBase();
		$clsComConst = new ComConst();

		try
		{
			//パラメーターエラー
			if(!isset($arrParam["kcd"]) || empty($arrParam["kcd"]))
			{ throw new Exception('SelectReceiveHistory', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["dstartdate"]) || empty($arrParam["dstartdate"]))
			{ throw new Exception('SelectReceiveHistory', $clsComConst::ERR_CODE_400); }
			if(!isset($arrParam["denddate"]) || empty($arrParam["denddate"]))
			{ throw new Exception('SelectReceiveHistory', $clsComConst::ERR_CODE_400); }

			//DB接続
			$blnRet = $clsModelBase->initDb($db);

			//DB接続エラー
			if(!$blnRet) { throw new Exception('', $clsComConst::ERR_CODE_101); }

			$sql = " SELECT TOP 1000";
			$sql .= "       '(' + B.TOKUISAKI_SNO + ');' + K.TORIHIKISAKI_NM AS contract";
			$sql .= "      ,F.KEISAIHAN_NM + ';' + dbo.PADO_MK_AREA_CD_STR(A.KAISHA_CD, A.DENPYO_NO, C.MEISAI_NO, NULL, G.HINMOKU_TYPE_CD) AS report";
			$sql .= "      ,(CASE ISNULL(J.KEISAIGOU_NO,'') ";
			$sql .= "          WHEN '' THEN CONVERT(char(10), C.NOUKI_YMD,111) ";
			$sql .= "          ELSE CONVERT(char(10), C.NOUKI_YMD,111)  + ';' + CONVERT(varchar(12), J.KEISAIGOU_NO) + '号'";
			$sql .= "        END) AS reportno ";
			$sql .= "      ,(CASE ISNULL(I.SIZE_CD,'') ";
			$sql .= "          WHEN '' THEN G.JUCHU_SHUBETU_NM + '(' + C.BUNRUI_CD_1 + ');' + D.TEXT_SIZE ";
			$sql .= "          ELSE G.JUCHU_SHUBETU_NM + '(' + C.BUNRUI_CD_1 + ');' + I.SIZE_NM + '(' + I.SIZE_CD + ')' ";
			$sql .= "        END) AS product ";
			$sql .= "      ,CONVERT(varchar(12), C.DENPYO_NO) + '-' + CONVERT(varchar(3), C.MEISAI_NO) AS seq";
			$sql .= "      ,REPLACE(CONVERT(varchar, CONVERT(MONEY,D.BAIKA), 1), '.00', '') + ';' + REPLACE(CONVERT(varchar, CONVERT(MONEY,D.SEISAKU_KINGAKU), 1), '.00', '') AS rprice";
			$sql .= "      ,REPLACE(CONVERT(varchar, CONVERT(MONEY,C.SHOUKEI), 1), '.00', '') + ';' +  REPLACE(CONVERT(varchar, CONVERT(MONEY,C.TAX), 1), '.00', '') AS pprice";
			$sql .= "      ,REPLACE(CONVERT(varchar, CONVERT(MONEY, C.SHOUKEI + C.TAX), 1), '.00', '') + ';' + REPLACE(CONVERT(varchar, CONVERT(MONEY, C.SHOUKEI - (C.GENKA + C.GENKA_SONOTA)), 1), '.00', '') AS price";
			$sql .= "      ,'(' + B.TANTO_SHA_NO + ');' +  H.SHAIN_NM AS charge";
			$sql .= "      ,CONVERT(char(10), E.KAISHU_YOTEI_YMD,111) AS receivedate";

			//【広告】伝票ヘッダ
			$sql .= "  FROM ADSD_TBL_DENPYOHDR AS A WITH(NOLOCK) ";

			//伝票ヘッダ
			$sql .= " INNER JOIN  SD_TBL_DENPYOHDR AS B WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = B.KAISHA_CD ";
			$sql .= "   AND A.DENPYO_NO = B.DENPYO_NO ";
			$sql .= "   AND ISNULL(B.DEL_FLG, 0) = 0 AND ISNULL(B.TORIKESI_FLG, 0) = 0 ";

			//伝票明細
			$sql .= " INNER JOIN SD_TBL_DENPYODTL AS C WITH(NOLOCK) ";
			$sql .= "    ON A.KAISHA_CD = C.KAISHA_CD ";
			$sql .= "   AND A.DENPYO_NO = C.DENPYO_NO ";
			$sql .= "   AND ISNULL(C.DEL_FLG, 0) = 0 AND ISNULL(C.TORIKESI_FLG, 0) = 0 ";
			$sql .= "   AND CONVERT(char(10), C.NOUKI_YMD,111) >= '" . $arrParam["dstartdate"] . "'";
			$sql .= "   AND CONVERT(char(10), C.NOUKI_YMD,111) <= '" . $arrParam["denddate"] . "'";

			//【広告】伝票明細
			$sql .= " INNER JOIN ADSD_TBL_DENPYODTL AS D WITH(NOLOCK) ";
			$sql .= "    ON C.KAISHA_CD = D.KAISHA_CD ";
			$sql .= "   AND C.DENPYO_NO = D.DENPYO_NO ";
			$sql .= "   AND C.MEISAI_NO = D.MEISAI_NO ";
			$sql .= "   AND ISNULL(D.DEL_FLG, 0) = 0 ";

			//伝票明細_請求
			$sql .= " INNER JOIN SD_TBL_DENPYODTL_SEIKYU AS E WITH(NOLOCK) ";
			$sql .= "    ON C.KAISHA_CD = E.KAISHA_CD ";
			$sql .= "   AND C.DENPYO_NO = E.DENPYO_NO ";
			$sql .= "   AND C.MEISAI_NO = E.MEISAI_NO ";
			$sql .= "   AND ISNULL(E.DEL_FLG, 0) = 0 ";

			//掲載版マスタ
			$sql .= " INNER JOIN ADMS_MST_KEISAIHAN AS F WITH(NOLOCK) ";
			$sql .= "    ON D.KAISHA_CD = F.KAISHA_CD ";
			$sql .= "   AND D.KEISAIHAN_CD = F.KEISAIHAN_CD ";
			$sql .= "   AND ISNULL(F.DEL_FLG, 0) = 0 AND ISNULL(F.MUKOU_FLG, 0) = 0 ";

			//受注種別マスタ
			$sql .= " INNER JOIN AD_VIEW_MST_JUCHU_SHUBETU AS G WITH(NOLOCK) ";
			$sql .= "    ON C.DAIBUNRUI_CD = G.DAIBUNRUI_CD ";
			$sql .= "   AND C.CHUBUNRUI_CD = G.CHUBUNRUI_CD ";
			$sql .= "   AND C.BUNRUI_CD_1  = G.JUCHU_SHUBETU_CD ";
			$sql .= "   AND ISNULL(G.DEL_FLG, 0) = 0 AND ISNULL(G.MUKOU_FLG, 0) = 0 ";
			$sql .= "   AND G.KAISHA_CD = '999' ";

			//受注種別マスタ(期間商材)
			$sql .= "  LEFT JOIN AD_VIEW_MST_JUCHU_SHUBETU AS G2 WITH(NOLOCK) ";
			$sql .= "    ON G.DAIBUNRUI_CD = G2.DAIBUNRUI_CD ";
			$sql .= "   AND G.CHUBUNRUI_CD = G2.CHUBUNRUI_CD ";
			$sql .= "   AND G.JUCHU_SHUBETU_CD  = G2.JUCHU_SHUBETU_CD ";
			$sql .= "   AND ISNULL(G2.DEL_FLG, 0) = 0 AND ISNULL(G2.MUKOU_FLG, 0) = 0 ";
			$sql .= "   AND G2.KAISHA_CD = '999' ";
			$sql .= "   AND G2.HINMOKU_TYPE_CD = 3 ";

			//社員マスタ
			$sql .= " INNER JOIN BT_VIEW_SHAIN AS H WITH(NOLOCK) ";
			$sql .= "    ON B.KAISHA_CD = H.KAISHA_CD ";
			$sql .= "   AND B.TANTO_SHA_NO = H.SHAIN_CD ";
			$sql .= "   AND ISNULL(H.DEL_FLG, 0) = 0 AND ISNULL(H.MUKOU_FLG, 0) = 0 ";

			//サイズマスタ
			$sql .= "  LEFT JOIN AD_VIEW_MST_SIZE AS I WITH(NOLOCK) ";
			$sql .= "    ON D.SIZE_CD = I.SIZE_CD ";
			$sql .= "   AND ISNULL(I.DEL_FLG, 0) = 0 ";
			$sql .= "   AND (G.HINMOKU_TYPE_CD = 1 OR G.HINMOKU_TYPE_CD = 2) ";

			//掲載号マスタ
			$sql .= "  LEFT JOIN ADMS_MST_KEISAIGOU AS J WITH(NOLOCK) ";
			$sql .= "    ON D.KAISHA_CD = J.KAISHA_CD ";
			$sql .= "   AND D.KEISAIHAN_CD = J.KEISAIHAN_CD ";
			$sql .= "   AND C.NOUKI_YMD = J.KEISAI_YMD ";
			$sql .= "   AND ISNULL(J.DEL_FLG, 0) = 0 ";
			$sql .= "   AND (G.HINMOKU_TYPE_CD = 1 OR G.HINMOKU_TYPE_CD = 2) ";

			//取引先マスタ
			$sql .= " INNER JOIN CM_MST_TORIHIKISAKI AS K WITH(NOLOCK) ";
			$sql .= "    ON B.KAISHA_CD = K.KAISHA_CD ";
			$sql .= "   AND B.TOKUISAKI_SNO = K.TORIHIKISAKI_CD ";
			$sql .= "   AND ISNULL(K.DEL_FLG, 0) = 0 AND ISNULL(K.MUKOU_FLG, 0) = 0 ";

			$sql .= " WHERE ISNULL(A.DEL_FLG, 0) = 0";
			$sql .= "   AND A.KAISHA_CD = '" . $arrParam["kcd"] . "'";

			if(isset($arrParam["contractcd"]) && !empty($arrParam["contractcd"]))
			{
				//契約主コード
				$sql .= "   AND B.TOKUISAKI_SNO = '" . $arrParam["contractcd"] . "'";
			}
			if(isset($arrParam["claimantcd"]) && !empty($arrParam["claimantcd"]))
			{
				//請求先コード
				$sql .= "   AND B.SEIKYUSAKI_SNO = '" . $arrParam["claimantcd"] . "'";
			}
			if(isset($arrParam["advertisercd"]) && !empty($arrParam["advertisercd"]))
			{
				//広告主コード
				$sql .= "   AND D.KOUKOKUNUSHI_NO = '" . $arrParam["advertisercd"] . "'";
			}
			if(isset($arrParam["connectcd"]) && !empty($arrParam["connectcd"]))
			{
				//自社取引担当者コード
				$sql .= "   AND B.TANTO_SHA_NO = '" . $arrParam["connectcd"] . "'";
			}
			if(isset($arrParam["claimcd"]) && !empty($arrParam["claimcd"]))
			{
				//自社請求担当者コード
				$sql .= "   AND E.JISHA_TANTOSHA_CD = '" . $arrParam["claimcd"] . "'";
			}
			if(isset($arrParam["reportcd"]) && !empty($arrParam["reportcd"]))
			{
				//掲載版コード
				$sql .= "   AND D.KEISAIHAN_CD = '" . $arrParam["reportcd"] . "'";
			}
			if(isset($arrParam["bclasscd"]) && !empty($arrParam["bclasscd"]))
			{
				//大分類コード
				$sql .= "   AND C.DAIBUNRUI_CD = '" . $arrParam["bclasscd"] . "'";
			}
			if(isset($arrParam["mclasscd"]) && !empty($arrParam["mclasscd"]))
			{
				//中分類コード
				$sql .= "   AND C.CHUBUNRUI_CD = '" . $arrParam["mclasscd"] . "'";
			}
			if(isset($arrParam["kindcd"]) && !empty($arrParam["kindcd"]))
			{
				//種別コード
				$sql .= "   AND C.BUNRUI_CD_1 = '" . $arrParam["kindcd"] . "'";
			}
			if(isset($arrParam["advertisingnm"]) && !empty($arrParam["advertisingnm"]))
			{
				$sql .= "   AND ((CASE WHEN ISNULL(G2.HINMOKU_TYPE_CD, '') = '' THEN D.KOUKOKU_NM ELSE D.TIRASI_NM END) like '%" . $arrParam["advertisingnm"] . "%'";
				$sql .= "    OR D.TIRASI_NM like '%" . $arrParam["advertisingnm"] . "%'";
				$sql .= "    OR dbo.PADO_FN_KOUKOKUKEISAI_HANSITA_NM_STR(C.KAISHA_CD, C.DENPYO_NO, C.MEISAI_NO) like '%" . $arrParam["advertisingnm"] . "%'";
				$sql .= "    OR dbo.PADO_FN_ORKM_UTIWAKE_TIRASI_NM_STR(C.KAISHA_CD, C.DENPYO_NO, C.MEISAI_NO) like '%" . $arrParam["advertisingnm"] . "%')";
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
}

