.. title:: PHP DotArray Library:: Sail through array using the DOT notation

====================
DotArray
====================

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
