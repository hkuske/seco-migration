USE [seco]
GO

/****** Object:  Table [dbo].[Staging_Attachments_JP]    Script Date: 7/14/2021 8:54:11 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[Staging_Attachments_JP](
	[BLOBKEY] [nvarchar](100) NOT NULL,
	[FileAtt] [varbinary](max) NULL,
	[Key] [nvarchar](100) NULL,
	[Body] [nvarchar](500) NULL,
	[CompanyLocationName] [nvarchar](100) NULL,
	[CompanyName] [nvarchar](100) NULL,
	[CustomerNumber] [nvarchar](50) NULL,
	[Description] [nvarchar](500) NULL,
	[PREVCUSTNUM] [nvarchar](20) NULL,
	[SalesRep] [nvarchar](100) NULL,
	[SECO_Salesman] [nvarchar](50) NULL,
	[Subject] [nvarchar](200) NULL,
	[Subsidiary] [nvarchar](50) NULL,
	[id] [int] IDENTITY(1,1) NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

