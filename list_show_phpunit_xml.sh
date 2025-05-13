#!/bin/bash


# Find files
files=$(find / -name "phpunit.xml*" 2>/dev/null)

# Loop over files and show their content
for file in $files; do
    if [ -f "$file" ]; then
        echo "File: $file"
        cat "$file"
        echo "" # Add an empty line between files
    fi
done
