#!/bin/bash
tar xzvf ~/KEYS.tgz
cd static
sed --file ../KEYS.sed nginx.conf.no-keys >nginx.conf
cd ../legiscan
./legiscan-latest
sed --file ../KEYS.sed config.php.no-keys >config.php
cd ../isf-wiki
chmod go+r KEYS.php
chmod go+r LocalSettings.php
cd ..
