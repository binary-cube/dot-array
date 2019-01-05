# DotArray - Sail through array using the dot notation


<p align="center">~ Enjoy your :coffee: ~</p>

Accessing PHP Arrays via DOT notation is easy as:

```php
DotArray::create(['config' => ['some.dotted.key' => 'value']])->get('config.{some.dotted.key}')
```
[![Minimum PHP Version `PHP >= 7.1`][ico-php-require]][link-php-site]
[![Latest Stable Version][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![Code Coverage][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![License][ico-license]][link-license]

-----




## Installing

- **via "composer require"**:

    ``` shell
    composer require binary-cube/dot-array
    ```

- **via composer (manually)**:

    If you're using Composer to manage dependencies, you can include the following
    in your `composer.json` file:

    ```json
    {
        "require": {
            "binary-cube/dot-array": "1.*"
        }
    }
    ```




## Usage

>##### REMEMBER: YOU NEED TO KNOW YOUR DATA
>##### DotArray::get() can return a new instance of DotArray in case the accessed path is an array or it will return the raw data value or the default given value

- **instantiation**:
    -   ```php
        new DotArray($array);
        DotArray::create($array);
        DotArray::createFromJson($jsonString);
        ```

- **get**:
    -   ```php
        // Because the key `sci-fi & fantasy` is array the returning value it will be a new instance of DotArray.
        $dot('books.{sci-fi & fantasy}');
        
        // Because the price is not an array, the result will be raw data, float in this case.
        $dot('books.{sci-fi & fantasy}.0.price');
        
        // Accessing the raw array.
        $dot('books.{sci-fi & fantasy}')->toArray();
        $dot->get('books.{sci-fi & fantasy}')->toArray();
        
        // Accessing the last leaf and getting the raw data.
        $dot('books.{sci-fi & fantasy}.0.name');
        $dot->get('books.{sci-fi & fantasy}.0.name');

        // Giving a default value in case the requested key is not found.
        $dot->get('key.not.exist', 'not-found-as-string');
        
        // Vanilla PHP.
        $dot('books.{sci-fi & fantasy}.0.name');
        $dot['books']['sci-fi & fantasy'][0]['name'];
        ```

- **get :: more-complex**:
    -   ```php
        // Using dotted key and accessing without getting confused.
        // Allowed tokens for keeping the names with dot(.) togethers are: '', "", [], (), {}
        $dot->get('config.{elastic-search}.\'v5.0\'.host')
        $dot->get('config.{elastic-search}."v5.0".host')
        $dot->get('config.{elastic-search}.[v5.0].host')
        $dot->get('config.{elastic-search}.(v5.0).host')
        $dot->get('config.{elastic-search}.{v5.0}.host')
        ```

- **set**:
    -   ```php
        $dot->set('books.{sci-fi & fantasy}.0.name', 'New Name');
        
        // Vanilla PHP.
        $dot['books.{sci-fi & fantasy}.0.name'] = 'New Name';
        $dot['books']['sci-fi & fantasy'][0]['name'] = 'New Name';
        ```

- **clear** *(empty array <=> [])*:
    -   ```php
        $dot->clear('books.{sci-fi & fantasy}');
        $dot->clear('books.{sci-fi & fantasy}', null);
        $dot->clear('books.{sci-fi & fantasy}.0.name', null);
        
        // Multiple keys.
        $dot->clear([
          'books.{sci-fi & fantasy}',
          'books.{childre\'s books}'
        ]);
        
        // Vanilla PHP.
        $dot['books.{sci-fi & fantasy}'] = [];
        ```

- **merge**:
    >   Merges one or more arrays into master recursively.
        If each array has an element with the same string key value, the latter
        will overwrite the former (different from array_merge_recursive).
        Recursive merging will be conducted if both arrays have an element of array
        type and are having the same key.
        For integer-keyed elements, the elements from the latter array will
        be appended to the former array.

    -   ```php
        // Example 1.
        $dot->merge(['key_1' => ['some_key' => 'some_value']]);

        // Example 2.
        $dot->merge(
            [
                'key_1' => ['some_key' => 'some_value'],
            ], 
            [
                'key_2' => ['some_key' => 'some_value'],
            ],
            [
                'key_n' => ['some_key' => 'some_value']
            ],
        );
        ```

- **delete** *(unset(...))*:
    -   ```php
        $dot->delete('books.{sci-fi & fantasy}');
        $dot->delete('books.{sci-fi & fantasy}.0.name');
        $dot->delete(['books.{sci-fi & fantasy}.0', 'books.{childre\'s books}.0']);
        ```

- **find**:
    -   ```php
        /*
            Find the first item in an array that passes the truth test, otherwise return false
            The signature of the callable must be: `function ($value, $key)`.
        */
        $book = $dot->get('books.{childre\'s books}')->find(function ($value, $key) {
           return $value['price'] > 0;
        });
        ```

- **filter**:
    -   ```php
        /*
            Use a callable function to filter through items.
            The signature of the callable must be: `function ($value, $key)`
        */
        $books = $dot->get('books.{childre\'s books}')->filter(function ($value, $key) {
            return $value['name'] === 'Harry Potter and the Order of the Phoenix';
        });
        
        $books->toArray();
        ```
        
- **filterBy**:
    -   ```php
        /*
            Allowed comparison operators:
                - [ =, ==, eq (equal) ]
                - [ ===, i (identical) ]
                - [ !=, ne (not equal) ]
                - [ !==, ni (not identical) ]
                - [ <, lt (less than) ]
                - [ >, gr (greater than) ]
                - [ <=, lte (less than or equal to) ]
                - [ =>, gte (greater than or equal to) ]
                - [ in, contains ]
                - [ not-in, not-contains ]
                - [ between ]
                - [ not-between ]
        */
        // Example 1.
        $books = $dot->get('books.{childre\'s books}')->filterBy('price', 'between', 5, 12);

        // Example 2.
        $books = $dot->get('books.{childre\'s books}')->filterBy('price', '>', 10);

        // Example 3.
        $books = $dot->get('books.{childre\'s books}')->filterBy('price', 'in', [8.5, 15.49]);
        ```

- **where**:
    -   ```php
        /*
            The signature of the `where` call can be:
                - where([property, comparisonOperator, ...value])
                - where(\Closure) :: The signature of the callable must be: `function ($value, $key)`

            Allowed comparison operators:
                - [ =, ==, eq (equal) ]
                - [ ===, i (identical) ]
                - [ !=, ne (not equal) ]
                - [ !==, ni (not identical) ]
                - [ <, lt (less than) ]
                - [ >, gr (greater than) ]
                - [ <=, lte (less than or equal to) ]
                - [ =>, gte (greater than or equal to) ]
                - [ in, contains ]
                - [ not-in, not-contains ]
                - [ between ]
                - [ not-between ]
        */
        
        // Example 1. (using the signature: [property, comparisonOperator, ...value])
        $books = $dot->get('books.{childre\'s books}')->where(['price', 'between', 5, 12]);

        // Example 2. (using the signature: [property, comparisonOperator, ...value])
        $books = $dot->get('books.{childre\'s books}')->where(['price', '>', 10]);
        
        // Example 3. (using the signature: [property, comparisonOperator, ...value])
        $books = $dot->get('books.{childre\'s books}')->where(['price', 'in', [8.5, 15.49]]);
        
        // Example 4. (using the signature: \Closure)
        $books = $dot->get('books.{childre\'s books}')->where(function ($value, $key) {
            return $value['name'] === 'Harry Potter and the Order of the Phoenix';
        });
        ```

- **toArray**:
    -   ```php
        // Getting the internal raw array.

        // Example 1.
        $dot->toArray();

        // Example 2.
        $dot->get('books.{sci-fi & fantasy}')->toArray();
        ```

- **toJson**:
    -   ```php
        // Getting the internal raw array as JSON.

        // Example 1.
        $dot->toJson();

        // Example 2.
        $dot->get('books.{sci-fi & fantasy}')->toJson();
        ```
        
- **toFlat**:
    -   ```php
        $dot = DotArray::create(
            [
                'a' => [
                    'b' => 'value',
                ],
    
                'b' => [
                    1,
                    2,
                    3,
                    'array' => [
                        1,
                        2,
                        3,
                    ]
                ],
            ]
        );

        $dot->toFlat();

        /*
            The output will be an array:
            [
                '{a}.{b}' => 'value',
                '{b}.{0}' => 1,
                '{b}.{1}' => 2,
                '{b}.{2}' => 3,
                '{b}.{array}.{0}' => 1,
                '{b}.{array}.{1}' => 2,
                '{b}.{array}.{2}' => 3,
            ],
        */
        ```




### Data Sample:

```php
$dummyArray = [
    'books' => [
        'sci-fi & fantasy' => 
            [
                [
                    'name'      => 'Chronicles of Narnia Box Set',
                    'price'     => 24.55,
                    'currency'  => '$',
                    'authors'   => 
                        [
                            [
                                'name' => 'C.S. Lewis'
                            ],
                        ],
                ],
                [
                    'name'      => 'A Game of Thrones / A Clash of Kings / A Storm of Swords / A Feast of Crows / A Dance with Dragons ',
                    'price'     => 37.97,
                    'currency'  => '$',
                    'authors'   => 
                        [
                            [
                                'name' => 'George R. R. Martin'
                            ],
                         ],
                ],
            ],
        
        'childre\'s books' => 
            [
                [
                    'name'      => 'Harry Potter and the Order of the Phoenix',
                    'price'     => 15.49,
                    'currency'  => '$',
                    'authors'   => 
                        [
                            [
                                'name' => 'J. K. Rowling'
                            ],
                        ],
                ],
                [
                    'name'      => 'Harry Potter and the Cursed Child',
                    'price'     => 8.5,
                    'currency'  => '$',
                    'authors'   => 
                        [
                            [
                                'name' => 'J. K. Rowling',
                            ],
                            [
                                'name' => 'Jack Thorne'
                            ],
                        ],
                ],
            ],
        
    ],
];
```




## Bugs and feature requests

Have a bug or a feature request? 
Please first read the issue guidelines and search for existing and closed issues. 
If your problem or idea is not addressed yet, [please open a new issue][link-new-issue].




## Contributing guidelines

All contributions are more than welcomed. 
Contributions may close an issue, fix a bug (reported or not reported), add new design blocks, 
improve the existing code, add new feature, and so on. 
In the interest of fostering an open and welcoming environment, 
we as contributors and maintainers pledge to making participation in our project and our community a harassment-free experience for everyone, 
regardless of age, body size, disability, ethnicity, gender identity and expression, level of experience, nationality, 
personal appearance, race, religion, or sexual identity and orientation. 
[Read the full Code of Conduct][link-code-of-conduct].




#### Versioning

Through the development of new versions, we're going use the [Semantic Versioning][link-semver]. 

Example: `1.0.0`.
- Major release: increment the first digit and reset middle and last digits to zero. Introduces major changes that might break backward compatibility. E.g. 2.0.0
- Minor release: increment the middle digit and reset last digit to zero. It would fix bugs and also add new features without breaking backward compatibility. E.g. 1.1.0
- Patch release: increment the third digit. It would fix bugs and keep backward compatibility. E.g. 1.0.1




## Authors

* **Banciu N. Cristian Mihai**

See also the list of [contributors][link-contributors] who participated in this project.




## License

This project is licensed under the MIT License - see the [LICENSE][link-license] file for details.




<!-- Links -->
[ico-php-require]:          https://img.shields.io/badge/php-%3E%3D%207.1-8892BF.svg?style=flat-square
[ico-version]:              https://img.shields.io/packagist/v/binary-cube/dot-array.svg?style=flat-square
[ico-downloads]:            https://img.shields.io/packagist/dt/binary-cube/dot-array.svg?style=flat-square
[ico-travis]:               https://img.shields.io/travis/binary-cube/dot-array/master.svg?style=flat-square
[ico-scrutinizer]:          https://img.shields.io/scrutinizer/coverage/g/binary-cube/dot-array.svg?style=flat-square
[ico-code-quality]:         https://img.shields.io/scrutinizer/g/binary-cube/dot-array.svg?style=flat-square
[ico-license]:              https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[link-domain]:              https://binary-cube.com
[link-homepage]:            https://binary-cube.com
[link-git-source]:          https://github.com/binary-cube/dot-array
[link-packagist]:           https://packagist.org/packages/binary-cube/dot-array
[link-downloads]:           https://packagist.org/packages/binary-cube/dot-array
[link-php-site]:            https://php.net
[link-semver]:              https://semver.org
[link-code-of-conduct]:     https://github.com/binary-cube/dot-array/blob/master/code-of-conduct.md
[link-license]:             https://github.com/binary-cube/dot-array/blob/master/LICENSE
[link-contributors]:        https://github.com/binary-cube/dot-array/graphs/contributors
[link-new-issue]:           https://github.com/binary-cube/dot-array/issues/new
[link-travis]:              https://travis-ci.org/binary-cube/dot-array
[link-scrutinizer]:         https://scrutinizer-ci.com/g/binary-cube/dot-array/code-structure
[link-code-quality]:        https://scrutinizer-ci.com/g/binary-cube/dot-array
