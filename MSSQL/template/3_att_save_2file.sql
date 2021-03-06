USE [seco]
GO
/****** Object:  StoredProcedure [dbo].[att_save_2file]    Script Date: 7/16/2020 1:49:46 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER PROCEDURE [dbo].[att_save_2file]
AS
BEGIN
SET NOCOUNT ON;

declare @ObjectToken INT
declare @Body VARBINARY(MAX)
declare @out_body VARBINARY(MAX)
declare @Save2File VARCHAR(255)
declare @id varchar(100)
declare @FileName varchar(255)
DECLARE @HR int;
declare @Source varchar(255)
declare @Desc varchar(255)

declare @fcounter INT

DECLARE TheCursor CURSOR FAST_FORWARD FOR 

SELECT replace(BLOBKEY,'?','_'), 'null', CAST(N'' AS XML).value('sql:column(''FileAtt'')','VARBINARY(MAX)' ) 
from Staging_Attachments_$(country)

OPEN TheCursor 
FETCH NEXT FROM TheCursor INTO @id, @FileName, @Body

SET @fcounter = 0 

WHILE @@FETCH_STATUS = 0
-- WHILE @fcounter < 15   -- restriction only for testing
BEGIN
SET @fcounter = @fcounter  + 1
PRINT @fcounter
PRINT @id
SET @Save2File = 'E:\migration\notes\' + convert(varchar(100), @id)
PRINT @Save2File
EXEC sp_OACreate 'ADODB.Stream', @ObjectToken OUTPUT -- Creates an instance of an OLE object.
EXEC sp_OASetProperty @ObjectToken, 'Type', 1 -- Sets a property of an OLE object to a new value.
EXEC sp_OAMethod @ObjectToken, 'Open' -- Calls a method of an OLE object.
EXEC @HR=sp_OAMethod @ObjectToken, 'Write', NULL, @Body
EXEC @HR=sp_OAMethod @ObjectToken, 'SaveToFile', NULL, @Save2File, 2
EXEC sp_OAMethod @ObjectToken, 'Close'
EXEC sp_OADestroy @ObjectToken -- Destroys a created OLE object.
FETCH NEXT FROM TheCursor INTO @id, @FileName, @Body
END


CLOSE TheCursor
DEALLOCATE TheCursor

 return @fcounter

END
