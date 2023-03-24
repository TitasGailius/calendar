<?php

namespace TitasGailius\Calendar\Resources;

use ReflectionClass;

/**
 * @template TValue of array
 */
abstract class Resource
{
    /**
     * Raw resource value.
     *
     * @var TValue
     */
    protected array $raw;

    /**
     * Update the current resource.
     */
    public function update(Resource $resource): static
    {
        $properties = (new ReflectionClass($resource))->getProperties();

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            if (! $property->isInitialized($resource)) {
                continue;
            }

            $this->{$property->getName()} = $property->getValue($resource);
        }

        return $this;
    }

    /**
     * Set raw value.
     *
     * @param  TValue  $raw
     * @return $this
     */
    public function setRaw(array $raw): static
    {
        $this->raw = $raw;

        return $this;
    }

    /**
     * Get raw value.
     */
    public function getRaw(): ?array
    {
        return $this->raw ?? null;
    }
}
