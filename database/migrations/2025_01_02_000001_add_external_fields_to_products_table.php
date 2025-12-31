<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('external_source')->nullable()->after('slug');
            $table->string('external_id')->nullable()->after('external_source');
            $table->string('image_path')->nullable()->after('image');

            $table->unique(
                ['external_source', 'external_id'],
                'products_external_source_external_id_unique'
            );
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_external_source_external_id_unique');
            $table->dropColumn(['external_source', 'external_id', 'image_path']);
        });
    }
};
