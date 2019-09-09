PRAGMA foreign_keys= OFF;
BEGIN TRANSACTION;
CREATE TABLE `users`
(
    id              INTEGER      NOT NULL PRIMARY KEY AUTOINCREMENT,
    login_id        VARCHAR(128) NOT NULL UNIQUE,
    hashed_password TEXT         NOT NULL,
    nick            TEXT         NOT NULL,
    updated_at      INTEGER      NOT NULL DEFAULT 0,
    created_at      INTEGER      NOT NULL DEFAULT 0
);
COMMIT;
