# サンプルアプリ Mizam

サンプルアプリケーションです。

## Setup

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

## settings

TBD

## requirement

- PHP7.3

## テストデータアカウント

`user@exmpele.jp` / `pass`



