#Garlic.js

[![Build Status](https://secure.travis-ci.org/guillaumepotier/Garlic.js.png?branch=master)](https://travis-ci.org/guillaumepotier/Garlic.js)

Garlic.js allows you to automatically persist your forms' text and select field values locally, until the form is submitted. This way, your users don't lose any precious data if they accidentally close their tab or browser.

#Demonstration / Documentation

http://garlicjs.org/

#Version

1.2.3

See CHANGELOG for more info.

#TODO

* Improve doc and api;
* Refactorize some code;
* Work on inputs radio and textarea where there are conflicts;
* And much more, for fun!

#Run tests

* In your browser: go to `tests/index.html`
* Headless tests: install mocha-phantomjs with npm: `npm install -g mocha-phantomjs` and then run `./bin/test-suite.sh`

#Make production minified versions

You'll need ruby, and Google Closure compiler: `gem install closure-compiler`. Then, just call:

`./bin/build.sh version` where version is the build release. eg: `./bin/build.sh 1.1.2`

They'll be created and dumped in the dist/ directory

#Contributors

* @cdmoyer
* @johnrees
* @Marfa
* @leondewey
* @willdurand
* @nashby

#Contribute!

* fork repository
* add your changes to garlic.js
* add / update tests to test suite (tests/index.html / tests/tests.js)
* run tests (see above)
* create new minified versions with minify script (see above)
* make a Pull Request!

#Used / Inspiration

* localStorageshim for IE browsers: https://github.com/mattpowell/localstorageshim by @mattpowell
* minify ruby script https://gist.github.com/765432 by @benpickles

#Licence

MIT - See LICENCE.md
