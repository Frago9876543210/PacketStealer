#!/bin/bash
apt-get update > /dev/null
apt-get install wget curl zip unzip gcc g++ make cmake -y
wget -nc https://raw.githubusercontent.com/Frago9876543210/modloader-helper/master/helper.sh && chmod +x helper.sh
source helper.sh > /dev/null
setup_sdk && cd ..
mkdir -p $LIBS && MODS_CODE=./
build_mod DisableEncryption && cd ../..

wget -nc https://raw.githubusercontent.com/pmmp/PocketMine-DevTools/master/src/DevTools/ConsoleScript.php
wget -nc https://jenkins.pmmp.io/job/PHP-7.2-Linux-x86_64/lastSuccessfulBuild/artifact/PHP_Linux-x86_64.tar.gz
tar xzf PHP_Linux-x86_64.tar.gz
./bin/composer install --prefer-dist --no-interaction
./bin/php7/bin/php -dphar.readonly=0 ConsoleScript.php --make src,vendor --relative . --entry "src/PacketStealer.php" --out PacketStealer.phar
mkdir artifacts && cd artifacts
zip -r PacketStealer.zip ../mods ../PacketStealer.phar