SL5_preg_contentFinder
======================

a PHP Template Engine class using Perl Compatible Regular Expressions (PCRE)

See complete examples inside example folder or inside the tests folder. :)

<pre>
Example source conversion :

a{b{B}}

==>

a[b[B]]

______________________

Example source conversion :

if(X1){$X1;if(X2){$X2;}}

==>

if(X1)[
..$X1;if(X2)[
....$X2;
..]
]

HowTo config conversion for this last example:

$old_open = '{';
$old_close = '}';
$new_open_default = '[';
$new_close_default = ']';
$charSpace = ".";
$newline = "\r\n";
$indentSize = 2;

now simply start conversion. BTW optional you could use regular expressions and much more. or you could use your own callback function. enjoy :) 

______________________

Example source conversion :

a{b{B}}

==>

a
1|[
1:..b
2|..[
2:....B
2:..]
1:]
______________________


