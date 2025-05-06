#!/bin/bash
cd ~/projects/php/SL5_preg_contentFinder || exit
for testfile_relative in $(find tests/PHPUnit -maxdepth 1 -name '*.php' ! -name 'PHPUnitAllTest_AutoCollected.php' ! -name 'create_1file_withAll_PHPUnit_tests.php')
do

  output=$(docker run --rm -v "$(pwd):/app" -w /app sl5-preg-contentfinder-php56-baseline php /usr/local/bin/phpunit "$testfile_relative" 2>&1)

  if echo "$output" | grep -E -q "FAILURES!|ERRORS!"; then
    echo "!!! Found Failures/Errors in $testfile_relative !!!"
    # Zeige die Ausgabe ab "There was/were..." oder "FAILURES!/ERRORS!", filtere OK-Zeilen, Punkte, Zeit und Speicher.
    echo "$output" | \
    sed -n -e '/There was .* failure:/,$p' -e '/There were .* failures:/,$p' -e '/There was .* error:/,$p' -e '/There were .* errors:/,$p' -e '/^FAILURES!/,$p' -e '/^ERRORS!/,$p' | \
    sed -e '/^OK/d' -e '/Time: /d' -e '/Memory: /d' -e '/PHPUnit 3\.7\.38 by Sebastian Bergmann\.$/d' -e '/^\s*\.*$/d' -e '/^$/d'
  else
    # Optional: Nichts ausgeben bei Erfolg, oder eine kurze OK-Meldung
    # echo "OK - $testfile_relative"
    : # Nichts tun bei Erfolg
  fi
done
