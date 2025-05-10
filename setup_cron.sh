#!/bin/bash

# Absolute paths
PHP_PATH=$(which php)
CRON_FILE="$(cd "$(dirname "$0")"; pwd)/cron.php"
CRON_CMD="*/5 * * * * $PHP_PATH $CRON_FILE"

# Check if job already exists
(crontab -l 2>/dev/null | grep -F "$CRON_FILE") >/dev/null

if [ $? -eq 0 ]; then
    echo "CRON job already exists."
else
    (crontab -l 2>/dev/null; echo "$CRON_CMD") | crontab -
    echo "CRON job added to run every 5 minutes."
fi
