<?php

namespace WP_CLI_Dotenv\Dotenv;

use Traversable;

class Collection implements \ArrayAccess, \IteratorAggregate, \Countable
{
    protected $items;

    /**
     * Collection constructor.
     *
     * @param array $items
     */
    public function __construct($items = [])
    {
        $this->items = ($items instanceof Collection) ? $items->all() : $items;
    }

    public static function make($items)
    {
        return new static($items);
    }

    public function all()
    {
        return $this->items;
    }

    public function keys()
    {
        return new static(array_keys($this->items));
    }

    public function values()
    {
        return new static(array_values($this->items));
    }

    public function each($callback)
    {
        foreach ($this->items as $key => $value) {
            if (false === $callback($value, $key)) {
                break;
            }
        }

        return $this;
    }

    public function map($callback)
    {
        $keys   = array_keys($this->items);
        $values = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $values));
    }

    public function contains($callback)
    {
        return null !== $this->first($callback);
    }

    public function first($callback = null, $default = null)
    {
        if ($callback instanceof \Closure) {
            foreach ($this->items as $key => $value) {
                if ($callback($key, $value)) {
                    return $value;
                }
            }

            return $default;
        }

        return reset($this->items);
    }

    public function count()
    {
        return count($this->items);
    }

    public function implode($glue)
    {
        return implode($glue, $this->items);
    }

    public function filter($callback = null)
    {
        if ($callback instanceof \Closure) {
            return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
        }

        return new static(array_filter($this->items));
    }

    public function reject($callback)
    {
        return $this->filter(function ($value, $key) use ($callback) {
            return ! $callback($value, $key);
        });
    }

    public function isEmpty()
    {
        return empty($this->items);
    }

    public function reduce($callback, $initial)
    {
        return new static(array_reduce($this->items, $callback, $initial));
    }

    public function get($key, $default = null)
    {
        return $this->offsetExists($key) ? $this->offsetGet($key) : $default;
    }

    public function pluck($prop)
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

    public function put($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    public function search($needle)
    {
        if ($needle instanceof \Closure) {
            foreach ($this->items as $key => $value) {
                if ($needle($value, $key)) {
                    return $key;
                }
            }
            return false;
        }

        return array_search($needle, $this->items);
    }

    public function push($value)
    {
        array_push($this->items, $value);

        return $this;
    }

    public function only($keys)
    {
        return $this->filter(function ($value, $key) use ($keys) {
            return in_array($key, $keys);
        });
    }

    public function unique()
    {
        return new static(array_unique($this->items));
    }

    /**
     * Whether a offset exists
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * Offset to retrieve
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->items[ $offset ] : null;
    }

    /**
     * Offset to set
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->items[ $offset ] = $value;
    }

    /**
     * Offset to unset
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->items[ $offset ]);
    }

    /**
     * Retrieve an external iterator
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}

