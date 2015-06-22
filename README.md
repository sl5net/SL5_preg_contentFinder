SL5_preg_contentFinder
======================

a PHP Template Engine class using Perl Compatible Regular Expressions (PCRE)

See complete examples inside example folder or inside the tests folder. :)


Example source conversion :

:a{b{B}}

==>

:a<b<B>>

______________________

Example source conversion :

if(X1){$X1;if(X2){$X2;}}

==>

if(X1)[
..$X1;if(X2)[
....$X2;
..]
]

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


