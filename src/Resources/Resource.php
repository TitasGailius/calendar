<?php

namespace TitasGailius\Calendar\Resources;

use ReflectionClass;

abstract class Resource
{
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
}
