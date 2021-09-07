<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('addresses.table_names');
        $morphName = config('addresses.column_names.model_morph_name');

        Schema::create($tableNames['addresses'], function (Blueprint $table) use ($morphName) {
            $table->increments('id');
            
            $table->string('label');
            $table->string('street')->nullable()->default(null);
            $table->string('housenumber')->nullable()->default(null);
            $table->string('housenumber_postfix')->nullable()->default(null);
            $table->string('postal_code')->nullable()->default(null);
            $table->string('city')->nullable()->default(null);
            $table->string('state')->nullable()->default(null);
            $table->string('country')->nullable()->default(null);
            
            $table->string('latitude')->nullable()->default(null);
            $table->string('longitude')->nullable()->default(null);
            
            $table->boolean('is_public')->default(false);
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_billing')->default(false);
            $table->boolean('is_shipping')->default(false);
            
            $table->nullableMorphs($morphName);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('addresses.table_names');

        Schema::drop($tableNames['addresses']);
    }
}
