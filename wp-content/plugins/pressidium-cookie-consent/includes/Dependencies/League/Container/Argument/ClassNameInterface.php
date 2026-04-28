<?php declare(strict_types=1);

namespace Pressidium\WP\CookieConsent\Dependencies\League\Container\Argument;

interface ClassNameInterface
{
    /**
     * Return the class name.
     *
     * @return string
     */
    public function getClassName() : string;
}
