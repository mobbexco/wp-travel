#!/bin/sh

VER="1.0.0"

if type rsync > /dev/null; then
    rsync --exclude='.git/' --exclude='.vscode/' --exclude='wp-travel-mobbex/' --exclude='*.zip' --exclude='readme.md' --exclude='build.sh' --exclude='wp-travel-mobbex' --exclude='.gitignore' . wp-travel-mobbex
elif type robocopy > /dev/null; then
    robocopy . wp-travel-mobbex //E //XD .git .vscode wp-travel-mobbex //XF .gitignore build.sh readme.md *.zip
fi

if type 7z > /dev/null; then
    7z a -tzip "wp-travel-mobbex.$VER.zip" wp-travel-mobbex
elif type zip > /dev/null; then
    zip wp-travel-mobbex.$VER.zip -r wp-travel-mobbex
fi

rm -r wp-travel-mobbex