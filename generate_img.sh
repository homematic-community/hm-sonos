#!/bin/sh
mkdir -p tmp
cp -a sonos2 tmp/
cp -a rc.d tmp/
cp -a www tmp/
cp -a ccu2 tmp/
cp -a update_script tmp/
cp -a VERSION tmp/sonos2/
cd tmp

tar --owner=root --group=root -czvf ../sonos2-$(cat ../VERSION).tar.gz *
cd ..
rm -rf tmp
