<?php

namespace Koen12344\SiteImportForGbp\DependencyInjection;

use ArrayAccess;
use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements ContainerInterface, ArrayAccess {

	private $values = [];

	private $locked;
	public function __construct(array $environment){
		$this->locked = false;
		$this->values = $environment;
	}

	public function configure($configurations){
		if(!is_array($configurations)){
			$configurations = [ $configurations ];
		}
		foreach($configurations as $configuration){
			$this->modify($configuration);
		}
	}

	public function is_locked(): bool {
		return $this->locked;
	}

	public function lock(){
		$this->locked = true;
	}
	public function register($name, $value){
		$this->values[$name] = $value;
	}

	public function get($id){
		if(!array_key_exists($id, $this->values)){
			throw new InvalidArgumentException(sprintf('Container doesn\'t have a value stored for the "%s" key.', $id));
		}elseif(!$this->is_locked()){
			$this->lock();
		}

		return $this->values[$id] instanceof Closure ? $this->values[$id]($this) : $this->values[$id];
	}

	public function has( string $id ): bool {
		return array_key_exists($id, $this->values);
	}

	public function service($callable): Closure {
		if(!is_object($callable) || !method_exists($callable, '__invoke')){
			throw new \InvalidArgumentException('Service definition is not a Closure or invokable object.');
		}

		return function ( Container $container) use ($callable){
			static $object;

			if(null === $object){
				$object = $callable($container);
			}

			return $object;
		};
	}
	public function offsetExists( $offset ): bool {
		return $this->has($offset);
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->get($offset);
	}

	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
		if($this->locked){
			throw new \RuntimeException('Container is locked and cannot be modified');
		}
		$this->values[$offset] = $value;
	}
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
		if($this->locked){
			throw new \RuntimeException('Container is locked and cannot be modified');
		}
		unset($this->values[$offset]);
	}

	private function modify($configuration) {
		if(is_string($configuration)){
			$configuration = new $configuration();
		}
		if(!$configuration instanceof ContainerConfigurationInterface){
			throw new InvalidArgumentException('Configuration object must implement the "ContainerConfigurationInterface".');
		}
		$configuration->modify($this);
	}
}
