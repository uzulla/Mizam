#!/bin/bash

echo "migration/* 以下のSQLを結合したSQLを生成します"
echo "このツールで生成するSQLはあくまでもサンプルコードの「手抜き」のためです"
echo "**実用的なものでは有りません**"

ls migration/* |sort |cat migration/* > generate_sqlite_ddl.sql
