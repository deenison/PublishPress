#!/bin/sh

# Sync the src folder into wordpress, to auto-update the code while 
# developing. Used with fswatch in Mac OS to detect file changes.

# rsync -rtvu --delete ./src ~/Projects/OSTraining/Git/dev-env-wordpress/www/wp-content/plugins/publishpress-3
rm -rf ~/Projects/OSTraining/Git/dev-env-wordpress/www/wp-content/plugins/publishpress-3/*
cp -R ./src/* ~/Projects/OSTraining/Git/dev-env-wordpress/www/wp-content/plugins/publishpress-3/