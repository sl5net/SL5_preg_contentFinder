## PHPUnit executed *inside the Container*

This document outlines how to set up and use VS Code with Dev Containers to run PHPUnit tests within the Docker environment managed by the `pcf` script.

# English

1.  The `devcontainer.json` uses the `"image": "..."` directive to utilize an image already built by the `pcf b` script.
    This ensures that VS Code uses the same image that your script uses for command-line tests.

2.  **Output from PHPUnit (Example):**
    *   `/usr/local/bin/php /app/vendor/bin/phpunit --configuration=/app/phpunit.xml /app/tests/PHPUnit/DontTouchThisSearchModeSimplifiedTest.php --colors=never --teamcity`
        *   This is the command that the VS Code PHPUnit extension (here, `recca0120.vscode-phpunit`, indicated by `--teamcity`) now executes *inside the container*.
        *   Paths like `/app/vendor/...` and `/app/phpunit.xml` are correct for the container context.
        *   `--colors=never` and `--teamcity` are typical options used by test runner extensions to make the output machine-readable.

3.  **Working with VS Code:**
    *   Standard workflow for "Dev Containers":
        1.  Open the **project folder** (`SL5_preg_contentFinder`) in VS Code.
        2.  VS Code detects the `.devcontainer/devcontainer.json` file.
        3.  A notification should appear in the bottom-right corner: "Folder contains a Dev Container configuration file. Reopen in Container?" Alternatively, use the Command Palette (`Ctrl+Shift+P` or `Cmd+Shift+P`) and select "Dev Containers: Reopen in Container".
    *   If you right-click on the `app` folder *within* the "Remote Explorer" view, you might only open that subfolder of the already running container in a new window. This is not quite the same as opening the entire project in the container context.
    *   **Ensure you open the entire `SL5_preg_contentFinder` project in the container.**

**Summary:**

*   **PHPUnit runs inside the container via VS Code!**
*   This uses the image built by `pcf b` and the `remoteUser: "appuser"` setting in `devcontainer.json`.
*   Failed tests (`❌`) can now be addressed and re-tested (e.g., using the VS Code Test Explorer).

**Next Steps and Considerations:**

1.  **Debug Failed Tests:**
    Focus on fixing the tests that are currently failing. Use the Test Explorer in VS Code to see error messages and (if Xdebug is configured) use VS Code's debugging capabilities.

2.  **Workflow: "Open Project in Container":**
    *   Start VS Code, open the `SL5_preg_contentFinder` project folder.
    *   Look for the "Dev Containers: Reopen in Container" prompt in the bottom-right, or use the "Remote Explorer" sidebar and/or the "Reopen in Container" command from the Command Palette.

3.  **Synchronization between `devcontainer.json` and `pcf b`:**
    *   The `pcf b` script modifies `devcontainer.json`, setting the `"image"` field to the newly built image.
    *   **Important:** After `pcf b` has run and updated `devcontainer.json`, you **must** execute **"Dev Containers: Rebuild and Reopen in Container"** (or "Dev Containers: Rebuild Container" if it was already open) in VS Code. This ensures VS Code uses the updated image specification.

