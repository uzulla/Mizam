#!/bin/bash

# open psql shell with heroku run.

# DATABASE_URL="postgres://lxxxxi:9xxxxx0@ec2xxxx.example.jp:5432/dxxxxt"

if [[ ${DATABASE_URL} =~ ^postgres://([0-9a-z]+)\:([0-9a-z]+)@([^\:]+)\:([0-9]+)/(.+)$ ]]; then
  username=${BASH_REMATCH[1]}
  password=${BASH_REMATCH[2]}
  host=${BASH_REMATCH[3]}
  port=${BASH_REMATCH[4]}
  dbname=${BASH_REMATCH[5]}

  echo username: ${username}
  echo password: ${password}
  echo host: ${host}
  echo port: ${port}
  echo dbname: ${dbname}

  export PGPASSWORD=${password}

  psql -h ${host} -U ${username} -p ${port} ${dbname}

else
  echo failed to parse DATABASE_URL. please cheack ENV.
fi
