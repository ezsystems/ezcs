# eZ JSHint configuration

This is the [JSHint](http://www.jshint.com/) configuration to be used to enforce
our JavaScript coding standard.

## Usage

After [installing JSHint](http://www.jshint.com/install/), just run `jshint`
like:

    $ jshint --verbose --config path/to/jshint.json file1.js file2.js

It's also possible to setup this configuration as the default one for JSHint. To
do this, either copy or symlink jshint.json to `~/.jshintrc`. In such case, the
previous command line can be simplifed to:

    $ jshint --verbose file1.js file2.js
