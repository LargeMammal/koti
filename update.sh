#!/usr/bin/env bash

rsync -avz --exclude=".[!.]*" . /srv/http
