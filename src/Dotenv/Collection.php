<?php

namespace WP_CLI_Dotenv\Dotenv;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use ReturnTypeWillChange;
use Traversable;

class Collection implements ArrayAccess, IteratorAggregate, Countable
{
    protected $items;

    /**
     * Collection constructor.
     *
     * @param array|Collection $items
     */
    public function __construct($items = [])
    {
        $this->items = ($items instanceof Collection) ? $items->all() : $items;
    }

    public static function make($items): Collection
    {
        return new static($items);
    }

    public function all()
    {
        return $this->items;
    }

    public function keys(): Collection
    {
        return new static(array_keys($this->items));
    }

    public function values(): Collection
    {
        return new static(array_values($this->items));
    }

    public function each($callback): Collection
    {
        foreach ($this->items as $key => $value) {
            if (false === $callback($value, $key)) {
                break;
            }
        }

        return $this;
    }

    public function map($callback): Collection
    {
        $keys   = array_keys($this->items);
        $values = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $values));
    }

    public function contains($callback): bool
    {
        return null !== $this->first($callback);
    }

    public function first($callback = null, $default = null)
    {
        if ($callback instanceof Closure) {
            foreach ($this->items as $key => $value) {
                if ($callback($key, $value)) {
                    return $value;
                }
            }

            return $default;
        }

        return reset($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function implode($glue): string
    {
        return implode($glue, $this->items);
    }

    public function filter($callback = null): Collection
    {
        if ($callback instanceof Closure) {
            return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
        }

        return new static(array_filter($this->items));
    }

    public function reject($callback): Collection
    {
        return $this->filter(function ($value, $key) use ($callback) {
            return ! $callback($value, $key);
        });
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function reduce($callback, $initial): Collection
    {
        return new static(array_reduce($this->items, $callback, $initial));
    }

    public function get($key, $default = null)
    {
        return $this->offsetExists($key) ? $this->offsetGet($key) : $default;
    }

    public function pluck($prop): Collection
    {
        return $this->map(function ($item) use ($prop) {
            if (is_array($item) && array_key_exists($prop, $item)) {
                return $item[$prop];
            }
            if (is_object($item) && property_exists($item, $prop)) {
                return $item->$prop;
            }
            return null;
        });
    }

    public function put($key, $value): void
    {
        $this->offsetSet($key, $value);
    }

    public function search($needle)
    {
        if ($needle instanceof Closure) {
            foreach ($this->items as $key => $value) {
                if ($needle($value, $key)) {
                    return $key;
                }
            }
            return false;
        }

        return array_search($needle, $this->items);
    }

    public function push($value): Collection
    {
        array_push($this->items, $value);

        return $this;
    }

    public function only($keys): Collection
    {
        return $this->filter(function ($value, $key) use ($keys) {
            return in_array($key, $keys);
        });
    }

    public function unique(): Collection
    {
        return new static(array_unique($this->items));
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->items[ $offset ] : null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->items[ $offset ] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->items[ $offset ]);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}

