<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        DB::table('products')
            ->whereIn('department', ['man', 'Man', 'MAN'])
            ->update(['department' => 'Homme']);

        DB::table('products')
            ->whereIn('department', ['woman', 'Woman', 'WOMAN'])
            ->update(['department' => 'Femme']);

        DB::table('products')
            ->whereIn('department', ['accessories', 'Accessories', 'ACCESSORIES'])
            ->update(['department' => 'Essentials']);
    }

    public function down()
    {
        DB::table('products')
            ->where('department', 'Homme')
            ->update(['department' => 'man']);

        DB::table('products')
            ->where('department', 'Femme')
            ->update(['department' => 'woman']);

        DB::table('products')
            ->where('department', 'Essentials')
            ->update(['department' => 'accessories']);
    }
};
