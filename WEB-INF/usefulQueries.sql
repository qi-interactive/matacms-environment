
-- get the latest version of all documents available in arhistory_revision and produce an sql statement to publish them
SELECT CONCAT('insert into matacms_itemenvironment values(''',REPLACE(arhistory_revision.DocumentId, '\\', '\\\\'), ''', ', tt.Revision, ', ''LIVE'');')
FROM arhistory_revision
INNER JOIN (
	SELECT DocumentId, MAX(Revision) Revision
	FROM arhistory_revision AS l
	GROUP BY DocumentId
) tt ON (arhistory_revision.DocumentId = tt.DocumentId)
GROUP BY arhistory_revision.DocumentId
LIMIT 1000000