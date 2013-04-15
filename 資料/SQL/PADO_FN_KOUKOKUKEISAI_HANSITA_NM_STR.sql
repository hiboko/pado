USE [base_web]
GO

/****** Object:  UserDefinedFunction [dbo].[PADO_FN_KOUKOKUKEISAI_HANSITA_NM_STR]    Script Date: 04/08/2013 17:16:07 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

-- =================================================
-- Author:		Aihara
-- Create date: 2013/04/08
-- Description:	ÅyçLçêÅzì`ï[ñæç◊î≈â∫çLçêñºéÊìæ
-- =================================================
CREATE FUNCTION [dbo].[PADO_FN_KOUKOKUKEISAI_HANSITA_NM_STR]
(
	 @KAISHA_CD			NVARCHAR(15)		--âÔé–ÉRÅ[Éh
	,@DENPYO_NO			NVARCHAR(12)		--ì`ï[î‘çÜ
	,@MEISAI_NO			DECIMAL(3,0)		--ñæç◊î‘çÜ
)
RETURNS NVARCHAR(2000)
AS
BEGIN

	DECLARE @wStr	NVARCHAR(2000) = ''

	SELECT @wStr = @wStr + A.KOUKOKU_NM + ','
	  FROM ADSD_TBL_KOUKOKUKEISAI_HANSITA AS A WITH(NOLOCK)
	WHERE ISNULL(A.DEL_FLG, 0) = 0
	  AND A.KAISHA_CD = @KAISHA_CD
	  AND A.DENPYO_NO = @DENPYO_NO
	  AND A.MEISAI_NO = @MEISAI_NO

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


