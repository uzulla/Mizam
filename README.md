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
$ cp sample.env heroku.env
$ vi heroku.env
$ make heroku-update-config # appにheroku.envの内容を反映

# 必要なaddonの有効化
$ heroku addons:create heroku-postgresql:hobby-dev
# REDIS_URLがENVにあれば、REDISをsessionで使うようになっています
$ heroku addons:create heroku-redis:hobby-dev

# コードのデプロイ
$ make heroku-deploy

# DBへサンプルのDBを反映
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


## settings

TBD

## requirement

- PHP7.3

## テストデータアカウント

`user@exmpele.jp` / `pass`



