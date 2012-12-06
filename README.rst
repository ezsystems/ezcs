=============================
eZ v2 PHPCodeSniffer standard
=============================

This is the PHPCodeSniffer standard to be used to enforce the updated coding standards.

Installation
============

This standard must be placed inside PHPCodeSniffer's "Standards" directory aside the built-in one like:

* PEAR
* PHPCS
* Squiz
* Zend

Usage
=====

Use phpcs' ``--standard`` option to use it::

    $ phpcs --standard=ezcs eZ/Publish/Core/FieldType/BinaryBase/PathGenerator.php
