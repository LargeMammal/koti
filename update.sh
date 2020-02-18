#!/usr/bin/env bash

rsync -avz --exclude=".[!.]*" . /srv/http/tardigrade.ddns.net/
