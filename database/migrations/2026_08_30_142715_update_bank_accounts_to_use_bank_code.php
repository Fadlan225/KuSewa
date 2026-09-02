<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new column
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->string('bank_code', 13)->nullable()->after('owner_profile_id');
        });

        // Seed banks first if empty
        if (\Illuminate\Support\Facades\DB::table('banks')->count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'BankSeeder']);
        }

        // Migrate existing data
        $bankMapping = [
            'BCA' => '014',
            'Mandiri' => '008',
            'BNI' => '009',
            'BRI' => '002',
            'BSI' => '451',
            'CIMB Niaga' => '022'
        ];

        $accounts = \Illuminate\Support\Facades\DB::table('bank_accounts')->get();
        foreach ($accounts as $account) {
            $code = null;
            
            // Map exact or partial match
            foreach ($bankMapping as $name => $mappedCode) {
                if (stripos($account->bank_name, $name) !== false) {
                    $code = $mappedCode;
                    break;
                }
            }
            
            if ($code) {
                \Illuminate\Support\Facades\DB::table('bank_accounts')
                    ->where('id', $account->id)
                    ->update(['bank_code' => $code]);
            }
        }

        // Alter schema to make it foreign key and not null, then drop old column
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Drop bank_name safely
            $table->dropColumn('bank_name');
        });
        
        // Add constraint
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->foreign('bank_code')->references('code')->on('banks')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropForeign(['bank_code']);
            $table->string('bank_name')->after('owner_profile_id')->nullable();
        });
        
        // Restore mapping best effort
        $reverseMapping = [
            '014' => 'BCA',
            '008' => 'Mandiri',
            '009' => 'BNI',
            '002' => 'BRI',
            '451' => 'BSI',
            '022' => 'CIMB Niaga'
        ];
        
        $accounts = \Illuminate\Support\Facades\DB::table('bank_accounts')->get();
        foreach ($accounts as $account) {
            if (isset($reverseMapping[$account->bank_code])) {
                \Illuminate\Support\Facades\DB::table('bank_accounts')
                    ->where('id', $account->id)
                    ->update(['bank_name' => $reverseMapping[$account->bank_code]]);
            }
        }
        
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn('bank_code');
        });
    }
};
