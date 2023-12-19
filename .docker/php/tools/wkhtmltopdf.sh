#!/bin/sh

apk add --no-cache \
    wkhtmltopdf \
    xvfb \
    ttf-dejavu ttf-droid ttf-freefont ttf-liberation

wget https://nodevo-devops.s3.eu-west-3.amazonaws.com/binaries/wkhtmltopdf/alpine/wkhtmltopdf -O /usr/bin/wkhtmltopdf
wget https://nodevo-devops.s3.eu-west-3.amazonaws.com/binaries/wkhtmltopdf/alpine/wkhtmltoimage -O /usr/bin/wkhtmltoimage

chmod +x /usr/bin/wkhtmltopdf
chmod +x /usr/bin/wkhtmltoimage
