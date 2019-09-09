DOCROOT = public/
DEV_SERVER_PORT = 8080
DEV_SERVER_LISTEN_IP = 127.0.0.1
PHP_PATH = php

.PHONY: start
start:
	$(PHP_PATH) -S $(DEV_SERVER_LISTEN_IP):$(DEV_SERVER_PORT) -t $(DOCROOT)

dev-setup: composer.phar composer-install local-reset dev.env 

dev.env:
	cp sample.env dev.env

composer.phar:
	curl -sSfL -o composer-setup.php https://getcomposer.org/installer
	$(PHP_PATH) composer-setup.php --filename=composer.phar
	rm composer-setup.php

.PHONY: composer-install
composer-install:
	$(PHP_PATH) composer.phar install

.PHONY: local-reset
local-reset:
	-rm db/sqlite.db
	echo db/migration/* | sort | xargs cat | sqlite3 db/sqlite.db
	-rm upload_files/thumbnail_files/*
	-rm upload_files/original_files/*

.PHONY: test
test:
	$(PHP_PATH) vendor/bin/phpunit tests/

# for heroku

.PHONY: heroku-update-config
heroku-update-config:
	cat heroku.env | grep -v '^#' | grep -v '^\s*$$' | xargs -L 1 echo heroku config:set | sh

.PHONY: heroku-deploy
heroku-deploy:
	git push heroku master

.PHONY: heroku-log-tail
heroku-log-tail:
	heroku logs -t

# ファイルをコピーしてレンサバ用のhtdocsを生成、事前にdev-setupをして下さい。
.PHONY: restriction_htdocs
restriction_htdocs:
	for_hosting_server/mk_restriction_htdocs.sh

# uzulla作業用
.PHONY: uzulla-local-reset-all
uzulla-local-reset-all: local-reset
	find . | grep .DS_Store |xargs rm
	-rm composer.phar
	-rm db/sqlite.db
	-rm dev.env
	-rm -r vendor/
	-rm -r restriction_htdocs/
	git status --ignored
