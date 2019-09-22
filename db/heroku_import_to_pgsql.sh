#!/bin/bash

./generate_pgsql_ddl.sh

cat ./generate_pgsql_ddl.sql | ./heroku_open_psql_shell.sh
