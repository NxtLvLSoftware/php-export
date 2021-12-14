JS style import/export for PHP
===================
__POC allowing developers to scope and rename imports according to context.__

```php
require_once 'vendor/autoload.php';

use function NxtLvLSoftware\Import\from;

// imports
[$named_func] = from('./functions');
[$print_person, $person] = from('./classes');

// call aliased function
$named_func();

// instantiate aliased class and call function from same import
$print_person(new $person('Jimmy', 19));
```

## About

Eliminates the need for composer and other autoloaders in favour of a JS style import/export. If all code conforms and exports
adhere to good practices then it gives control back to the application developer in terms of scoping code and naming required
imports according to the current context.

All php files should define a unique namespace to avoid collisions when importing, which allows the import site to alias
functions and classes to local variables using the [Symmetric array destructuring](https://www.php.net/manual/en/migration71.new-features.php#migration71.new-features.symmetric-array-destructuring) or [list()](https://www.php.net/manual/en/function.list.php) syntax.

See [classes.php](examples/classes.php) for declaring classes, [functions.php](examples/functions.php) for function definitions and [import.php](examples/import.php) for importing and using classes/functions.

## Caveats

Usually this approach only works nicely with simple single script applications, owing to fundamental differences between PHP and JS.
Without guarding all class and method declarations inside existing definition checks you'll run into duplicate definition
exceptions quite easily.

#### _## THIS IS POC SO NO UNIT TESTS ARE PROVIDED ##_

## License Information

The content of this repo is & will always be licensed under the [Unlicense](http://unlicense.org/).

> This is free and unencumbered software released into the public domain.
>
> Anyone is free to copy, modify, publish, use, compile, sell, or
> distribute this software, either in source code form or as a compiled
> binary, for any purpose, commercial or non-commercial, and by any
> means.

__A full copy of the license is available [here](../LICENSE).__

#

__A [NxtLvL Software Solutions](https://github.com/NxtLvLSoftware) product.__
