# Universal Editor to VS Code: Jump to File/Line (AutoKey Script)

This document describes an AutoKey script for quickly navigating from various source applications (like Kate, Konsole, or VS Code's own integrated terminal) to a specific file and line number in the main VS Code editor.

The script uses editor-specific techniques to select the `Filename:LineNumber` portion of a line (e.g., from log output) and then uses VS Code's "Go to File..." feature to open it.

## Purpose

When viewing output that contains file and line references, such as:

*   `PregContentFinder:63[__construct()]INFO: greetings from PregContentFinder :) ...`
*   `F2T2_Test:32[test_F2T2c_WriteLog_File1Exists()]INFO: Hey from test_F2T2c_WriteLog_File1Exists ...`

This script allows you to:
1. Position your cursor on such a line in the source application (Kate, Konsole, VS Code terminal, etc.).
2. Press the configured hotkey (e.g., F11).
3. The script will attempt to select and copy just the `Filename:LineNumber` part (e.g., `PregContentFinder:63` or `F2T2_Test:32`).
4. It then activates VS Code, opens "Go to File...", pastes the reference, and navigates to the exact location.

VS Code's "Go to File..." is generally smart enough to handle common filename extensions (like `.php`) even if they are not explicitly selected, as long as the base filename and line number are provided (e.g., `PregContentFinder:63` will typically resolve to `PregContentFinder.php` line 63 if unambiguous).

## Prerequisites

*   **AutoKey Installed:** This script is for [AutoKey](https://github.com/autokey/autokey).
*   **Python:** AutoKey scripts are typically Python.
*   **VS Code, Kate, Konsole (or other source apps):** The script has specific logic for Kate and a general approach for others.

## AutoKey Script Code

```python
# recomandet hotkey ctrl+v # User note: Hotkey is typically F11 as per discussion
# recomandet Window Filter:  code.Code|konsole.konsole
# may read also https://github.com/sl5net/0ad-autokey-scripts/
import time
time.sleep(.01)

# get fileName and FileNumer
# expected formats:
# Test.php:16
# Test:16

keyboard.send_keys("<home>") # Go to beginning of line in current editor

winC = window.get_active_class()
if winC == 'kate.kate':
    # Kate-specific logic to position cursor after Filename:LineNumber
    keyboard.send_keys("<ctrl>+f:") # Use Kate's find to jump to the colon
    keyboard.send_keys("<escape>")   # Close find dialog
    # time.sleep(.2) # Original script had a sleep here, commented out
    keyboard.send_keys("<ctrl>+<right>") # Move past the line number
    # exit(0) # Original script had an exit here, commented out
else:
    # Generic logic for other editors (VS Code, Konsole, etc.)
    # to position cursor after Filename:LineNumber
    keyboard.send_keys("<ctrl>+<right>") # Move past filename part
    keyboard.send_keys("<ctrl>+<right>") # Move past :linenumber part

# Select from current cursor position back to the beginning of the line
# This should select "Filename:LineNumber"
keyboard.send_keys("<ctrl><shift>+<home>")
keyboard.send_keys("<ctrl>+c") # Copy the selection

# Activate VS Code (ensure 'Code' is the correct identifier for your VS Code window)
window.activate('Code') # User note: 'code.Code' with matchClass=True might be more robust

keyboard.send_keys("<ctrl>+p") # Open VS Code's "Go to File..." palette
keyboard.send_keys("<ctrl>+v") # Paste the "Filename:LineNumber"
time.sleep(.1) # Wait for paste and palette to update
keyboard.send_keys("<enter>") # Navigate
```

## Setup in AutoKey

1.  **Create/Edit Script:**
    *   Open AutoKey.
    *   Create a new script or edit an existing one.
    *   Paste the Python code above.
2.  **Set Hotkey:**
    *   Click "Set" next to "Hotkey".
    *   Press your desired hotkey (e.g., `F11` is commonly used for this type of utility).
    *   Click "OK".
3.  **Set Window Filter (Crucial):**
    *   Click "Set" next to "Window Filter".
    *   Enter a pipe-separated list of window classes for *all* applications from which you want to trigger this script. Based on the script and common usage:
        `code.Code|kate.Kate`
    *   Use AutoKey's "Detect Window Properties" tool (usually found under the "Tools" menu in AutoKey) to confirm the correct window class names for your applications (e.g., for VS Code, it's often `code.Code`; for Konsole, `konsole.Konsole`; for Kate, `kate.Kate`).
    *   Click "OK".

## Usage Workflow

1.  **In your source application (Kate, VS Code terminal, etc.):**
    *   Ensure the application window is active.
    *   Place your cursor anywhere on the line that contains the `filename:linenumber` reference you want to jump to.
    *   Example lines this works with:
        *   `PregContentFinder:63[__construct()]INFO: greetings from PregContentFinder :) ...`
        *   `PregContentFinder:195[setSearchMode()]INFO: Search mode changed. ...`
        *   `F2T2_Test:105[run()]INFO: from tearDown:65 Test Method FINISH ---`
        *   `F2T2_Test:32[test_F2T2c_WriteLog_File1Exists()]INFO: Hey from test_F2T2c_WriteLog_File1Exists`
2.  **Press Hotkey:** Press your configured hotkey (e.g., `F11`).
3.  The script will:
    *   Move to the start of the line.
    *   Use editor-specific commands to move the cursor just past the `Filename:LineNumber` part.
    *   Select text from that point back to the start of the line (capturing `Filename:LineNumber`).
    *   Copy the selection.
    *   Switch to (or ensure focus on) VS Code.
    *   Open the "Go to File..." palette (`Ctrl+P`), paste the copied string, and press Enter.
4.  VS Code should then open the correct file and navigate to the specified line.

## Notes & Considerations

*   **Hotkey in Script Comment:** The script has a comment `# recomandet hotkey ctrl+v`. This might be a note for an alternative trigger or a leftover. The setup typically uses a dedicated hotkey like `F11`.
*   **Window Activation for VS Code:** The line `window.activate('Code')` activates VS Code. Depending on your system and whether you have multiple VS Code windows or use Insiders, you might find `window.activate('code.Code', matchClass=True)` more reliable. Test what works best for your setup.
*   **Kate Specific Logic:** The Kate automation (`<ctrl>+f:`, `<escape>`, `<ctrl>+<right>`) is specific. If Kate's behavior changes or if the line number part is complex, this might need adjustment. The commented-out `time.sleep(.2)` and `exit(0)` from your original script were specific to how you were developing/testing it for Kate.
*   **Generic Editor Logic:** The `else` block with two `<ctrl>+<right>` presses is for other editors. Its success depends on how those editors define "word" boundaries for `Ctrl+Right` navigation. It generally works well for simple `Filename:LineNumber` formats.
*   **No Error Handling for Selection:** The script assumes the selection process yields a usable `Filename:LineNumber` string. If the selection is incorrect, the `Ctrl+P` navigation in VS Code might fail or go to the wrong place.
