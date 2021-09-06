<?php

namespace Chuckcms\Addresses\Traits;

use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;
use Chuckcms\Addresses\Contracts\Address;
use Illuminate\Database\Eloquent\Builder;
use Chuckcms\Addresses\Exceptions\FailedValidation;
use Chuckcms\Addresses\Models\Address as AddressModel;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasAddresses
{
    private $addressClass;

    /**
     * Boot the addressable trait for the model.
     *
     * @return void
     */
    public static function bootHasAddresses()
    {
        static::deleting(function (self $model) {
            $model->addresses()->delete();
        });
    }

    public function getAddressClass()
    {
        if (! isset($this->addressClass)) {
            $this->addressClass = config('addresses.models.address');
        }

        return $this->addressClass;
    }

    /**
     * A model may have multiple addresses.
     */
    public function addresses(): MorphToMany
    {
        return $this->morphToMany(
            config('addresses.models.address'),
            'model',
            config('addresses.table_names.model_has_addresses'),
            config('addresses.column_names.model_morph_key'),
            'address_id'
        );
    }

    /**
     * Check if model has addresses.
     *
     * @return bool
     */
    public function hasAddresses(): bool
    {
        return (bool) count($this->addresses);
    }

    /**
     * Add an address to this model.
     *
     * @param  array  $attributes
     * @return mixed
     * @throws Exception
     */
    public function addAddress(array $attributes)
    {
        $attributes = $this->loadAddressAttributes($attributes);

        return $this->addresses()->create($attributes);
    }

    /**
     * Updates the given address.
     *
     * @param  Address  $address
     * @param  array    $attributes
     * @return bool
     * @throws Exception
     */
    public function updateAddress(Address $address, array $attributes): bool
    {
        $attributes = $this->loadAddressAttributes($attributes);

        return $address->fill($attributes)->save();
    }

    /**
     * Revoke the given address from the model.
     *
     * @param int|\Chuck\Addresses\Contracts\Address $address
     */
    protected function removeAddress($address)
    {
        $this->addresses()->detach($this->getStoredAddress($address));

        $this->load('addresses');

        return $this;
    }

    /**
     * Deletes given address.
     *
     * @param  Address  $address
     * @return mixed
     * @throws Exception
     */
    public function deleteAddress(Address $address): bool
    {
        return $this->addresses()->where('id', $address->id)->delete();
    }

    /**
     * Determine if the model has (one of) the given address(es).
     *
     * @param int|array|\Chuck\Address\Contracts\Address|\Illuminate\Support\Collection $addresses
     *
     * @return bool
     */
    public function hasAddress($addresses): bool
    {
        if (is_int($addresses)) {
            return $this->addresses->contains('id', $addresses);
        }

        if ($addresses instanceof Address) {
            return $this->addresses->contains('id', $addresses->id);
        }

        if (is_array($addresses)) {
            foreach ($addresses as $address) {
                if ($this->hasAddress($address)) {
                    return true;
                }
            }

            return false;
        }

        return $addresses->intersect($this->addresses)->isNotEmpty();
    }

    public function getAddressLabels(): Collection
    {
        return $this->addresses->pluck('label');
    }

    protected function getStoredAddress($address): Address
    {
        $addressClass = $this->getAddressClass();

        if (is_numeric($address)) {
            return $addressClass->findById($address);
        }

        if ($address instanceof Address) {
            return $addressClass->findById($address->id);
        }

        return $address;
    }

    /**
     * Get the primary address.
     *
     * @param  string  $direction
     * @return Address|null
     */
    public function getPrimaryAddress(string $direction = 'desc'): ?Address
    {
        return $this->addresses()
                    ->primary()
                    ->orderBy('is_primary', $direction)
                    ->first();
    }

    /**
     * Get the billing address.
     *
     * @param  string  $direction
     * @return Address|null
     */
    public function getBillingAddress(string $direction = 'desc'): ?Address
    {
        return $this->addresses()
                    ->billing()
                    ->orderBy('is_billing', $direction)
                    ->first();
    }

    /**
     * Get the first shipping address.
     *
     * @param  string  $direction
     * @return Address|null
     */
    public function getShippingAddress(string $direction = 'desc'): ?Address
    {
        return $this->addresses()
                    ->shipping()
                    ->orderBy('is_shipping', $direction)
                    ->first();
    }

    /**
     * Add country id to attributes array.
     *
     * @param  array  $attributes
     * @return array
     * @throws FailedValidation
     */
    public function loadAddressAttributes(array $attributes): array
    {
        if (! isset($attributes['label']))
            throw new FailedValidation('[Addresses] No label given.');

        $validator = $this->validateAddress($attributes);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $error  = '[Addresses] '. implode(' ', $errors);

            throw new FailedValidation($error);
        }

        return $attributes;
    }

    /**
     * Validate the address.
     *
     * @param  array  $attributes
     * @return Validator
     */
    function validateAddress(array $attributes): Validator
    {
        $rules = (new AddressModel)->getValidationRules();

        return validator($attributes, $rules);
    }
}
