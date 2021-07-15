USE [seco]
GO

/****** Object:  View [dbo].[Attachments_$(country)]    Script Date: 7/16/2020 1:53:34 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO



CREATE VIEW [dbo].[Attachments_$(country)]
AS
SELECT   BLOBKEY, 
		CAST(LEN(FileAtt) AS int) AS Filesize, 
		CAST(CASE WHEN len(FileAtt) = 0 THEN '' ELSE Body END AS varchar(200)) AS Filename, 
		CAST(CASE WHEN len(FileAtt) = 0 THEN '' ELSE REPLACE(RIGHT(Body, 4), '.', '') END AS varchar(4)) AS Extension, 
		CustomerNumber, 
		Description, 
		PREVCUSTNUM, 
		SalesRep, 
		Subject, 
        Subsidiary, 
		SECO_Salesman, 
		CompanyName
FROM     dbo.Staging_Attachments_$(country)
GO

