<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Website\Forms\FieldMap;

class CreateWebsiteFormFieldMap extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('website_form_field_map', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', array_keys(FieldMap::MAP_TYPES));
            $table->string('form_field', 50);
            $table->enum('map_field', array_keys(FieldMap::getNovaMapFields()))->index();
            $table->enum('db_table', array_values(FieldMap::getUniqueMapTables()))->index();
            $table->text('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            // Set Unique Index
            $table->unique(['type', 'form_field']);
        });

        DB::table('website_form_field_map')->insertOrIgnore([
            // Base Lead Mapping
            ['type' => 'lead', 'form_field' => 'first name', 'map_field' => 'firstname', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'last name', 'map_field' => 'lastname', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'name', 'map_field' => 'special_name', 'db_table' => 'website_lead', 'details' => 'Breaks into first and last name.'],
            ['type' => 'lead', 'form_field' => 'full name', 'map_field' => 'special_name', 'db_table' => 'website_lead', 'details' => 'Breaks into first and last name.'],
            ['type' => 'lead', 'form_field' => 'email', 'map_field' => 'email_address', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'email address', 'map_field' => 'email_address', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'phone', 'map_field' => 'special_phone', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'phone number', 'map_field' => 'special_phone', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'home phone', 'map_field' => 'special_phone', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'mobile phone', 'map_field' => 'special_phone', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'work phone', 'map_field' => 'special_phone', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'cell phone', 'map_field' => 'special_phone', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'daytime phone', 'map_field' => 'special_phone', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'evening phone', 'map_field' => 'special_phone', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'toll free phone', 'map_field' => 'special_phone', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'address', 'map_field' => 'special_address', 'db_table' => 'website_lead', 'details' => 'Sub-elements will be mapped using type special_address.'],
            ['type' => 'lead', 'form_field' => 'physical address no po boxes', 'map_field' => 'special_address', 'db_table' => 'website_lead', 'details' => 'Sub-elements will be mapped using type special_address.'],
            ['type' => 'lead', 'form_field' => 'referral', 'map_field' => 'referral', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'referrer', 'map_field' => 'referral', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'preferred contact', 'map_field' => 'preferred_contact', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'how would you like to be contacted', 'map_field' => 'preferred_contact', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'preferred location', 'map_field' => 'preferred_location', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead', 'form_field' => 'preferred salesman', 'map_field' => 'preferred_salesperson', 'db_table' => 'website_lead', 'details' => 'Directly sets sales person if sales person name exists on dealer.'],
            ['type' => 'lead', 'form_field' => 'preferred sales man', 'map_field' => 'preferred_salesperson', 'db_table' => 'website_lead', 'details' => 'Directly sets sales person if sales person name exists on dealer.'],
            ['type' => 'lead', 'form_field' => 'preferred salesperson', 'map_field' => 'preferred_salesperson', 'db_table' => 'website_lead', 'details' => 'Directly sets sales person if sales person name exists on dealer.'],
            ['type' => 'lead', 'form_field' => 'preferred sales person', 'map_field' => 'preferred_salesperson', 'db_table' => 'website_lead', 'details' => 'Directly sets sales person if sales person name exists on dealer.'],

            // Lead Special Mapping - Name
            ['type' => 'special_name', 'form_field' => 'first', 'map_field' => 'firstname', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'special_name', 'form_field' => 'last', 'map_field' => 'lastname', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'special_name', 'form_field' => 'prefix', 'map_field' => 'prefix', 'db_table' => 'website_lead', 'details' => 'This will prepend to first name.'],
            ['type' => 'special_name', 'form_field' => 'middle', 'map_field' => 'middle', 'db_table' => 'website_lead', 'details' => 'This will append to first name.'],
            ['type' => 'special_name', 'form_field' => 'suffix', 'map_field' => 'suffix', 'db_table' => 'website_lead', 'details' => 'This will append to last name.'],

            // Lead Special Mapping - Name
            ['type' => 'special_phone', 'form_field' => 'area', 'map_field' => 'area', 'db_table' => 'website_lead', 'details' => 'This will prepend to first name.'],
            ['type' => 'special_phone', 'form_field' => 'phone', 'map_field' => 'phone_number', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'special_phone', 'form_field' => 'full', 'map_field' => 'phone_number', 'db_table' => 'website_lead', 'details' => NULL],

            // Lead Special Mapping - Address
            ['type' => 'special_address', 'form_field' => 'addr_line1', 'map_field' => 'address', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'special_address', 'form_field' => 'addr_line2', 'map_field' => 'address2', 'db_table' => 'website_lead', 'details' => 'This will simply append to address.'],
            ['type' => 'special_address', 'form_field' => 'city', 'map_field' => 'city', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'special_address', 'form_field' => 'state', 'map_field' => 'state', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'special_address', 'form_field' => 'postal', 'map_field' => 'zip', 'db_table' => 'website_lead', 'details' => NULL],

            // Trade Mapping
            ['type' => 'trade', 'form_field' => 'type', 'map_field' => 'type', 'db_table' => 'website_lead_trades', 'details' => NULL],
            ['type' => 'trade', 'form_field' => 'make', 'map_field' => 'make', 'db_table' => 'website_lead_trades', 'details' => NULL],
            ['type' => 'trade', 'form_field' => 'model', 'map_field' => 'model', 'db_table' => 'website_lead_trades', 'details' => NULL],
            ['type' => 'trade', 'form_field' => 'year', 'map_field' => 'year', 'db_table' => 'website_lead_trades', 'details' => NULL],
            ['type' => 'trade', 'form_field' => 'price', 'map_field' => 'price', 'db_table' => 'website_lead_trades', 'details' => NULL],
            ['type' => 'trade', 'form_field' => 'length', 'map_field' => 'length', 'db_table' => 'website_lead_trades', 'details' => NULL],
            ['type' => 'trade', 'form_field' => 'width', 'map_field' => 'width', 'db_table' => 'website_lead_trades', 'details' => NULL],
            ['type' => 'trade', 'form_field' => 'notes', 'map_field' => 'notes', 'db_table' => 'website_lead_trades', 'details' => NULL],
            ['type' => 'trade', 'form_field' => 'comments', 'map_field' => 'notes', 'db_table' => 'website_lead_trades', 'details' => NULL],
            ['type' => 'trade', 'form_field' => 'questions', 'map_field' => 'notes', 'db_table' => 'website_lead_trades', 'details' => NULL],
            ['type' => 'trade', 'form_field' => 'comments/questions', 'map_field' => 'notes', 'db_table' => 'website_lead_trades', 'details' => NULL],

            // Lead Type Mapping
            ['type' => 'lead_type', 'form_field' => 'f&i input', 'map_field' => 'financing', 'db_table' => 'website_lead_fandi', 'details' => 'If title contains f&i input a special financing configuration will be used (for P&P primarily).'],
            ['type' => 'lead_type', 'form_field' => 'financing', 'map_field' => 'financing', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead_type', 'form_field' => 'rent to own', 'map_field' => 'financing', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead_type', 'form_field' => 'build', 'map_field' => 'build', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead_type', 'form_field' => 'design a trailer', 'map_field' => 'build', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead_type', 'form_field' => 'rental', 'map_field' => 'rentals', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead_type', 'form_field' => 'sell your', 'map_field' => 'trade', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead_type', 'form_field' => 'trade', 'map_field' => 'trade', 'db_table' => 'website_lead', 'details' => NULL],
            ['type' => 'lead_type', 'form_field' => 'service your', 'map_field' => 'service', 'db_table' => 'website_lead', 'details' => NULL],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('website_form_field_map');
    }
}
