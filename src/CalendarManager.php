<?php

namespace TitasGailius\Calendar;

class CalendarManager implements Factory
{
    /**
     * Available extensions.
     *
     * @var array<int, \Closure>
     */
    protected array $extensions = [];

    /**
     * Create a new provider instance.
     */
    public function provider(?string $provider = null, array $config = []): Provider
    {
        $provider ??= $this->getDefaultProvider();

        $serviceConfig = config("calendar.providers.{$provider}", function () use ($default) {
            throw new InvalidArgumentException("Missing \"{$default}\" provider configuration.");
        });

        if (isset($this->extensions[$provider])) {
            return $this->extensions[$provider]($serviceConfig, $config);
        }

        $method = 'create'.ucfirst($serviceConfig['driver']).'Driver';

        return $this->{$method}($serviceConfig, $config);
    }

    /**
     * Create a Google driver.
     */
    public function createGoogleDriver(array $serviceConfig, array $config = []): Provider
    {
        $client = new Google($serviceConfig);
        $client->setAccessToken($config);

        return new GoogleProvider($client);
    }

    /**
     * Create a Microsoft driver.
     */
    public function createMicrosoftDriver(array $serviceConfig, array $config = []): Provider
    {
        $client = new Graph;
        $grap->setAccessToken($config['access_token']);
        $grap->setRefreshToken($config['refresh_token']);

        return new MicrosoftProvider($client);
    }

    /**
     * Get default provider name.
     */
    protected function getDefaultProvider(): string
    {
        return config('calendar.default', function () {
            throw new InvalidArgumentException('There\'s no default calendar provider configured.');
        });
    }

    /**
     * Extend supported providers.
     */
    public function extend(string $provider, Closure $creator)
    {
        $this->extensions[$provider] = $creator;
    }
}
