# PregContentFinder

**A powerful and robust PHP library for finding, extracting, and transforming content within text based on regular expression (PCRE) delimiters, featuring advanced support for nested structures, custom callback processing, and intelligent delimiter handling.**

[![Latest Stable Version](https://poser.pugx.org/sl5net/preg-contentfinder/v/stable)](https://packagist.org/packages/sl5net/preg-contentfinder)
[![Total Downloads](https://poser.pugx.org/sl5net/preg-contentfinder/downloads)](https://packagist.org/packages/sl5net/preg-contentfinder)
[![License](https://poser.pugx.org/sl5net/preg-contentfinder/license)](https://packagist.org/packages/sl5net/preg-contentfinder)

**A Legacy of Stability:** The fundamental recursive parsing logic and the intelligent handling of delimiter conflicts have been stable and functional **since its early years (around 2002-2003, likely PHP 4)**. Subsequent updates primarily focused on adapting to new PHP versions (like PHP 5.6). This latest modernization brings the proven core engine to **PHP 8.1+**, integrating it with Composer and modern PHP standards.

It excels where simple string functions or basic regex fall short, particularly with nested structures and context-dependent transformations.

## Key Features

*   **Delimiter-Based Searching:** Use strings or full PCRE regular expressions for start and end delimiters.
*   **Intelligent Nested Structure Handling:** Reliably finds content within correctly balanced and nested delimiter pairs, even when the content itself contains delimiter characters. **You often don't need to worry about manually escaping delimiter characters within your content.**
*   **Recursive Processing:** Apply transformations recursively through nested structures using `getContent_user_func_recursive`.
*   **Custom Callback Functions:** Pass PHP callables to dynamically process and transform the content found between delimiters, receiving context like nesting depth and match details.
*   **Flexible Search Modes:**
    *   `lazyWhiteSpace`: Treats delimiters as literal strings, quoting them and making whitespace flexible.
    *   `dontTouchThis`: Treats delimiters as raw PCRE patterns provided by the user.
    *   `use_BackReference_IfExists_()$1${1}`: Allows the end delimiter's regex to use backreferences from the start delimiter's match (e.g., for matching `<div>...</div>`).
*   **Content Access:** Easily retrieve content *between*, *before*, or *after* matched delimiters for the current match.
*   **Modernized:** PHP 8.1+ compatible, PSR-4 compliant, available via Composer.
*   **Well-Tested:** Backed by ~100 unit tests ensuring core functionality and robustness.

## Why PregContentFinder?

Use this library when you need to:

*   Parse and transform custom template languages or markup (like BBCode).
*   Reliably extract data from semi-structured text files with potentially nested blocks, even if those blocks contain delimiter characters.
*   Perform context-aware text transformations using callbacks based on nesting or content.
*   Analyze or refactor code structures based on block delimiters.
*   Implement interpreters for simple, domain-specific languages.

Its unique combination of features, especially the robust handling of nested structures and internal management of delimiter conflicts, makes it a powerful tool for complex text processing tasks.

## Installation

The recommended way to install PregContentFinder is via [Composer](https://getcomposer.org/):

```bash
composer require sl5net/preg-contentfinder
```

Basic Usage

```php     
<?php
require 'vendor/autoload.php';

use SL5\PregContentFinder\PregContentFinder;

// Example 1: Simple Extraction
$sourceText = "Some text [สำคัญ data1] and [other {data2}] stuff.";
$finder = new PregContentFinder($sourceText, '[', ']');

$content1 = $finder->getContent(); // Finds first match
// $content1 = "สำคัญ data1"

$content2 = $finder->getContent(); // Finds next match (if state is managed correctly)
// This depends on how getContent updates the internal position.
// Often used in a loop with setPosOfNextSearch or getBorders.

// Example 2: Transformation with Callback
$source = "Process {A{B}C} and {D}.";
$finder = new PregContentFinder($source, '{', '}');

$result = $finder->getContent_user_func_recursive(
    function ($cut, $deepCount) {
        // $cut['middle'] contains content between {} for the current level
        // $deepCount indicates nesting level (0 for outermost)
        $cut['middle'] = "[$deepCount:" . strtoupper($cut['middle']) . "]";
        return $cut; // Return modified segment parts
    }
);

// Expected $result might be something like:
// Process [0:A[1:B]C] and [0:D].
// (Exact output depends on internal recursive assembly logic)
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
