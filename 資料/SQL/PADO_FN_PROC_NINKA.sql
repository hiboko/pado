USE [base_web_test]
GO
/****** �I�u�W�F�N�g:  UserDefinedFunction [dbo].[PADO_FN_PROC_NINKA]    �X�N���v�g���t: 03/19/2013 14:34:27 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =================================================
-- Author:		Aihara
-- Create date: 2013/03/19
-- Description:	���O�C���p�X���[�h�ϊ�����
-- =================================================
CREATE FUNCTION [dbo].[PADO_FN_PROC_NINKA]
(
	@PASS		VARCHAR(30)	--�p�X���[�h
)
RETURNS VARCHAR(1000)
AS
BEGIN

	DECLARE	@BinPass 	VARBINARY(8000)
	DECLARE	@charvalue	VARCHAR(255)
	DECLARE	@i			INT
	DECLARE	@length		INT
	DECLARE	@hexstring	CHAR(16)

	--MD5�n�b�V���l�ŕϊ� (�o�C�i��)
	SELECT @BinPass = HASHBYTES('md5', @PASS)

	--�F�؃`�F�b�N���ɂ͓���'0x'�͕s�v�A���������������K�v
	SELECT @charvalue = ''
	SELECT @i = 1
	SELECT @length = DATALENGTH(@BinPass)
	SELECT @hexstring = '0123456789abcdef'

	--�o�C�i���f�[�^����16�i������ւ̌^�ϊ�����
	WHILE (@i <= @length)
	BEGIN

		DECLARE	@tempint	INT
		DECLARE	@firstint	INT
		DECLARE	@secondint	INT

		/*
		Transact-SQL CONVERT�R�}���h�́A1�o�C�g1���������Ńo�C�i�� �f�[�^�𕶎��f�[�^�ɕϊ����܂��B
		SQL Server�̓\�[�X �o�C�i�� �f�[�^�̊e�o�C�g�𐮐��l�ɕϊ����A���ɂ��̐����l�����敶���f�[�^��ASCII�l�Ƃ��Ďg�p���܂��B
		���̋@�\�́A�o�C�i���^�A�ώ�̃o�C�i���^�A�^�C���X�^���v�^�̃f�[�^�ɓK�p����܂��B
		���Ƃ��΁A�o�C�i���̒l00001111 (16�i�@�ł́A0x0F) �́A����ɑ������鐮���l�ł���15�ɕϊ�����A����ASCII�̒l15 (����͓ǂ߂Ȃ�)�ɑΉ����镶���ɕϊ�����܂�
		*/
		SELECT @tempint = CONVERT(INT, SUBSTRING(@BinPass , @i , 1))
		SELECT @firstint = FLOOR(@tempint / 16)
		SELECT @secondint = @tempint - (@firstint * 16)

		SELECT @charvalue = @charvalue + SUBSTRING(@hexstring, @firstint + 1, 1) + SUBSTRING(@hexstring, @secondint + 1, 1)

		SELECT @i = @i + 1

	END

	RETURN( @charvalue )

END
