<?php namespace Windsor\Master;

use Dflydev\DotAccessData\Data;

class Model implements \ArrayAccess
{
    /**
     * @var Data
     */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->setData($data);
    }

    /**
     * @return Data
     */
    private function d()
    {
        if (null === $this->data) {
            $this->data = new Data();
        }

        return $this->data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->d()->import($data);

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->d()->export();
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->d()->get($name, $default);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return $this->d()->has($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function set($name, $value)
    {
        $this->d()->set($name, $value);

        return $this;
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __isset($name)
    {
        return null !== $this->d()->get($name);
    }

    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->d()->remove($offset);
    }
}
