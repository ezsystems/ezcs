# eZ CSSLint configuration

This is the [CSSLint](https://github.com/stubbornella/csslint) configuration to
be used to run automatic tests on our CSS.

## Usage

`csslint` looks for a file named `.csslintrc` in the current working directory.
So the easiest way to apply the configuration provided in this repository is to
create a symlink in the correct place.

    $ ln -s path/to/csslintrc .csslintrc
    $ csslint --format=compact directory/to/css/files/

