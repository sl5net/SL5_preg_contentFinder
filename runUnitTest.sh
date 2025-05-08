#!/bin/bash
clear
# Stelle sicher, dass das Skript im Projekt-Root ausgeführt wird oder passe $(pwd) an
cd ~/projects/php/SL5_preg_contentFinder || exit # Stellt sicher, dass wir im richtigen Verzeichnis sind

echo "Running ALL PHPUnit tests (PHP, recursive). Displaying only summaries of failures and errors."
echo "=================================================================================================="

# -maxdepth 1 ENTFERNT, um rekursiv zu suchen
for testfile_relative in $(find tests/PHPUnit -name '*.php' ! -name 'PHPUnitAllTest_AutoCollected.php' ! -name 'create_1file_withAll_PHPUnit_tests.php' ! -name 'GetContent_Test.php')
do
  echo "" # Leerzeile für bessere Lesbarkeit zwischen den Tests
  echo "--- TESTING: $testfile_relative ---"
  vendor/bin/phpunit $testfile_relative
done

echo "=================================================================================================="
echo "All tests finished."
