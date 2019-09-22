DOCROOT = public/
DEV_SERVER_PORT = 8080
DEV_SERVER_LISTEN_IP = 127.0.0.1
PHP_PATH = php

# 開発環境準備系(主にローカル開発用)

dev-setup: composer.phar composer-install local-reset dev.env

dev.env:
	cp sample.env dev.env

composer.phar:
	curl -sSfL -o composer-setup.php https://getcomposer.org/installer
	$(PHP_PATH) composer-setup.php --filename=composer.phar
	rm composer-setup.php

.PHONY: composer-install
composer-install: composer.phar
	$(PHP_PATH) composer.phar install

.PHONY: local-reset
local-reset:
	-rm db/sqlite.db
	echo db/migration/* | sort | xargs cat | sqlite3 db/sqlite.db
	-rm upload_files/thumbnail_files/*
	-rm upload_files/original_files/*

.PHONY: composer-dump-autoload-opt
composer-dump-autoload-opt:
	composer dump-autoload --optimize --no-dev

# # mysql利用時に初期データ流し込み。ENVが未設定だとdev（つまり、dev.envの情報を接続情報として使う）ものとして扱われます。
.PHONY: init-mysql
init-mysql: db/generate_mysql_ddl.sql composer-install
	$(PHP_PATH) cli/load_sample_to_mysql.php

db/generate_mysql_ddl.sql:
	echo "generate ddl"
	cd db && ./generate_mysql_ddl.sh


# for built in web server

# # ビルトインウェブサーバーの起動
.PHONY: start
start:
	$(PHP_PATH) -S $(DEV_SERVER_LISTEN_IP):$(DEV_SERVER_PORT) -t $(DOCROOT)

.PHONY: test
test:
	$(PHP_PATH) vendor/bin/phpunit tests/


# for heroku

# # Herokuにheroku.envの情報を設定（事前にheroku.envをsample.envからコピー作成してください）

.PHONY: heroku-update-config
heroku-update-config: heroku.env
	cat heroku.env | grep -v '^#' | grep -v '^\s*$$' | xargs -0 -L 1 echo heroku config:set | sh

.PHONY: heroku-deploy
heroku-deploy:
	git push heroku master

.PHONY: heroku-log-tail
heroku-log-tail:
	heroku logs -t

.PHONY: heroku-psql
heroku-psql:
	heroku run bash -c "db/heroku_open_psql_shell.sh"

.PHONY: heroku-import-to-psql-sample
heroku-import-to-psql-sample:
	heroku run bash -c "cd db && ./heroku_import_to_pgsql.sh"

# for hosting server

# # ファイルをコピーしてレンサバ用のhtdocsを生成、事前にdev-setupをして下さい。
.PHONY: restriction_htdocs
restriction_htdocs:
	for_hosting_server/mk_restriction_htdocs.sh


# uzulla作業用、全てが消えるので危険です。

.PHONY: uzulla-local-reset-all
uzulla-local-reset-all: local-reset
	find . | grep .DS_Store |xargs rm
	-rm composer.phar
	-rm db/sqlite.db
	-rm db/*.sql
	-rm dev.env
	-rm -r vendor/
	-rm -r restriction_htdocs/
	git status --ignored
