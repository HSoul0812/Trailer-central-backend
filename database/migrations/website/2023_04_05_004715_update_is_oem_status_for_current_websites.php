<?php

use App\Models\Website\Website;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIsOemStatusForCurrentWebsites extends Migration
{
    private const OEM_SITES = [
        'inventory.bwisetrailers.com',
        'inventory.pjtrailers.com',
        'inventory.haulmark.com',
        'inventory.cargoexpress.com',
        'inventory.paceamerican.com',
        'inventory.looktrailers.com',
        'inventory.diamondc.com',
        'inventory.loadtrail.com',
        'inventory.lamartrailers.com',
        'inventory.norstarcompany.com',
        'inventory.wellscargo.com',
        'inventory.lamartrailer.net',
        'inventory.loetrailers.com',
        'inventory.cttrailers.com',
        'inventory.maxeyco.com',
        'inventory.tegtmeyertrailers.com',
        'inventory.goldengait.com',
        'inventory.renvillesales.com',
        'inventory.apcequipment.com',
        'inventory.weingartz.com',
        'inventory.fraserpacificequipment.com',
        'inventory.txcustomtrailers.com',
        'inventory.trucknamerica.com',
        'inventory.mgstrailerstore.com'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Website::whereIn('domain', self::OEM_SITES)->update(['is_oem' => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Website::whereIn('domain', self::OEM_SITES)->update(['is_oem' => false]);
    }
}
