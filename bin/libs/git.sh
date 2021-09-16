#!/usr/bin/env bash

set -eu

########################
#
# Get the list of changed files which need to be fixed
#
########################
get_fixable_files () {
  local files=$1

  fixable_files=""

  for file in $files
  do
    if echo "$file" | grep 'css\|scss\|html\|js\|ts\|php'; then
      fixable_files="$fixable_files $file"
    fi
  done
  echo "$fixable_files"
}
