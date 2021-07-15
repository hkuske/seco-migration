USE [seco]
GO
/****** Object:  StoredProcedure [dbo].[att_save_2file_JP]    Script Date: 7/14/2021 6:38:27 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO


ALTER PROCEDURE [dbo].[att_save_2file_JP]
AS
BEGIN
SET NOCOUNT ON;

declare @ObjectToken INT
declare @Body VARBINARY(MAX)
declare @out_body VARBINARY(MAX)
declare @Save2File NVARCHAR(255)
declare @id nvarchar(200)
declare @newfile nvarchar(100)
declare @ext nvarchar(4)
declare @FileName nvarchar(255)
DECLARE @HR int;
declare @Source nvarchar(255)
declare @Desc nvarchar(255)

declare @fcounter INT

DECLARE TheCursor CURSOR FAST_FORWARD
FOR 
SELECT 
    id,
--	BLOBKEY,
--	replace(BLOBKEY,'?','_'), 
--	'null', 
	CAST(N'' AS XML).value('sql:column(''FileAtt'')','VARBINARY(MAX)' ),
	CAST(CASE WHEN DATALENGTH(FileAtt) = 0 THEN '' ELSE REPLACE(RIGHT (Body , 4) , '.' , '') END AS nvarchar(4))
from Staging_Attachments_JP


OPEN TheCursor 
FETCH NEXT FROM TheCursor INTO @id, @Body, @ext

SET @fcounter = 0 

WHILE @@FETCH_STATUS = 0
-- WHILE @fcounter < 15   -- restriction only for testing
BEGIN
SET @fcounter = @fcounter  + 1
-- SET @newfile = RIGHT('00000000' + @id,8) 
SET @newfile = @id
PRINT @fcounter
PRINT @id

if @ext <> ''
BEGIN
		SET @newfile = @newfile + '.' + @ext
END


--SET @Save2File = 'E:\migration\notes\' + convert(varchar(100), @id)
SET @Save2File = 'E:\migration\notes\' + @newfile 
PRINT @Save2File

/*
UPDATE Staging_Attachments_JP 
	SET "Key" = @newfile
	WHERE  BLOBKEY = @id;
*/

EXEC sp_OACreate 'ADODB.Stream', @ObjectToken OUTPUT -- Creates an instance of an OLE object.
EXEC sp_OASetProperty @ObjectToken, 'Type', 1 -- Sets a property of an OLE object to a new value.
EXEC sp_OAMethod @ObjectToken, 'Open' -- Calls a method of an OLE object.
EXEC @HR=sp_OAMethod @ObjectToken, 'Write', NULL, @Body
EXEC @HR=sp_OAMethod @ObjectToken, 'SaveToFile', NULL, @Save2File, 2
EXEC sp_OAMethod @ObjectToken, 'Close'
EXEC sp_OADestroy @ObjectToken -- Destroys a created OLE object.



FETCH NEXT FROM TheCursor INTO @id, @Body, @ext
END


CLOSE TheCursor
DEALLOCATE TheCursor

 return @fcounter

END


