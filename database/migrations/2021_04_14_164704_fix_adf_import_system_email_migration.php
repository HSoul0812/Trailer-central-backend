<?php

use App\Models\System\Email;
use Illuminate\Database\Migrations\Migration;

class FixAdfImportSystemEmailMigration extends Migration
{
    /**
     * Custom Emails Update
     */
    private const CUSTOM_EMAILS_FROM = 'catchall@operatebeyond.com';
    private const CUSTOM_EMAILS_TO = 'adf@operatebeyond.com';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update Old System Email to New One
        Email::where('email', self::CUSTOM_EMAILS_FROM)
             ->update(['email' => self::CUSTOM_EMAILS_TO]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
