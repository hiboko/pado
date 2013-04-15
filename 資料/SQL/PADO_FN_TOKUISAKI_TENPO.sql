USE [base_web]
GO

/****** Object:  UserDefinedFunction [dbo].[PADO_FN_TOKUISAKI_TENPO]    Script Date: 04/03/2013 18:11:13 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

-- =================================================
-- Author:		Aihara
-- Create date: 2013/04/03
-- Description:	得意先店舗命名をカンマ区切りで返却
-- =================================================
CREATE FUNCTION [dbo].[PADO_FN_TOKUISAKI_TENPO]
(
	 @KAISHA_CD			NVARCHAR(15)		--会社コード
	,@TOKUISAKI_NO		NVARCHAR(15)		--得意先コード
)
RETURNS NVARCHAR(2000)
AS
BEGIN

	DECLARE @wStr	NVARCHAR(2000) = ''

	SELECT @wStr = @wStr + TENPO_NM + ','
	  FROM CM_MST_TOKUISAKI_TENPO_INFO WITH(NOLOCK)
	WHERE ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0
	  AND KAISHA_CD = @KAISHA_CD
	  AND TOKUISAKI_SNO = @TOKUISAKI_NO

	IF @wStr = ''
		BEGIN
			SET @wStr = Null
		END
	ELSE
		BEGIN
			SET @wStr = LEFT(@wStr, LEN(@wStr) - 1)
		END

	RETURN( @wStr )

END

GO


