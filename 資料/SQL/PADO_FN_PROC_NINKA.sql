USE [base_web_test]
GO
/****** オブジェクト:  UserDefinedFunction [dbo].[PADO_FN_PROC_NINKA]    スクリプト日付: 03/19/2013 14:34:27 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =================================================
-- Author:		Aihara
-- Create date: 2013/03/19
-- Description:	ログインパスワード変換処理
-- =================================================
CREATE FUNCTION [dbo].[PADO_FN_PROC_NINKA]
(
	@PASS		VARCHAR(30)	--パスワード
)
RETURNS VARCHAR(1000)
AS
BEGIN

	DECLARE	@BinPass 	VARBINARY(8000)
	DECLARE	@charvalue	VARCHAR(255)
	DECLARE	@i			INT
	DECLARE	@length		INT
	DECLARE	@hexstring	CHAR(16)

	--MD5ハッシュ値で変換 (バイナリ)
	SELECT @BinPass = HASHBYTES('md5', @PASS)

	--認証チェック時には頭の'0x'は不要、ただし初期化が必要
	SELECT @charvalue = ''
	SELECT @i = 1
	SELECT @length = DATALENGTH(@BinPass)
	SELECT @hexstring = '0123456789abcdef'

	--バイナリデータから16進文字列への型変換処理
	WHILE (@i <= @length)
	BEGIN

		DECLARE	@tempint	INT
		DECLARE	@firstint	INT
		DECLARE	@secondint	INT

		/*
		Transact-SQL CONVERTコマンドは、1バイト1文字方式でバイナリ データを文字データに変換します。
		SQL Serverはソース バイナリ データの各バイトを整数値に変換し、次にその整数値を宛先文字データのASCII値として使用します。
		この機能は、バイナリ型、変種のバイナリ型、タイムスタンプ型のデータに適用されます。
		たとえば、バイナリの値00001111 (16進法では、0x0F) は、これに相当する整数値である15に変換され、次にASCIIの値15 (これは読めない)に対応する文字に変換されます
		*/
		SELECT @tempint = CONVERT(INT, SUBSTRING(@BinPass , @i , 1))
		SELECT @firstint = FLOOR(@tempint / 16)
		SELECT @secondint = @tempint - (@firstint * 16)

		SELECT @charvalue = @charvalue + SUBSTRING(@hexstring, @firstint + 1, 1) + SUBSTRING(@hexstring, @secondint + 1, 1)

		SELECT @i = @i + 1

	END

	RETURN( @charvalue )

END
