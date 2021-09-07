<?php

namespace Chuckcms\Addresses\Traits;

use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;
use Chuckcms\Addresses\Contracts\Address;
use Illuminate\Database\Eloquent\Builder;
use Chuckcms\Addresses\Exceptions\FailedValidation;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Chuckcms\Addresses\Models\Address as AddressModel;

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
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                $model->addresses()->forceDelete();
                return;
            }

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
     *
     * @return MorphMany
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(
            config('addresses.models.address'),
            config('addresses.column_names.model_morph_name'),
            config('addresses.column_names.model_morph_type'),
            config('addresses.column_names.model_morph_key')
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
     * Deletes given address(es).
     *
     * @param  int|array|\Chuck\Address\Contracts\Address $addresses
     * @param  bool $force
     * @return mixed
     * @throws Exception
     */
    public function deleteAddress($addresses, $force = false): bool
    {
        if (is_int($addresses) && $this->hasAddress($addresses)) {
            return $force ? 
                    $this->addresses()->where('id', $addresses)->forceDelete() : 
                    $this->addresses()->where('id', $addresses)->delete();
        }

        if ($addresses instanceof Address && $this->hasAddress($addresses)) {
            return $force ? 
                    $this->addresses()->where('id', $addresses->id)->forceDelete() : 
                    $this->addresses()->where('id', $addresses->id)->delete();
        }

        if (is_array($addresses)) {
            foreach ($addresses as $address) {
                if ($this->deleteAddress($address, $force)) {
                    continue;
                } 
            }

            return true;
        }

        return false;
    }

    /**
     * Forcefully deletes given address(es).
     *
     * @param  int|array|\Chuck\Address\Contracts\Address $addresses
     * @param  bool $force
     * @return mixed
     * @throws Exception
     */
    public function forceDeleteAddress($addresses): bool
    {
        return $this->deleteAddress($addresses, true);
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

    /**
     * Get the public address.
     *
     * @param  string  $direction
     * @return Address|null
     */
    public function getPublicAddress(string $direction = 'desc'): ?Address
    {
        return $this->addresses()
                    ->isPublic()
                    ->orderBy('is_public', $direction)
                    ->first();
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
                    ->isPrimary()
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
                    ->isBilling()
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
                    ->isShipping()
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
