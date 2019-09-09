PRAGMA foreign_keys= OFF;
BEGIN TRANSACTION;
CREATE TABLE `upload_files`
(
    id         INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    user_id    TEXT    NOT NULL,
    file_name  TEXT    NOT NULL,
    size       INTEGER NOT NULL,
    created_at INTEGER NOT NULL DEFAULT 0
);
COMMIT;
