<?php

use App\Models\Website\Website;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIsOemStatusForCurrentWebsites extends Migration
{
    private const OEM_SITES = [
        'inventory.apcequipment.com',
        'inventory.atw.com',
        'inventory.bigtextrailers.com',
        'inventory.bwisetrailers.com',
        'inventory.cargoexpress.com',
        'inventory.carry-ontrailer.com',
        'inventory.cttrailers.com',
        'inventory.diamondc.com',
        'inventory.fraserpacificequipment.com',
        'inventory.goldengait.com',
        'inventory.haulmark.com',
        'inventory.lamartrailer.net',
        'inventory.lamartrailers.com',
        'inventory.loadtrail.com',
        'inventory.loetrailers.com',
        'inventory.looktrailers.com',
        'inventory.maxeyco.com',
        'inventory.mgstrailerstore.com',
        'inventory.norstarcompany.com',
        'inventory.paceamerican.com',
        'inventory.pjtrailers.com',
        'inventory.renvillesales.com',
        'inventory.sure-trac.com',
        'inventory.tegtmeyertrailers.com',
        'inventory.trucknamerica.com',
        'inventory.txcustomtrailers.com',
        'inventory.weingartz.com',
        'inventory.wellscargo.com',
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
