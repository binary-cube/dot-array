.. title:: PHP DotArray Library:: Sail through array using the DOT notation

====================
DotArray
====================

.. raw:: html

    <a href="https://github.com/binary-cube/dot-array" target="_blank" style="display: inline-block; position: relative; border: none">
        <img src="https://img.shields.io/badge/source-GitHub-8892BF.svg?style=flat-square"/>
    </a>

    <a href="https://php.net" target="_blank" style="display: inline-block; position: relative; border: none">
        <img src="https://img.shields.io/badge/php-%3E%3D%207.1-8892BF.svg?style=flat-square"/>
    </a>

    <a href="https://packagist.org/packages/binary-cube/dot-array" target="_blank" style="display: inline-block; position: relative; border: none">
        <img src="https://img.shields.io/packagist/v/binary-cube/dot-array.svg?style=flat-square"/>
    </a>

    <a href="https://packagist.org/packages/binary-cube/dot-array" target="_blank" style="display: inline-block; position: relative; border: none">
        <img src="https://img.shields.io/packagist/dt/binary-cube/dot-array.svg?style=flat-square"/>
    </a>

    <a href="https://travis-ci.org/binary-cube/dot-array" target="_blank" style="display: inline-block; position: relative; border: none">
        <img src="https://img.shields.io/travis/binary-cube/dot-array/master.svg?style=flat-square"/>
    </a>

    <a href="https://scrutinizer-ci.com/g/binary-cube/dot-array/code-structure" target="_blank" style="display: inline-block; position: relative; border: none">
        <img src="https://img.shields.io/scrutinizer/coverage/g/binary-cube/dot-array.svg?style=flat-square"/>
    </a>

    <a href="https://scrutinizer-ci.com/g/binary-cube/dot-array" target="_blank" style="display: inline-block; position: relative; border: none">
        <img src="https://img.shields.io/scrutinizer/g/binary-cube/dot-array.svg?style=flat-square"/>
    </a>

    <a href="https://github.com/binary-cube/dot-array/blob/master/LICENSE" target="_blank" style="display: inline-block; position: relative; border: none">
        <img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square"/>
    </a>

    <br/><br/>

DotArray is a simple PHP Library that can access an array in a dotted manner.

- Easy to use.
- Support to access complex keys that are having dot in the name.
- Fluent access.
- Possibility to search & filter items.
- Dot access can, also, works in the "old school" array way (Vanilla PHP).

.. code-block:: php

    DotArray::create(['config' => ['some.dotted.key' => 'value']])->get('config.{some.dotted.key}')

User Guide
==========

.. toctree::
    :maxdepth: 2

    overview
    quickstart
