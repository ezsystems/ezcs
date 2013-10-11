# eZ v2 PHPCodeSniffer standard

This is the PHPCodeSniffer standard to be used to enforce the [updated coding
standards](https://github.com/ezsystems/ezpublish-kernel/wiki/codingstandards).

## Installation

The `ezcs` directory must be placed inside PHPCodeSniffer's `Standards`
directory aside the built-in one like:

* PEAR
* PHPCS
* Squiz
* Zend

### Debian based systems

On Debian/Ubuntu systems, the easiest way to install this standard and its
requirements is to run the following commands as root:

```bash
# install pear
$ apt-get install php-pear
# install php code sniffer
$ pear install --alldeps PHP_CodeSniffer
# clone the ezcs repository
$ git clone https://github.com/ezsystems/ezcs.git
# install ezcs as a standard for phpcs
$ ln -s path/to/ezcs/php/ezcs /usr/share/php/PHP/CodeSniffer/Standards/ezcs
```

## Usage

Use phpcs' `--standard` option to use it, example:

```bash
$ phpcs --standard=ezcs eZ/Publish/Core/Repository/Repository.php
```
