<?php

namespace Chuckcms\Addresses\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Chuckcms\Addresses\Exceptions\AddressDoesNotExist;
use Chuckcms\Addresses\Contracts\Address as AddressContract;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Address extends Model implements AddressContract
{
    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * The attributes that are fillable on this model.
     *
     * @var array
     */
    protected $fillable = ['addressable_id', 'addressable_type'];

    /**
     * The default rules that the model will validate against.
     *
     * @var array
     */
    protected $rules = [];

    public function __construct(array $attributes = [])
    {
        $this->setTable(config('addresses.table_names.addresses'));
        $this->mergeFillables();

        parent::__construct($attributes);
    }

    /**
     * Merge fillable fields.
     *
     * @return void.
     */
    private function mergeFillables()
    {
        $fillable = $this->fillable;
        $columns  = array_keys(config('addresses.fields.addresses'));

        $this->fillable(array_merge($fillable, $columns));
    }

    /**
     * Get the related model.
     *
     * @return MorphTo
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the validation rules.
     *
     * @return array
     */
    public static function getValidationRules(): array
    {
        $rules = config('addresses.fields.addresses');

        return $rules;
    }

    /**
     * Scope public addresses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsPublic(Builder $builder): Builder
    {
        return $builder->where('is_public', true);
    }

    /**
     * Scope primary addresses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsPrimary(Builder $builder): Builder
    {
        return $builder->where('is_primary', true);
    }

    /**
     * Scope billing addresses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsBilling(Builder $builder): Builder
    {
        return $builder->where('is_billing', true);
    }

    /**
     * Scope shipping addresses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsShipping(Builder $builder): Builder
    {
        return $builder->where('is_shipping', true);
    }

    /**
     * Scope addresses by the given country.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $countryCode
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInCountry(Builder $builder, string $country): Builder
    {
        return $builder->where('country', $country);
    }

    public static function findById(int $id): AddressContract
    {
        $address = static::where('id', $id)->first();

        if (! $address) {
            throw AddressDoesNotExist::withId($id);
        }

        return $address;
    }
}