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

### Windows systems

On Windows, this is one way to install:

```bash
# Assuming PHP is installed in C:\php
# install pear:
# download http://pear.php.net/go-pear.phar and place it in C:\php
$ cd c:\php
$ .\php.exe go-pear.phar
# install php code sniffer
$ pear install PHP_CodeSniffer
# clone the ezcs repository (via Git GUI, Git Shell, PowerShell, etc)
# copy path\to\ezcs\php\ezcs to C:\php\pear\PHP\CodeSniffer\Standards
```

## Usage

Use phpcs' `--standard` option to use it, example:

```bash
$ phpcs --standard=ezcs eZ/Publish/Core/Repository/Repository.php
```
