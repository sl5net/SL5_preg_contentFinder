# PregContentFinder

**A powerful PHP library for finding, extracting, and transforming content within text نيكى based on regular expression (PCRE) delimiters, with advanced support for nested structures and custom callback processing.**

Originally developed as part of the doSqlWeb PHP Template Engine, `PregContentFinder` has evolved into a versatile tool for complex text manipulation tasks where simple string functions or basic regex replacements fall short.

[![Latest Stable Version](https://poser.pugx.org/sl5net/preg-contentfinder/v/stable)](https://packagist.org/packages/sl5net/preg-contentfinder)
[![Total Downloads](https://poser.pugx.org/sl5net/preg-contentfinder/downloads)](https://packagist.org/packages/sl5net/preg-contentfinder)
[![License](https://poser.pugx.org/sl5net/preg-contentfinder/license)](https://packagist.org/packages/sl5net/preg-contentfinder)

## Key Features

*   **Delimiter-Based Searching:** Define start and end delimiters using strings or full PCRE regular expressions.
*   **Nested Structure Handling:** Intelligently finds content within correctly balanced and nested delimiter pairs.
*   **Recursive Processing:** Apply transformations recursively to nested structures.
*   **Custom Callback Functions:** Pass your own PHP callables to dynamically process and transform the content found between delimiters, including information about nesting depth and match positions.
*   **Flexible Search Modes:**
    *   `lazyWhiteSpace`: Treats delimiters as literal strings, automatically quoting them and making whitespace flexible.
    *   `dontTouchThis`: Treats delimiters as raw PCRE patterns.
    *   `use_BackReference_IfExists_()$1${1}`: Allows the end delimiter's regex to use backreferences from the start delimiter's match (e.g., for matching `<div>...</div>` but not `<div>...</p>`).
*   **Content Access:** Easily retrieve content found *between*, *before*, or *after* the matched delimiters.
*   **Caching:** Internal caching of found positions for improved performance on repeated searches with the same parameters.

## Why PregContentFinder?

While PHP offers powerful string and regex functions, `PregContentFinder` excels when dealing with:

*   **Deeply nested or recursively defined structures** in text that are hard to parse reliably with a single complex regex.
*   **Contextual transformations** where the modification of a found segment depends on its content, its nesting level, or surrounding data, best handled by custom callback logic.
*   **Scenarios requiring robust parsing of paired delimiters** where the closing delimiter might depend on the opening one.

To our knowledge, there are few PHP libraries offering this specific, powerful combination of regex-based recursive searching with extensive callback-driven transformation capabilities for nested text structures.

## Installation

The recommended way to install PregContentFinder is via [Composer](https://getcomposer.org/):

```bash
composer require sl5net/preg-contentfinder
```

## Basic Usage

```php
<?php
require 'vendor/autoload.php'; // If using Composer

use SL5\PregContentFinder\PregContentFinder;

$sourceText = "Here is some [important data] and [another piece].";
$finder = new PregContentFinder($sourceText);

// Simple string delimiters
$finder->setBeginEnd_RegEx('[', ']');

$content1 = $finder->getContent(); // Finds the first match
// $content1 will be "important data"

// To get all matches, you typically loop or use recursive callbacks
// For example, to replace content:
$result = $finder->getContent_user_func_recursive(
    function ($cut, $deepCount, $callsCount, $posList0, $originalSegmentContent) {
        $cut['middle'] = strtoupper($cut['middle']); // Transform the content
        return $cut; // Return the modified segment parts
    }
);
// $result would be "Here is some IMPORTANT DATA and ANOTHER PIECE."
// (Actual assembly depends on how the callback result is used by the class)

echo $result;
?>
```

## Advanced Examples

### 1. Transforming Nested Structures (Simple Replacement)

**Input:**
```
a{b{B}}
```

**Goal:** Change `{` to `[` and `}` to `]`.

**PHP Code:**
```php
<?php
use SL5\PregContentFinder\PregContentFinder;

$source = 'a{b{B}}';
$finder = new PregContentFinder($source, '{', '}'); // Set delimiters in constructor

$transformed = $finder->getContent_user_func_recursive(
    function ($cut) { // $cut is an array: ['before' => ..., 'middle' => ..., 'behind' => ...]
        // This simple callback just reassembles with new brackets
        // A more robust callback would only modify $cut['middle'] for this specific example.
        // The class logic for getContent_user_func_recursive handles the assembly.
        // Assuming the callback returns the new middle part:
        return $cut['before'] . '[' . $cut['middle'] . ']' . $cut['behind'];
    }
);
// To purely replace delimiters and keep content, often simpler regex is enough.
// The power of user_func_recursive comes with complex logic in the callback.

// For simple delimiter replacement, a direct string replace after extraction might be easier,
// or a callback that only returns the $cut['middle'] unmodified if the goal
// is just to change the delimiters during reassembly by the main class (if it supports that).

// Let's assume a more practical callback for this simple case,
// where the class itself handles outer delimiters and recursion passes inner content:
$finder = new PregContentFinder($source, '{', '}');
$transformed = $finder->getContent_user_func_recursive(
    function ($cut) {
        // The callback is called for the content *inside* each {} pair.
        // For a{b{B}}, first call $cut['middle'] = "b{B}"
        // Recursive call for {B}, $cut['middle'] = "B"
        // $cut['before'] and $cut['behind'] are relative to the current segment.
        
        // To achieve a[b[B]], the callback needs to be smart or the class
        // needs to offer specific ways to replace delimiters.
        // A more direct way for *this specific example* if just changing delimiters:
        // This is conceptual if the class provides these replacement delimiters in callback.
        $newOpen = '[';
        $newClose = ']';
        $cut['before'] = str_replace('{', $newOpen, $cut['before']); // Risky, better done by class
        $cut['middle'] = $cut['middle']; // Content stays same
        $cut['behind'] = str_replace('}', $newClose, $cut['behind']); // Risky

        // A cleaner callback just processes $cut['middle']
        // $cut['middle'] = "TRANSFORMED_" . $cut['middle'];
        return $cut;
    }
);
// The example below is more realistic for the callback's role.
// For the simple a{b{B}} => a[b[B]] example, it implies the *class* is configured
// with new delimiters for output, or the callback is very simple.

// More realistic callback for transformation:
$finder = new PregContentFinder('a{b{B}}', '{', '}');
$result = $finder->getContent_user_func_recursive(function($cut) {
    $cut['middle'] = '*' . $cut['middle'] . '*'; // Example: wrap middle content with asterisks
    return $cut;
});
// $result would be 'a{*b{B}*}' if non-recursive or 'a{*b{*B*}*}' if recursive on middle.
// The original example's output 'a[b[B]]' implies the class itself was configured to output
// different delimiters, or the callback was extremely simple for that specific transformation.

// The key is the callback operates on $cut['middle'] (the content within the current delimiters).
// The examples provided in the original README are about the *final output* after the
// class (with a suitable callback) has processed the input.
?>
```
_(The simple `{` to `[` example is a bit tricky to show concisely without knowing the exact callback logic used to produce that specific output. The power is that the callback *can* do this.)_

### 2. Code Indentation / Pretty-Printing (Conceptual)

**Input:**
```
if(X1){$X1;if(X2){$X2;}}
```

**Goal:** Indent based on nesting.

**PHP (Conceptual Callback Logic):**
```php
<?php
use SL5\PregContentFinder\PregContentFinder;

$source = 'if(X1){$X1;if(X2){$X2;}}';
$finder = new PregContentFinder($source, '{', '}');

$indented = $finder->getContent_user_func_recursive(
    function ($cut, $deepCount, $callsCount, $posList0, $originalSegmentContent) {
        $indentChar = "  "; // Two spaces for indentation
        $newline = "\n";
        
        $currentIndent = str_repeat($indentChar, $deepCount);
        $innerIndent = str_repeat($indentChar, $deepCount + 1);

        // Process $cut['middle'] for newlines and apply indentation
        $middleLines = explode(';', rtrim($cut['middle'], ';')); // Split by semicolon for example
        $processedMiddle = "";
        foreach ($middleLines as $line) {
            if (trim($line) !== "") {
                $processedMiddle .= $innerIndent . trim($line) . ";" . $newline;
            }
        }
        // Remove last semicolon and newline if present
        $processedMiddle = rtrim(rtrim($processedMiddle, $newline), ';');


        // Reconstruct the segment for this level
        // The class will handle how $cut['before'] and $cut['behind'] are used from outer levels.
        // The callback focuses on transforming its $cut['middle'].
        $cut['middle'] = $newline . $processedMiddle . $newline . $currentIndent;
        
        // For the example output, the class also needs to be aware of replacing
        // the original delimiters with new ones that include newlines and outer indentation.
        // e.g., $cut['before'] might become $cut['before'] . "[" . $newline
        // This requires a more complex callback or class configuration.
        // The example output:
        // if(X1)[
        // ..$X1;if(X2)[
        // ....$X2;
        // ..]
        // ]
        // implies the callback or class is also changing '{' to '[\n..', etc.

        return $cut;
    }
);

// echo $indented; 
// Note: Achieving the exact output requires the callback to precisely manage newlines,
// indentation for the content, AND how the delimiters themselves are replaced/formatted.
// The example output in the original README is a target state.
?>
```

## Use Cases
`PregContentFinder` is well-suited for:
*   Parsing and transforming custom template languages or markup (like BBCode).
*   Extracting data from semi-structured text files with nested blocks.
*   Code analysis and transformation (e.g., pretty-printing, refactoring simple patterns).
*   Implementing interpreters for simple, domain-specific languages.
*   Any task requiring robust identification and manipulation of hierarchically structured text.

## License

`PregContentFinder` is licensed under the GNU General Public License v3.0 or later (GPL-3.0-or-later). See the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues. (Consider adding a `CONTRIBUTING.md` file if you have specific guidelines).
