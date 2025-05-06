#!/bin/bash

# Stelle sicher, dass das Skript im Projekt-Root ausgeführt wird oder passe $(pwd) an
cd ~/projects/php/SL5_preg_contentFinder || exit # Stellt sicher, dass wir im richtigen Verzeichnis sind

echo "Running ALL PHPUnit tests (PHP 7.4, recursive). Displaying only summaries of failures and errors."
echo "=================================================================================================="

# -maxdepth 1 ENTFERNT, um rekursiv zu suchen
for testfile_relative in $(find tests/PHPUnit -name '*.php' ! -name 'PHPUnitAllTest_AutoCollected.php' ! -name 'create_1file_withAll_PHPUnit_tests.php')
do
  echo "" # Leerzeile für bessere Lesbarkeit zwischen den Tests
  echo "--- TESTING: $testfile_relative ---"

  # Verwende das PHP 7.4 Image und den vendor/bin/phpunit Pfad
  output=$(docker run --rm -v "$(pwd):/app" -w /app sl5-preg-contentfinder-php74-dev php vendor/bin/phpunit "$testfile_relative" 2>&1)

  if echo "$output" | grep -E -q "FAILURES!|ERRORS!"; then
    echo "!!! Found Failures/Errors in $testfile_relative !!!"
    # Filter angepasst für PHPUnit 9.x Header
    echo "$output" | \
    sed -n -e '/There was .* failure:/,$p' \
           -e '/There were .* failures:/,$p' \
           -e '/There was .* error:/,$p' \
           -e '/There were .* errors:/,$p' \
           -e '/^FAILURES!/,$p' \
           -e '/^ERRORS!/,$p' | \
    sed -e '/^OK/d' \
        -e '/Time: /d' \
        -e '/Memory: /d' \
        -e '/PHPUnit [0-9]\+\.[0-9]\+\.[0-9]\+ by Sebastian Bergmann and contributors\.$/d' \
        -e '/^\s*\.*$/d' \
        -e '/^$/d' \
        -e '/^\s*$/d'
  else
    echo "OK - $testfile_relative" # Erfolg melden ist gut für die Übersicht
    # : # Alternative: Nichts tun bei Erfolg
  fi
done

echo ""
echo "=================================================================================================="
echo "All tests finished."
