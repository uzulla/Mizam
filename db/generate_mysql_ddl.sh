#!/bin/bash

echo "migration/* 以下にあるsqlite3用のDDLからMysqlに通せるSQLを生成しています。"
echo "このツールで生成するSQLはあくまでもサンプルコードの「手抜き」のためです"
echo "**実用的なものでは有りません**"

echo "-- THIS IS GENERATE FILE. DON'T EDIT THIS!!!" > generate_mysql_ddl.sql
echo "BEGIN;" >> generate_mysql_ddl.sql
ls migration/* |sort |cat migration/* | \
perl -pe 's/AUTOINCREMENT/AUTO_INCREMENT/; s/PRAGMA foreign_keys= ON;//; s/PRAGMA foreign_keys= OFF;//; s/BEGIN TRANSACTION;//; s/COMMIT;//; s/INTEGER/BIGINT/; s/\)\; \-\- FOR_MYSQL \-\- /\) /;' \
 >> generate_mysql_ddl.sql
echo "COMMIT;" >> generate_mysql_ddl.sql
