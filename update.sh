#!/usr/bin/env bash

rsync -avz --exclude=".[!.]*" * /var/www/tardigrade.ddns.net
