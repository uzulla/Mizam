#!/bin/bash

set -eu

# echo ディレクトリ構造 \(Document root\) が制限されたレンサバ用にファイルを再配置

# すでにファイルが存在しないかチェック
if [[ ! -e vendor ]]; then
  echo vendorがありませんので終了します。dev-setupなどでcomposer installは完了していますか？
  exit 1
fi

# すでにファイルが存在しないかチェック
if [[ -e restriction_htdocs ]]; then
  echo すでに restriction_htdocs が存在しますので中止します。
  exit 1
fi

mkdir restriction_htdocs
cp -a public/* restriction_htdocs/
mkdir restriction_htdocs/app


if [ "$(uname)" == 'Darwin' ]; then
  ls  | grep -v -e ^public -e ^restriction_htdocs | xargs -J% cp -a % restriction_htdocs/app/
elif [ "$(expr substr $(uname -s) 1 5)" == 'Linux' ]; then
  ls  | grep -v -e ^public -e ^restriction_htdocs | xargs -u cp -a {} restriction_htdocs/app/
else
  echo このスクリプトはLinuxとmacOS以外はサポートしていません。
  exit 1
fi

# echo 多くのレンサバはSymlinkを作る方法が（公式に）提供されていないので、thumbnail ファイル のディレクトリを実ディレクトリに変更
rm restriction_htdocs/thumbnail_files
mkdir restriction_htdocs/thumbnail_files

# echo app以下を保護するためにhtaccessを設置（Apacheのみ有効）
cp for_hosting_server/deny_all.htaccess restriction_htdocs/app/

# echo autoloadのPathを変更したindex.phpを設置
cp for_hosting_server/index.php restriction_htdocs/index.php

# echo レンサバ用の.htaccess
cp for_hosting_server/sample.htaccess restriction_htdocs/.htaccess

# echo レンサバ用のdev.envを生成
cp sample.env restriction_htdocs/app/dev.env
sed -i -e "s|DB_DSN=\"sqlite:../db/sqlite.db\"|DB_DSN=\"sqlite:app/db/sqlite.db\"|g" restriction_htdocs/app/dev.env
sed -i -e "s|LOCAL_BLOB_STORE_DIR=\"../upload_files/original_files\"|LOCAL_BLOB_STORE_DIR=\"app/upload_files/original_files\"|g" restriction_htdocs/app/dev.env
sed -i -e "s|LOCAL_THUMBNAIL_STORE_DIR=\"../upload_files/thumbnail_files\"|LOCAL_THUMBNAIL_STORE_DIR=\"upload_files/thumbnail_files\"|g" restriction_htdocs/app/dev.env

echo restriction_htdocs/ にファイルを再配置しました。
