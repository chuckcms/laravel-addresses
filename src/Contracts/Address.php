<?php

namespace Chuckcms\Addresses\Contracts;

interface Address
{
    /**
     * Find an address by its id.
     *
     * @param int $id
     *
     * @throws \Chuck\Address\Exceptions\AddressDoesNotExist
     *
     * @return \Chuck\Address\Contracts\Role
     */
    public static function findById(int $id): self;
}
