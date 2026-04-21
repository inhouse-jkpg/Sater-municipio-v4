<?php

namespace Pressidium\WP\CookieConsent\Dependencies\League\Container\Exception;

use Pressidium\WP\CookieConsent\Dependencies\Psr\Container\NotFoundExceptionInterface;
use InvalidArgumentException;

class NotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
}
