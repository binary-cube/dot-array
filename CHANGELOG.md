DotArray Change Log 
=====================

1.1.0 March 02, 2019
-----------------------------
- Remove DotArray::uniqueIdentifier 
- Remove DotPathTrait - parts have been moved into DotArray 
- Code Standard Improvements
- Refactoring DotArray:
- More Tests. 

1.0.5 December 30, 2018
-----------------------------

- Refactoring DotArray:
    - Using a Trait (DotFilteringTrait) to split code in more organized units.
- Refactoring DotPathTrait::flatten
- using PHPStan.
- Updating composer.json scripts to use PHPStan.
- More Tests. 

1.0.4 December 30, 2018
-----------------------------

- Refactoring DotArray:
    - Using a Trait (DotPathTrait) to split code in more organized units.
    - Refactor DotArray::mergeRecursive :: less `if ... else` branches.
    - Refactor DotArray::normalize :: now is recursive and if type of the entry is DotArray then is converted to array.
    - Apply DotArray::normalize after every DotArray::write used when DotArray::set is called.
- Fix composer.json `create-folders` script :: in case of fail creating the `build` folder, exit with code 0.
- Updating README.md
- Updating Tests 

1.0.3 December 28, 2018
-----------------------------

- Update `.gitattributes`

1.0.2 December 28, 2018
-----------------------------

- Update README.md
- Added the following scripts to `composer.json`:
    - `composer check` (running phpcs & phpunit)
    - `composer generate-reports` (running phpcs, phpmd, phpunit :: for generating internal reports)

1.0.1 December 26, 2018
-----------------------------

- More Tests
- Fix DotArray::__invoke @return comment

1.0.0 December 26, 2018
-----------------------------

- Initial release.
