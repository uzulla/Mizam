#!/bin/bash

echo "migration/* 以下にあるsqlite3用のDDLからpostgresqlに通用するSQLを生成しています。"
echo "このツールで生成するSQLはあくまでもサンプルコードの「手抜き」のためです"
echo "**実用的なものでは有りません。**"

echo "-- THIS IS GENERATE FILE. DON'T EDIT THIS!!!" > generate_pgsql_ddl.sql
echo "BEGIN;" >> generate_pgsql_ddl.sql
ls migration/* | sort | xargs cat | \
perl -pe 's/AUTOINCREMENT/AUTO_INCREMENT/; s/PRAGMA foreign_keys= ON;//; s/`//g; s/"/'"'"'/g; s/PRAGMA foreign_keys= OFF;//; s/BEGIN TRANSACTION;//; s/COMMIT;//; s/INTEGER/BIGINT/; s/\)\; \-\- FOR_MYSQL \-\- /\) /; s/BIGINT[ ]+NOT NULL PRIMARY KEY AUTO_INCREMENT/SERIAL/;' \
 >> generate_pgsql_ddl.sql
echo "COMMIT;" >> generate_pgsql_ddl.sql
