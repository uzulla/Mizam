#!/bin/bash

./generate_pgsql_ddl.sh

cat ./generate_pgsql_ddl.sql | ./heroku_import_ddl.sh
