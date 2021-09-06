<?php

namespace Chuckcms\Addresses\Exceptions;

use InvalidArgumentException;

class AddressDoesNotExist extends InvalidArgumentException
{
    public static function withId(int $addressId)
    {
        return new static("There is no address with id `{$addressId}`.");
    }
}
