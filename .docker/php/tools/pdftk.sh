apk add openjdk8
wget https://gitlab.com/pdftk-java/pdftk/-/jobs/924565145/artifacts/raw/build/libs/pdftk-all.jar
mv pdftk-all.jar pdftk.jar

echo '#!/usr/bin/env bash
java -jar "$0.jar" "$@"
' > pdftk

chmod 775 pdftk*
mv pdftk* /usr/local/bin
