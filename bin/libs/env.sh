#!/usr/bin/env bash

set -eu

#######################################
# Read a var from .env file
#
# Arguments:
#   1 the name of the env variable
#   2 default value for desired variable
#######################################
read_env_var() {
    VAR=$(grep "^$1=" ./.env | xargs)
    VAR=${VAR#*=}

    if [ -z "$VAR" ]
    then
          echo "$2"
    else
          echo "${VAR}"
    fi
}
