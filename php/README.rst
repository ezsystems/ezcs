=============================
eZ v2 PHPCodeSniffer standard
=============================

This is the PHPCodeSniffer standard to be used to enforce the updated_ coding standards.

Installation
============

This standard must be placed as "ezcs" inside PHPCodeSniffer's "Standards" directory aside the built-in one like:

* PEAR
* PHPCS
* Squiz
* Zend

Usage
=====

Use phpcs' ``--standard`` option to use it::

    $ phpcs --standard=ezcs eZ/Publish/Core/FieldType/BinaryBase/PathGenerator.php



.. _updated: https://github.com/ezsystems/ezpublish-kernel/wiki/codingstandards
