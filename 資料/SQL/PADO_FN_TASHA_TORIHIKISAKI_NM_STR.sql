USE [base_web_test]
GO

/****** Object:  UserDefinedFunction [dbo].[PADO_FN_TASHA_TORIHIKISAKI_NM_STR]    Script Date: 03/15/2013 09:51:09 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =================================================
-- Author:		Aihara
-- Create date: 2013/03/15
-- Description:	他社取引先名をカンマ区切りで返却
-- =================================================
CREATE FUNCTION [dbo].[PADO_FN_TASHA_TORIHIKISAKI_NM_STR]
(
	 @KAISHA_CD			NVARCHAR(15)		--会社コード
	,@TORIHIKISAKI_CD	NVARCHAR(15)		--取引先コード
)
RETURNS NVARCHAR(2000)
AS
BEGIN

	DECLARE @wStr	NVARCHAR(2000) = ''

	SELECT @wStr = @wStr + B.TORIHIKISAKI_NM + ','
	  FROM BC_MST_SEIKYUSAKI_RENKEI AS A WITH(NOLOCK)
	 INNER JOIN
	 (
		SELECT KAISHA_CD, TORIHIKISAKI_CD, TORIHIKISAKI_NM
		  FROM CM_MST_TORIHIKISAKI WITH(NOLOCK)
		 WHERE ISNULL(DEL_FLG, 0) = 0 AND ISNULL(MUKOU_FLG, 0) = 0
	 ) AS B
	   ON A.KAISHA_CD = B.KAISHA_CD
	  AND A.SEIKYUSAKI_TORIHIKISAKI_CD = B.TORIHIKISAKI_CD
	WHERE ISNULL(A.DEL_FLG, 0) = 0
	  AND A.KAISHA_CD = @KAISHA_CD
	  AND A.TORIHIKISAKI_CD = @TORIHIKISAKI_CD
	  AND A.TORIHIKISAKI_CD <> A.SEIKYUSAKI_TORIHIKISAKI_CD

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
