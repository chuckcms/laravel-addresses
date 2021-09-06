<?php

namespace Chuckcms\Addresses\Contracts;

interface Address
{
    /**
     * Find an address by its id.
     *
     * @param int $id
     *
     * @return \Chuck\Address\Contracts\Role
     *
     * @throws \Chuck\Address\Exceptions\AddressDoesNotExist
     */
    public static function findById(int $id): self;
}
