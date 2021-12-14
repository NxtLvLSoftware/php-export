<?php

declare(strict_types=1);

/**
 * Copyright (C) 2018–2021 NxtLvL Software Solutions
 *
 * This is private software, you cannot redistribute and/or modify it in any way
 * unless given explicit permission to do so. If you have not been given explicit
 * permission to view or modify this software you should take the appropriate actions
 * to remove this software from your device immediately.
 *
 * @author Jack Noordhuis
 *
 */

namespace examples\classes;

use function NxtLvLSoftware\Import\export;
use const PHP_EOL;

class Person {

	public function __construct(private readonly string $name, private readonly int $age) {
		// ...
	}

	public function getName() : string {
		return $this->name;
	}

	public function getAge() : int {
		return $this->age;
	}

}

function prnt(Person $person) : void {
	echo $person->getName() . ' is ' . $person->getAge() . ' years old.' . PHP_EOL;
}

return export(prnt(...), Person::class);