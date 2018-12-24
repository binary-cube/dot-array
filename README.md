# DotArray

Require `PHP >=7.1`

Accessing PHP Arrays via DOT notation is easy as:

```php
DotArray::create(['config' => ['some.dotted.key' => 'value']])->get('config.{some.dotted.key}')
```
[![Latest Stable Version](https://poser.pugx.org/binary-cube/dot-array/version)](https://packagist.org/packages/binary-cube/dot-array)
[![Total Downloads](https://poser.pugx.org/binary-cube/dot-array/downloads)](https://packagist.org/packages/binary-cube/dot-array)
[![Build Status](https://travis-ci.org/binary-cube/dot-array.svg?branch=master)](https://travis-ci.org/binary-cube/dot-array)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/003187f2016e4c4cb1b014ccc9bdb5c0)](https://www.codacy.com/app/microThread/dot-array)
[![Code Coverage](https://scrutinizer-ci.com/g/binary-cube/dot-array/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/binary-cube/dot-array/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/binary-cube/dot-array/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/binary-cube/dot-array/?branch=master)
[![License](https://poser.pugx.org/binary-cube/dot-array/license)](LICENSE)

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

- **where**:
    -   ```php
        /*
            Allowed Operations:
                =, == ===, !=, !==, <, >, <=, >=,
                in, not-in, between, not-between, eq, ne, lt, gt, lte, gte, contains, not-contains
        */
        
        // Example 1.
        $books = $dot->get('books.{childre\'s books}')->where(['between', 'price', 5, 12]);

        $books->toArray();
        
        // Example 2.
        $books = $dot->get('books.{childre\'s books}')->where(['>', 'price', 10]);
        
        // Example 3.
        $books = $dot->get('books.{childre\'s books}')->where(['in', 'price', [8.5, 15.49]]);
        
        // Example 4.
        $books = $dot->get('books.{childre\'s books}')->where(function ($value, $key) {
            return $value['name'] === 'Harry Potter and the Order of the Phoenix';
        });
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

## Authors

* **Banciu N. Cristian Mihai**

See also the list of [contributors](https://github.com/binary-cube/dot-array/graphs/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details
