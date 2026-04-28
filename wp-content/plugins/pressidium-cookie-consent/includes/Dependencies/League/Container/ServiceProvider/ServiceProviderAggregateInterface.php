<?php declare(strict_types=1);

namespace Pressidium\WP\CookieConsent\Dependencies\League\Container\ServiceProvider;

use IteratorAggregate;
use Pressidium\WP\CookieConsent\Dependencies\League\Container\ContainerAwareInterface;

interface ServiceProviderAggregateInterface extends ContainerAwareInterface, IteratorAggregate
{
    /**
     * Add a service provider to the aggregate.
     *
     * @param string|ServiceProviderInterface $provider
     *
     * @return self
     */
    public function add($provider) : ServiceProviderAggregateInterface;

    /**
     * Determines whether a service is provided by the aggregate.
     *
     * @param string $service
     *
     * @return boolean
     */
    public function provides(string $service) : bool;

    /**
     * Invokes the register method of a provider that provides a specific service.
     *
     * @param string $service
     *
     * @return void
     */
    public function register(string $service);
}
