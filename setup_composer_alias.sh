#!/bin/bash

# --- Configuration ---
DEFAULT_COMPOSER_PHAR_PATH="" # Leave empty to force prompt, or set a default like "./composer.phar"

# --- Helper Functions ---
get_absolute_path() {
  local target_path="$1"
  if [[ -z "$target_path" ]]; then
    echo ""
    return 1
  fi
  # Try realpath first (more robust)
  if command -v realpath >/dev/null 2>&1; then
    realpath "$target_path" 2>/dev/null || echo ""
  # Fallback to a more manual approach if realpath isn't available or fails
  elif [[ "$target_path" == /* ]]; then # Already absolute
    echo "$target_path"
  else # Relative path
    echo "$(cd "$(dirname "$target_path")" && pwd)/$(basename "$target_path")"
  fi
}

prompt_for_composer_path() {
  local composer_path=""
  while true; do
    read -r -p "Enter the full path to your composer.phar file: " composer_path
    if [[ -z "$composer_path" ]]; then
      echo "Path cannot be empty."
      continue
    fi
    composer_path=$(get_absolute_path "$composer_path")
    if [[ -f "$composer_path" && -r "$composer_path" ]]; then
      COMPOSER_PHAR_PATH="$composer_path"
      echo "Using composer.phar at: $COMPOSER_PHAR_PATH"
      break
    else
      echo "File not found or not readable at '$composer_path'. Please try again."
      # Offer to use current directory if composer.phar exists there
      if [[ -f "./composer.phar" ]]; then
        read -r -p "Found composer.phar in current directory. Use it? (y/N): " use_current
        if [[ "$use_current" =~ ^[Yy]$ ]]; then
          COMPOSER_PHAR_PATH=$(get_absolute_path "./composer.phar")
          echo "Using composer.phar at: $COMPOSER_PHAR_PATH"
          break
        fi
      fi
    fi
  done
}

add_alias_if_not_exists() {
  local file_path="$1"
  local alias_string="$2"
  local grep_pattern="$3" # A unique part of the alias to check for existence

  if [ ! -f "$file_path" ]; then
    echo "Creating $file_path..."
    touch "$file_path"
  fi

  if grep -qF "$grep_pattern" "$file_path"; then
    echo "Composer alias/function already seems to exist in $file_path."
  else
    echo "" >> "$file_path" # Add a newline for separation
    echo "$alias_string" >> "$file_path"
    echo "Added composer alias/function to $file_path."
  fi
}

# --- Main Script ---

echo "Composer Alias Setup Script"
echo "---------------------------"

# Determine composer.phar path
if [[ -n "$1" && -f "$1" ]]; then
  COMPOSER_PHAR_PATH=$(get_absolute_path "$1")
  echo "Using composer.phar path from argument: $COMPOSER_PHAR_PATH"
elif [[ -n "$DEFAULT_COMPOSER_PHAR_PATH" && -f "$DEFAULT_COMPOSER_PHAR_PATH" ]]; then
  COMPOSER_PHAR_PATH=$(get_absolute_path "$DEFAULT_COMPOSER_PHAR_PATH")
  echo "Using default composer.phar path: $COMPOSER_PHAR_PATH"
else
  prompt_for_composer_path
fi

if [[ -z "$COMPOSER_PHAR_PATH" ]]; then
  echo "Error: Could not determine a valid path for composer.phar. Exiting."
  exit 1
fi

# Bash
BASHRC_FILE="$HOME/.bashrc"
BASH_ALIAS_CMD="php \"$COMPOSER_PHAR_PATH\" \"\$@\"" # Escape $ for literal output
BASH_ALIAS="alias composer='$BASH_ALIAS_CMD'"
BASH_GREP_PATTERN="alias composer='php \"$COMPOSER_PHAR_PATH\""
echo -e "\nSetting up for Bash..."
add_alias_if_not_exists "$BASHRC_FILE" "$BASH_ALIAS" "$BASH_GREP_PATTERN"

# Zsh
ZSHRC_FILE="$HOME/.zshrc"
ZSH_ALIAS_CMD="php \"$COMPOSER_PHAR_PATH\" \"\$@\"" # Escape $ for literal output
ZSH_ALIAS="alias composer='$ZSH_ALIAS_CMD'"
ZSH_GREP_PATTERN="alias composer='php \"$COMPOSER_PHAR_PATH\""
echo -e "\nSetting up for Zsh..."
add_alias_if_not_exists "$ZSHRC_FILE" "$ZSH_ALIAS" "$ZSH_GREP_PATTERN"

# Fish
FISH_CONFIG_DIR="$HOME/.config/fish"
FISH_FUNCTIONS_DIR="$FISH_CONFIG_DIR/functions"
FISH_COMPOSER_FUNC_FILE="$FISH_FUNCTIONS_DIR/composer.fish"
FISH_FUNCTION_CONTENT="function composer\n  php \"$COMPOSER_PHAR_PATH\" \$argv\nend"
FISH_GREP_PATTERN="php \"$COMPOSER_PHAR_PATH\" \$argv" # Check for the core command line

echo -e "\nSetting up for Fish..."
mkdir -p "$FISH_FUNCTIONS_DIR" # Ensure functions directory exists
if [ -f "$FISH_COMPOSER_FUNC_FILE" ] && grep -qF "$FISH_GREP_PATTERN" "$FISH_COMPOSER_FUNC_FILE"; then
  echo "Composer function already seems to exist in $FISH_COMPOSER_FUNC_FILE."
else
  echo -e "$FISH_FUNCTION_CONTENT" > "$FISH_COMPOSER_FUNC_FILE"
  echo "Created/Updated Fish function in $FISH_COMPOSER_FUNC_FILE."
fi

echo -e "\n---------------------------"
echo "Setup complete!"
echo "You may need to source your shell configuration files or open a new terminal for changes to take effect:"
echo "  For Bash: source ~/.bashrc"
echo "  For Zsh:  source ~/.zshrc"
echo "  For Fish: Restart your Fish shell, or if already running, type 'composer' (it should autoload)."
echo ""
echo "Remember: If you move '$COMPOSER_PHAR_PATH', these aliases/functions will break!"
echo "Consider moving composer.phar to a directory in your PATH and renaming it to 'composer' for a more robust solution."

exit 0
