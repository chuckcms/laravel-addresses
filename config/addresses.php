<?php

return [

    'models' => [

        /*
         * Which model to use for the Address when using the 'HasAddresses' trait.
         *
         */

        'address' => Chuckcms\Addresses\Models\Address::class,

    ],

    'table_names' => [

        /*
         * Define the table name to use when using the 'HasAddresses' trait.
         */

        'addresses' => 'addresses',

        /*
         * Define the table name to use when using the 'HasAddresses' trait for
         * retrieving the models linked to the addresses.
         */

        'model_has_addresses' => 'model_has_addresses',

    ],

    'column_names' => [

        'model_morph_key' => 'model_id',

    ],

    'fields' => [
        'addresses' => [
            'label' => 'required|string|max:255',
            'street' => 'nullable|string|max:140',
            'housenumber' => 'nullable|string|max:140',
            'housenumber_postfix' => 'nullable|string|max:140',
            'postal_code' => 'nullable|string|max:140',
            'city' => 'nullable|string|max:140',
            'state' => 'nullable|string|max:140',
            'country' => 'nullable|alpha|size:2',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_primary' => 'sometimes|boolean',
            'is_billing' => 'sometimes|boolean',
            'is_shipping' => 'sometimes|boolean',
        ]
    ],
];
