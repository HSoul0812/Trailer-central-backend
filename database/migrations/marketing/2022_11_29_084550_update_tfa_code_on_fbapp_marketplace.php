<?php

use App\Models\Marketing\Facebook\Marketplace;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class UpdateTfaCodeOnFbappMarketplace extends Migration
{
    /**
     * Only use the IDS of the accounts that got manually added
     * the TFA Code in TFA Password instead.
     */
    private const MANUALLY_ADDED_ACCOUNTS_IDS = [
        138,
        315,
        318,
        336,
        343,
        376,
        393,
        397,
        399,
        403,
        406,
        409,
        416,
        427,
        433,
        434,
        436,
        446,
        485,
        486
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Remove tfa_code at the end of the field list
        Schema::table(Marketplace::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('tfa_code');
        });

        // Add in the right order
        Schema::table(Marketplace::TABLE_NAME, function (Blueprint $table) {
            $table->string('tfa_code')->after('tfa_password');
        });

        // Move the codes only on manually updated accounts and clear the field
        DB::table(Marketplace::TABLE_NAME)
            ->whereIn('id', self::MANUALLY_ADDED_ACCOUNTS_IDS)
            ->update([
                'tfa_code' => DB::raw('tfa_password'),
                'tfa_password' => null
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Move back the data only on manually added accounts
        DB::table(Marketplace::TABLE_NAME)
            ->whereIn('id', self::MANUALLY_ADDED_ACCOUNTS_IDS)
            ->update([
                'tfa_password' => DB::raw('tfa_code'),
                'tfa_code' => null
            ]);
    }
}
