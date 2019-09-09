PRAGMA foreign_keys= OFF;
BEGIN TRANSACTION;
CREATE TABLE `sign_up_tokens`
(
    id                   INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    token                TEXT    NOT NULL,
    expire_at            INTEGER NOT NULL DEFAULT 0,
    user_login_id        TEXT    NOT NULL,
    user_hashed_password TEXT    NOT NULL,
    user_nick            TEXT    NOT NULL,
    user_created_at      INTEGER NOT NULL,
    user_updated_at      INTEGER NOT NULL
);
COMMIT;
