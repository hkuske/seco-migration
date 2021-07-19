USE [seco]
GO

/****** Object:  View [dbo].[Attachments_JP]    Script Date: 7/14/2021 8:47:39 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO



CREATE OR ALTER VIEW [dbo].[Attachments_JP]
AS
SELECT   BLOBKEY, 
		CAST(DATALENGTH(FileAtt) AS int) AS Filesize, 
		CAST(CASE WHEN DATALENGTH(FileAtt) = 0 THEN '' ELSE Body END AS nvarchar(200)) AS Filename, 
		CAST(CASE WHEN DATALENGTH(FileAtt) = 0 THEN '' ELSE REPLACE(RIGHT(Body, 4), '.', '') END AS nvarchar(4)) AS Extension, 
		id, 
		Description, 
		PREVCUSTNUM, 
		SalesRep, 
		Subject, 
        Subsidiary, 
		SECO_Salesman, 
		CompanyName
FROM     dbo.Staging_Attachments_JP
GO


