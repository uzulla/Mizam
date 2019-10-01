# サンプルアプリ Mizam

サンプルアプリケーションです。

> 手順の簡略化のため、Makefileに色々と入れています。`make 〜`での具体的な処理はMakefileを見て下さい。

## セットアップ手順例

### mac またはLinuxのビルトインウェブサーバーのセットアップと起動

```
$ make dev-setup
$ make start
```

- 環境をセットアップします
  - composer.pharのダウンロード
  - composer installの実行
  - `sample.env`を`dev.env`にコピー
  - sqliteをDBとして初期化
- make start 後、 `Listening on http://127.0.0.1:8080` の行に、テスト用のURLが表示されます。


### Heroku

事前に以下が必要です。

- herokuアカウントを作っておく
- herokuにカードを登録しておく（redis addonがカード登録していないと動かないはず）
- S3の設定を行っておく（Bucketをつくり、キーを取得しておく）
- (会員登録を動かすなら)送信用のsmtpを別途用意する（テストならMailtrapなどでOK）
- heroku のダッシュボードにて新しいアプリを作成しておく

```
$ heroku login

# サンプルアプリを手元にClone
$ git clone git@github.com:uzulla/Mizam.git mizam-heroku
$ cd mizam-heroku
$ heroku git:remote -a your-heroku-app-name

# heroku.envの作成、サンプルは後述。一つ一つ heroku config:set FOO=BAR すれば必須ではない
# (app.jsonのenvプロパティに書いても良いのですが、本アプリは学習用なので)
$ cp sample.env heroku.env
$ vi heroku.env
$ make heroku-update-config # appにheroku.envの内容を反映

# 必要なaddonの有効化
# (app.jsonのaddonプロパティに書いても良いのですが、本アプリは学習用なので)
$ heroku addons:create heroku-postgresql:hobby-dev
# REDIS_URLがENVにあれば、REDISをsessionで使うようになっています
$ heroku addons:create heroku-redis:hobby-dev

# コードのデプロイ
$ make heroku-deploy

# DBへサンプルのDBを反映
# (ちゃんとしたDBスキーママイグレーションツールをつかうなら、app.jsonのscripts.postdeployに指定してもよい）
$ make heroku-import-to-psql-sample

$ heroku open # ブラウザが開いて確認
```

`heroku.env 例`
```
# make heroku-config-update で herokuに反映

# general settings
SITE_URL="https://mizam-test-201909.herokuapp.com/"
MAIL_FROM="nobody@example.test"

# ログ出力先設定 disable=無効、 stream=ファイル
DEBUG_LOG_HANDLER_TYPE="disable"
DEBUG_LOG_PATH=""
EVENT_LOG_HANDLER_TYPE="stream"
EVENT_LOG_PATH="php://stderr"
ERROR_LOG_HANDLER_TYPE="stream"
ERROR_LOG_PATH="php://stderr"

# DB (heroku postgresql)
DB_TYPE="heroku_pg"

## 画像アップロードストレージ設定 s3 利用時
BLOB_STORE_CLASS="\\Mizam\\Repo\\UploadImageFileBlobS3Repo"
AWS_ACCESS_KEY_ID=AXXXXXXK
AWS_SECRET_ACCESS_KEY=KXXXXXXu
S3_BUCKET_NAME=XXXXXX
THUMBNAIL_STORE_BASE_URL="https://XXXXXX.s3-ap-northeast-1.amazonaws.com/thumbnail_images"

# メール送信設定 smtp利用時
MAIL_METHOD="smtp"
SMTP_HOST="smtp.mailtrap.io"
SMTP_PORT="587"
SMTP_USER_NAME="9XXXXXX2"
SMTP_USER_PASS="fXXXXXX7"
```

### レンタルサーバー等

htdocsより上位にファイルを設置できないサーバーで、サンプルコードを動かす例です。
「レンタルサーバーだからこうしなければならない」ということではありません。（しかし、多くのレンタルサーバーではこの制限があります）

`make restriction_htdocs`を実行してのファイル再配置は、サンプルコードを制限でも動作するように再配置を手順化したもので、これを利用して開発を続けるためのものではありません。再配置した後は再配置後のファイルを修正していくことになります。

```
# Composer installなどを実行
$ make dev-setup

$ make restriction_htdocs
# restriction_htdocs 以下にファイルが再配置されます
# これ以後はrestriction_htdocsの中のファイルをいじって下さい。

$ cd restriction_htdocs
# 設定を必要に応じて修正してください
$ vi dev.env

# restriction_htdocs内のファイルをすべてアップロードします
# .htaccessなど、dotファイルをアップロード漏れしないように注意
$ lftp hoge@example.jp
lftp> cd htdocs
lftp> mirror -R . .
```

以上で動作するはずです。

エラーが出る場合はエラーログを確認し、PHPのバージョンが適切か確認して下さい（PHP7.3を想定しています）


#### Mysqlをつかう

デフォルトではsqliteをDBとして用いているので、mysqlをDBとするようにenvを修正します。

事前に業者のコンパネなどでデータベースを作り、接続情報をメモして下さい。

```
# (手元のPCで）
$ cd app/db
$ ./generate_mysql_ddl.sh
# migration/* 以下にあるsqlite3用のDDLからMysqlに通せるSQLを生成しています。
# このツールで生成するSQLはあくまでもサンプルコードの「手抜き」のためです
# **実用的なものでは有りません**

$ cat ./generate_mysql_ddl.sql
# このSQLをレンサバ業者が提供するDB操作ツール等で実行します。
# usersなどのテーブルが作成されます。
```

`dev.env`等を修正します。まずはsqliteの記述を無効にします、以下の行を削除やコメントアウト（行頭に#)してください。

```
# DB設定 sqlite利用時
DB_TYPE="sqlite"
# pubic/index.php からの相対パス
DB_DSN="sqlite:app/db/sqlite.db"
```

次にMysqlの情報を有効にします。これらをレンサバの情報に書き直し、コメントイン（行頭の#を削除）します。

```
# DB設定 mysqld利用時
DB_TYPE="mysql"
DB_DSN="mysql:host=mysqlXXX.db.sakura.ne.jp;dbname=mizam-sample;charset=utf8mb4"
DB_USER_NAME="mizam-sample"
DB_USER_PASS="XXXXXXXXXXXXXXX"
```

sqliteでなく、Mysqlで動作しているか確認するには、`app/db/sqlite.db`を削除してログインしてみるなどでよいでしょう。



## settings

TBD

## requirement

- PHP7.3

## テストデータアカウント

`user@exmpele.jp` / `pass`



