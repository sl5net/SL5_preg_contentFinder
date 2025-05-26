# VS Code: Jump to File and Line from Clipboard (AutoKey Script)

This document describes an AutoKey script that allows you to quickly jump to a specific file and line number in Visual Studio Code by copying a `filename:linenumber` string to the clipboard and pressing a hotkey.

## Purpose

When viewing log files or error messages that reference a file and line number (e.g., `MyClass:123` often also works with `MyClass.php:123`), this script automates the process of:
1. Script not will be triggered when not in VS Code (Recommended config).
2. Copying the reference.
3. Using VS Code's "Go to File..." (Ctrl+P) command.
4. Pasting the reference.
5. Navigating to the specified line.

## Prerequisites

*   **AutoKey Installed:** This script is for [AutoKey](https://github.com/autokey/autokey).
*   **Python:** AutoKey scripts are typically Python.
*   **VS Code:** The script is designed to interact with Visual Studio Code.

## AutoKey Script Code

```python
# AutoKey Script: VS Code Jump to Line
#
# Recommended Hotkey: F11 (or your preference)
# Recommended Window Filter: code.Code (for VS Code)
#
# For more examples/ideas: https://github.com/sl5net/0ad-autokey-scripts/
#
import time

# Small delay to ensure focus or previous actions complete
time.sleep(.05) # Increased slightly from 0.01 for robustness

import time
time.sleep(.01)
# get fileName and FileNumer
# expected formats: 
# Test.php:16
# Test:16
keyboard.send_keys("<home>")
keyboard.send_keys("<ctrl>+<shift>+<right>")
keyboard.send_keys("<ctrl>+<shift>+<right>")
keyboard.send_keys("<ctrl>+c")
keyboard.send_keys("<ctrl>+p")
keyboard.send_keys("<ctrl>+v")
time.sleep(.1)
keyboard.send_keys("<enter>")

```

