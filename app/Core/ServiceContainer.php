<?php

namespace App\Core;

class ServiceContainer
{
    private array $services = [];
    private array $singletons = [];

    /**
     * Register a service factory
     */
    public function register(string $abstract, callable $factory): void
    {
        $this->services[$abstract] = $factory;
    }

    /**
     * Register a singleton service
     */
    public function singleton(string $abstract, callable $factory): void
    {
        $this->services[$abstract] = $factory;
        $this->singletons[$abstract] = true;
    }

    /**
     * Resolve a service from the container
     */
    public function resolve(string $abstract)
    {
        if (!isset($this->services[$abstract])) {
            throw new \Exception("Service {$abstract} not found");
        }

        // If it's a singleton and already instantiated, return the instance
        if (isset($this->singletons[$abstract]) && isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $instance = $this->services[$abstract]($this);

        // If it's a singleton, store the instance
        if (isset($this->singletons[$abstract])) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    private array $instances = [];
}