<?php

namespace Tests\Integration\Commands;

use App\Console\Commands\Database\PruneSSNCommand;
use App\Models\CRM\Leads\Jotform\WebsiteFormSubmissions;
use App\Models\Website\Lead\WebsiteLeadFAndI;
use Faker\Factory as Faker;
use Illuminate\Contracts\Container\BindingResolutionException as BindingResolutionExceptionAlias;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Class PruneSSNCommandTest
 *
 * @package Tests\Integration\Commands
 */
class PruneSSNCommandTest extends TestCase
{
    /**
     * @return void
     *
     * @throws BindingResolutionExceptionAlias
     */
    public function testRemoveSsnFromWebsiteFormSubmissions()
    {
        $faker = Faker::create();

        $ssn30Days = $faker->creditCardNumber;
        $olderFormSubmission = factory(WebsiteFormSubmissions::class)->create([
            'answers' => $this->formSubmissionsAnwers($ssn30Days),
            'created_at' => now()->subDays($faker->numberBetween(35, 1000)),
        ]);

        $ssnWithinLimitDays = $faker->creditCardNumber;
        $newerFormSubmission = factory(WebsiteFormSubmissions::class)->create([
            'answers' => $this->formSubmissionsAnwers($ssnWithinLimitDays),
            'created_at' => now()->subDays($faker->numberBetween(0, 25)),
        ]);

        $oldModel1 = $olderFormSubmission;
        $oldModel2 = $newerFormSubmission;

        $return = Artisan::call(PruneSSNCommand::class, [
            '--olderThanDays' => PruneSSNCommand::DEFAULT_OLDER_THAN_DAYS,
            '--chunkSize' => PruneSSNCommand::DEFAULT_CHUNK_SIZE,
            '--delay' => PruneSSNCommand::DEFAULT_DELAY,
        ]);

        $this->assertEquals(0, $return);

        $olderFormSubmission->refresh();
        $newerFormSubmission->refresh();

        // To assert that SSN data must be removed as it's older than specified days
        $this->assertWebForm($olderFormSubmission, $oldModel1, '');

        // To assert that SSN data must not be altered or removed as it's not older than specified days
        $this->assertWebForm($newerFormSubmission, $oldModel2, $ssnWithinLimitDays);

        $this->destroyFormSubmissionTestData($olderFormSubmission);
        $this->destroyFormSubmissionTestData($newerFormSubmission);
    }

    /**
     * @param WebsiteFormSubmissions $model
     *
     * @return void
     */
    private function destroyFormSubmissionTestData(WebsiteFormSubmissions $model)
    {
        $lead = $model->lead;
        $lead->website->dealer->delete();
        $lead->website->delete();
        $lead->delete();
        $model->delete();

        $this->assertDatabaseMissing(WebsiteFormSubmissions::TABLE_NAME, [
            'id' => $model->getKey(),
        ]);

        $this->assertDatabaseMissing($lead::TABLE_NAME, [
            'identifier' => $lead->getKey(),
        ]);

        $this->assertDatabaseMissing('website', [
            'id' => $lead->website_id,
        ]);

        $this->assertDatabaseMissing('dealer', [
            'dealer_id' => $lead->dealer_id,
        ]);
    }

    /**
     * @param WebsiteFormSubmissions $newModel
     * @param WebsiteFormSubmissions $oldModel
     * @param string $newSSNValue
     *
     * @return void
     */
    private function assertWebForm(
        WebsiteFormSubmissions $newModel,
        WebsiteFormSubmissions $oldModel,
        string $newSSNValue
    ) {
        collect($newModel->answers)->where('name', 'ssn')
            ->each(function ($item, $key) use ($newSSNValue) {
                $this->assertEquals($newSSNValue, $item['answer']);
            });

        $this->assertEquals($newModel->lead_id, $oldModel->lead_id);
        $this->assertEquals($newModel->merge_id, $oldModel->merge_id);
        $this->assertEquals($newModel->trade_id, $oldModel->trade_id);
        $this->assertEquals($newModel->submission_id, $oldModel->submission_id);
        $this->assertEquals($newModel->jotform_id, $oldModel->jotform_id);
        $this->assertEquals($newModel->customer_id, $oldModel->customer_id);
        $this->assertEquals($newModel->ip_address, $oldModel->ip_address);
        $this->assertEquals($newModel->created_at, $oldModel->created_at);
        $this->assertEquals($newModel->updated_at, $oldModel->updated_at);
        $this->assertEquals($newModel->status, $oldModel->status);
        $this->assertEquals($newModel->new, $oldModel->new);
        $this->assertEquals($newModel->is_ssn_removed, $oldModel->is_ssn_removed);
    }

    /**
     * @return void
     *
     * @throws BindingResolutionExceptionAlias
     */
    public function testRemoveSsnFromWebsiteLeadFAndI()
    {
        $faker = Faker::create();

        // Start Website Lead
        $olderWebLeadFI = factory(WebsiteLeadFAndI::class)->create();
        $newerWebLeadFI = factory(WebsiteLeadFAndI::class)->create([
            'date_imported' => $faker->dateTimeBetween('-25 days'),
        ]);

        $oldModel1 = $olderWebLeadFI;
        $oldModel2 = $newerWebLeadFI;
        // End Website Lead

        $return = Artisan::call(PruneSSNCommand::class, [
            '--olderThanDays' => PruneSSNCommand::DEFAULT_OLDER_THAN_DAYS,
            '--chunkSize' => PruneSSNCommand::DEFAULT_CHUNK_SIZE,
            '--delay' => PruneSSNCommand::DEFAULT_DELAY,
        ]);

        $this->assertEquals(0, $return);

        $olderWebLeadFI->refresh();
        $newerWebLeadFI->refresh();

        // To assert that SSN data must be removed as it's older than specified days
        $this->assertEquals('', $olderWebLeadFI->ssn_no);

        // To assert that SSN data must not be altered or removed as it's not older than specified days
        $this->assertEquals($oldModel2->ssn_no, $newerWebLeadFI->ssn_no);

        $this->assertWebsiteLead($oldModel1, $olderWebLeadFI);
        $this->assertWebsiteLead($oldModel2, $newerWebLeadFI);

        $this->destroyWebLeadFITestData($olderWebLeadFI);
        $this->destroyWebLeadFITestData($newerWebLeadFI);
    }

    /**
     * @param WebsiteLeadFAndI $old
     * @param WebsiteLeadFAndI $new
     *
     * @return void
     */
    private function assertWebsiteLead(WebsiteLeadFAndI $old, WebsiteLeadFAndI $new): void
    {
        $this->assertEquals($old->lead_id, $new->lead_id);
        $this->assertEquals($old->drivers_first_name, $new->drivers_first_name);
        $this->assertEquals($old->drivers_mid_name, $new->drivers_mid_name);
        $this->assertEquals($old->drivers_last_name, $new->drivers_last_name);
        $this->assertEquals($old->drivers_suffix, $new->drivers_suffix);
        $this->assertEquals($old->drivers_dob, $new->drivers_dob);
        $this->assertEquals($old->drivers_no, $new->drivers_no);
        $this->assertEquals($old->drivers_front, $new->drivers_front);
        $this->assertEquals($old->drivers_back, $new->drivers_back);
        $this->assertEquals($old->marital_status, $new->marital_status);
        $this->assertEquals($old->preferred_contact, $new->preferred_contact);
        $this->assertEquals($old->daytime_phone, $new->daytime_phone);
        $this->assertEquals($old->evening_phone, $new->evening_phone);
        $this->assertEquals($old->mobile_phone, $new->mobile_phone);
        $this->assertEquals($old->rent_own, $new->rent_own);
        $this->assertEquals($old->monthly_rent, $new->monthly_rent);
        $this->assertEquals($old->type, $new->type);
        $this->assertEquals($old->co_first_name, $new->co_first_name);
        $this->assertEquals($old->co_last_name, $new->co_last_name);
        $this->assertEquals($old->item_inquiry, $new->item_inquiry);
        $this->assertEquals($old->item_price, $new->item_price);
        $this->assertEquals($old->down_payment, $new->down_payment);
        $this->assertEquals($old->trade_value, $new->trade_value);
        $this->assertEquals($old->trade_payoff, $new->trade_payoff);
        $this->assertEquals($old->other_income, $new->other_income);
        $this->assertEquals($old->other_income_source, $new->other_income_source);
        $this->assertEquals($old->extra, $new->extra);
        $this->assertEquals($old->preferred_salesperson, $new->preferred_salesperson);
        $this->assertEquals($old->delivery_method, $new->delivery_method);
        $this->assertEquals($old->date_imported, $new->date_imported);
    }

    /**
     * @param WebsiteLeadFAndI $websiteLeadFAndI
     *
     * @return void
     */
    private function destroyWebLeadFITestData(WebsiteLeadFAndI $websiteLeadFAndI)
    {
        $lead = $websiteLeadFAndI->lead;
        $lead->website->dealer->delete();
        $lead->website->delete();
        $lead->delete();
        $websiteLeadFAndI->delete();

        $this->assertDatabaseMissing(WebsiteFormSubmissions::TABLE_NAME, [
            'id' => $websiteLeadFAndI->getKey(),
        ]);

        $this->assertDatabaseMissing($lead::TABLE_NAME, [
            'identifier' => $lead->getKey(),
        ]);

        $this->assertDatabaseMissing('website', [
            'id' => $lead->website_id,
        ]);

        $this->assertDatabaseMissing('dealer', [
            'dealer_id' => $lead->dealer_id,
        ]);
    }

    private function formSubmissionsAnwers(string $ssn): array
    {
        return [
            '3' => [
                'name' => 'individualOr',
                'order' => '3',
                'text' => 'Individual or Joint Application - (co applicants must each fill out their own application)',
                'type' => 'control_radio',
                'answer' => 'Joint',
            ],
            '5' => [
                'name' => 'productInquiry5',
                'order' => '5',
                'text' => 'Product Inquiry',
                'type' => 'control_textbox',
                'answer' => '2468',
            ],
            '10' => [
                'name' => 'ssn',
                'order' => '10',
                'text' => 'SSN:',
                'type' => 'control_textbox',
                'answer' => $ssn,
            ],
            '11' => [
                'name' => 'maritalStatus',
                'order' => '11',
                'text' => 'Marital Status',
                'type' => 'control_dropdown',
                'answer' => 'Married',
            ],
            '12' => [
                'name' => 'areYou',
                'order' => '12',
                'text' => 'Are you a U.S. Citizen',
                'type' => 'control_dropdown',
                'answer' => 'Yes',
            ],
            '23' => [
                'name' => 'yearsAt',
                'order' => '23',
                'text' => 'Years at current residence',
                'type' => 'control_dropdown',
                'answer' => '2',
            ],
            '24' => [
                'name' => 'monthsAt',
                'order' => '24',
                'text' => 'Months at current residence',
                'type' => 'control_dropdown',
                'answer' => '3',
            ],
            '25' => [
                'name' => 'doYou',
                'order' => '25',
                'text' => 'Do you rent or own',
                'type' => 'control_dropdown',
                'answer' => 'Own',
            ],
            '26' => [
                'name' => 'monthlyRentmtg',
                'order' => '26',
                'text' => 'Monthly Rent/Mtg:',
                'type' => 'control_textbox',
                'answer' => '1501',
            ],
            '27' => [
                'name' => 'landlordOr',
                'order' => '27',
                'text' => 'Landlord or mortgage company',
                'type' => 'control_textbox',
                'answer' => 'Roundpoint Mortgage',
            ],
            '31' => [
                'name' => 'yearsAt44',
                'order' => '31',
                'text' => 'Years at previous residence',
                'type' => 'control_dropdown',
            ],
            '32' => [
                'name' => 'monthsAt45',
                'order' => '32',
                'text' => 'Months at previous residence',
                'type' => 'control_dropdown',
            ],
            '34' => [
                'name' => 'currentEmployer',
                'order' => '34',
                'text' => 'Current employer (Actual Employer):',
                'type' => 'control_textbox',
                'answer' => 'Total Quality Logistics',
            ],
            '35' => [
                'name' => 'grossMonthly',
                'order' => '35',
                'text' => 'Gross monthly income (before taxes):',
                'type' => 'control_textbox',
                'answer' => '2500',
            ],
            '36' => [
                'name' => 'positionoccupation',
                'order' => '36',
                'text' => 'Position/Occupation:',
                'type' => 'control_textbox',
                'answer' => 'Logistics Account Executive',
            ],
            '38' => [
                'name' => 'yearsAt50',
                'order' => '38',
                'text' => 'Years at your job',
                'type' => 'control_dropdown',
                'answer' => 'Years',
            ],
        ];
    }
}
