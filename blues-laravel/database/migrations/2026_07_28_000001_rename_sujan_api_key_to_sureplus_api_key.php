<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'sujan_api_key')
            ->update(['key' => 'sureplus_api_key']);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'sureplus_api_key')
            ->update(['key' => 'sujan_api_key']);
    }
};
