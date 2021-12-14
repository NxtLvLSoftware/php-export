<?php

declare(strict_types=1);

/**
 * Copyright (C) 2018–2021 NxtLvL Software Solutions
 *
 * @author Jack Noordhuis
 *
 * This is free and unencumbered software released into the public domain.
 *
 * Anyone is free to copy, modify, publish, use, compile, sell, or
 * distribute this software, either in source code form or as a compiled
 * binary, for any purpose, commercial or non-commercial, and by any means.
 *
 * In jurisdictions that recognize copyright laws, the author or authors
 * of this software dedicate any and all copyright interest in the
 * software to the public domain. We make this dedication for the benefit
 * of the public at large and to the detriment of our heirs and
 * successors. We intend this dedication to be an overt act of
 * relinquishment in perpetuity of all present and future rights to this
 * software under copyright law.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
 * OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 * For more information, please refer to <http://unlicense.org/>
 *
 */

namespace NxtLvLSoftware\Import;

use ArrayAccess;
use BadFunctionCallException;
use Closure;
use InvalidArgumentException;
use Laravel\SerializableClosure\Support\ReflectionClosure;
use ReflectionException;
use ReflectionFunction;
use function class_exists;
use function file_exists;
use function function_exists;
use function is_int;
use function is_string;

if(!class_exists('\NxtLvLSoftware\Import\Container')) {
	/**
	 * Internal container responsible for storing class and function definitions mapped to their names and binding order.
	 */
	class Container {

		private array $namedBindings = [];
		private array $orderedBindings = [];

		public function bind(mixed $key, $concrete) {
			if($concrete instanceof Closure and !is_string($key)) {
				try {
					$key = (new ReflectionFunction($concrete))->getShortName();
				} catch(ReflectionException $e) {
					throw new InvalidArgumentException("Unable to determine name for closure: {$e->getMessage()}");
				}
			}

			if(($concrete instanceof Closure) || (is_string($concrete) and class_exists($concrete))) {
				$this->namedBindings[is_string($key) ? $key : $concrete] = $concrete;
				$this->orderedBindings[] = $concrete;
			} else {
				throw new InvalidArgumentException('Invalid binding type, must be a Closure or a class name');
			}
		}

		public function get(string|int $key) : Closure|string {
			$binding = $this->namedBindings[$key] ?? $this->orderedBindings[$key] ?? null;

			if($binding === null) {
				throw new InvalidArgumentException('Binding not found for key: \'' . $key . '\'');
			}

			return $binding;
		}

		public function exists(string $key) : bool {
			return ($this->namedBindings[$key] ?? null) !== null;
		}

	}
}

if(!class_exists('\NxtLvLSoftware\Import\Import')) {
	/**
	 * User facing array wrapper for retrieving function and class bindings from a {@link \NxtLvLSoftware\Import\Container}.
	 */
	class Import implements ArrayAccess {

		public function __construct(private readonly Container $container) {

		}

		public function __get(string $name) {
			return $this->container->get($name);
		}

		public function __call(string $name, array $arguments) {
			$this->container->get($name)(...$arguments);
		}

		public function offsetExists($offset) : bool {
			return $this->container->exists($offset);
		}

		public function offsetGet($offset) : mixed {
			return $this->container->get($offset);
		}

		public function offsetSet($offset, $value) : void {
			throw new BadFunctionCallException('Cannot set values in the import container');
		}

		public function offsetUnset($offset) : void {
			throw new BadFunctionCallException('Cannot unset values in the import container');
		}

	}
}

if(!function_exists('\NxtLvLSoftware\Import\export')) {
	/**
	 * Export a list of named functions and classes for a file.
	 *
	 * @param list<callable|class-string> ...$exports
	 */
	function export(...$exports) : Container {
		$container = new Container();

		foreach($exports as $key => $export) {
			$container->bind($key, $export);
		}

		return $container;
	}
}

if(!function_exists('\NxtLvLSoftware\Import\from')) {
	/**
	 * Import functions and class definitions from a file.
	 *
	 * @param string $path
	 *
	 * @return \NxtLvLSoftware\Import\Import
	 */
	function from(string $path) : Import {
		if(!file_exists($path)) {
			if(!file_exists($path . '.php')) {
				throw new BadFunctionCallException("File does not exist: $path");
			}
			$path .= '.php';
		}

		$exports = require $path;

		if(!$exports instanceof Container) {
			throw new BadFunctionCallException('The import file must return an instance of \NxtLvLSoftware\Import\Container');
		}

		return new Import($exports);
	}
}

return export(export(...), from(...), Container::class, Import::class);