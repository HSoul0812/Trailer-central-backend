<?php

namespace App\Services\Dms\CVR;

use App\Models\CRM\Dms\UnitSale;
use App\Services\Dms\CVR\DTOs\CVRFileDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\CRM\Dms\CvrCreds;

class CVRGeneratorService implements CVRGeneratorServiceInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(UnitSale $unitSale) : CVRFileDTO
    {
        $writer = new \XMLWriter(); 
        $writer->openMemory();
        $writer->startDocument('1.0'); 
            $writer->startElement('GEN');
                $writer->startElement('CSV');
                    
                    $writer->writeElement('Control_Number', '');
                    $this->writeDealOnContract($writer, $unitSale);
                    $writer->writeElement('VIN', $unitSale->inventory_vin);
                    $writer->writeElement('Amount_Financed', $unitSale->isFinanced() ? $unitSale->amountFinanced() : '');
                    
                    /* 
                     * Start Financing Data that we don't have but may have in some future
                     */
                    $writer->writeElement('Application_Date', '');
                    $writer->writeElement('Application_Equity_from_Trade', '');
                    $writer->writeElement('Application_Fee_for_Title', '');
                    $writer->writeElement('Annual_Percentage_Rate', '');
                    $writer->writeElement('Add_or_Rem_1_Description', '');
                    $writer->writeElement('Add_or_Rem_2_Description', '');
                    $writer->writeElement('Add_or_Rem_3_Description', '');
                    $writer->writeElement('Add_or_Rem_4_Description', '');
                    $writer->writeElement('Vehicle_Sold_As_Is', '');
                    $writer->writeElement('Assignment_Fee', '');
                    $writer->writeElement('Automatic_Transmission', '');
                    /**
                     * End Financing data that we don't have
                     */
                    
                    /**
                     * Start Buyer related data
                     */
                    if ($unitSale->customer) {
                        $writer->writeElement('Buyer_Address', $unitSale->customer->address);
                        $writer->writeElement('Buyer_Age', $unitSale->customer->age ? $unitSale->customer->age : '');
                        $writer->writeElement('Buyer_Age', $unitSale->customer->dob ? $unitSale->customer->dob : '');
                        $writer->writeElement('Buyer_Birth_State', '');
                        $writer->writeElement('Buyer_Birth_Month', $unitSale->customer->dob ? $unitSale->customer->birthMonth : '');
                        $writer->writeElement('Buyer_City', $unitSale->customer->city);
                        $writer->writeElement('Buyer_County', $unitSale->customer->county);
                        $writer->writeElement('Buyer_Drivers_License_Number', $unitSale->customer->drivers_license);
                        $writer->writeElement('Buyer_First_Name_and_Middle_Initial', $unitSale->customer->first_name . ' ' . $unitSale->customer->middle_name);
                        $writer->writeElement('Buyer_Phone', $unitSale->customer->home_phone);
                        $writer->writeElement('Buyer_Last_Name', $unitSale->customer->last_name);
                        $writer->writeElement('Buyer_Middle_Name', $unitSale->customer->middle_name);
                        $writer->writeElement('Buyer_Full_Name', $unitSale->customer->display_name);                    
                        $writer->writeElement('Buyer_Sex', $unitSale->customer->gender);
                        $writer->writeElement('Buyer_SSAN', '');
                        $writer->writeElement('Buyer_State', $unitSale->customer->region);
                        $writer->writeElement('Buyer_Name_Suffix', '');                    
                        $writer->writeElement('Buyer_Work_Phone', $unitSale->customer->work_phone);
                        $writer->writeElement('Buyer_ZIP', $unitSale->customer->postal_code);
                    }
                    
                    /**
                     * End Buyer related data
                     */
                    
                    /**
                     * Start bank data we don't have
                     */
                    $writer->writeElement('Bank_Address', '');                    
                    $writer->writeElement('Bank_City', '');
                    $writer->writeElement('Bank_County', '');
                    $writer->writeElement('Bank_Name', '');
                    $writer->writeElement('Bank_Phone_Number', '');                    
                    $writer->writeElement('Bank_State', '');
                    $writer->writeElement('Bank_Zip', '');
                    $writer->writeElement('Beneficiary_Name', '');                    
                    $writer->writeElement('Beneficiary_Address', '');
                    $writer->writeElement('Beneficiary_City_State_ZIP', '');
                    /**
                     * End bank data we don't have
                     */
                    
                    /**
                     * Co-Buyer start data
                     */
                    $writer->writeElement('Co-Buyer_Address', $unitSale->coCustomer ? $unitSale->coCustomer->address : '');                    
                    $writer->writeElement('Co-Buyer_Age', $unitSale->coCustomer ? $unitSale->coCustomer->age : '');
                    $writer->writeElement('Co-Buyer_Birth_Date', $unitSale->coCustomer ? $unitSale->coCustomer->dob : '');
                    $writer->writeElement('Co-Buyer_Birth_State', '');
                    $writer->writeElement('Co-Buyer_Birth_Month', $unitSale->coCustomer ? $unitSale->coCustomer->birthMonth : '');                    
                    $writer->writeElement('Co-Buyer_City', $unitSale->coCustomer ? $unitSale->coCustomer->city : '');
                    $writer->writeElement('Co-Buyer_County', $unitSale->coCustomer ? $unitSale->coCustomer->county : '');
                    $writer->writeElement('Co-Buyer_Drivers_License_Number', $unitSale->coCustomer ? $unitSale->coCustomer->drivers_license : '');                    
                    $writer->writeElement('Co-Buyer_First_Name', $unitSale->coCustomer ? $unitSale->coCustomer->first_name : '');
                    $writer->writeElement('Co-Buyer_Home_Phone', $unitSale->coCustomer ? $unitSale->coCustomer->home_phone : '');                    
                    $writer->writeElement('Co-Buyer_Last_Name', $unitSale->coCustomer ? $unitSale->coCustomer->last_name : '');
                    $writer->writeElement('Co-Buyer_Full_Middle_Name', $unitSale->coCustomer ? $unitSale->coCustomer->middle_name : '');                    
                    $writer->writeElement('Co-Buyer_Full_Name', $unitSale->coCustomer ? $unitSale->coCustomer->display_name : '');
                    $writer->writeElement('Co-Buyer_Sex', $unitSale->coCustomer ? $unitSale->coCustomer->gender : '');    
                    $writer->writeElement('Co-Buyer_SSAN', '');
                    $writer->writeElement('Co-Buyer_State', $unitSale->coCustomer ? $unitSale->coCustomer->region : '');                    
                    $writer->writeElement('Co-Buyer_Work_Phone', $unitSale->coCustomer ? $unitSale->coCustomer->work_phone : '');
                    $writer->writeElement('Co-Buyer_ZIP', $unitSale->coCustomer ? $unitSale->coCustomer->postal_code : '');
                    /**
                     * Co-Buyer end data
                     */
                    
                    $writer->writeElement('Numeric_Year_from_Deal_Date', Carbon::parse($unitSale->created_at)->format('Y')); 
                    $writer->writeElement('Carline_of_Vehicle_Sold', ''); 
                    $writer->writeElement('Tax_Amount', $unitSale->taxTotal());
                                                          
                    
                    /**
                     * Start Data we don't have
                     */
                    $writer->writeElement('Color_of_Car_Sold', ''); 
                    $writer->writeElement('Color_Code_of_Car_Sold', '');
                    $writer->writeElement('County_Tax_Rate', '');                     
                    $writer->writeElement('Commercial', ''); 
                    $writer->writeElement('Company_Name_of_Dealer', '');
                    $writer->writeElement('Buyers_Company', ''); 
                    $writer->writeElement('Conjuction_in_System_Control_File', ''); 
                    $writer->writeElement('Is_Co-Buyer_Co-Owner', ''); 
                    $writer->writeElement('Cosigner_or_Guarantor_in_Contract', ''); 
                    $writer->writeElement('Co-Signer_on_Contract', ''); 
                    $writer->writeElement('County_Number', ''); 
                    $writer->writeElement('County_Tax_Amount', '');                     
                    $writer->writeElement('Cosigner_Address', ''); 
                    $writer->writeElement('Cosigner_Age', '');                    
                    $writer->writeElement('Cosigner_Birth_Date', ''); 
                    $writer->writeElement('Cosigner_City', ''); 
                    $writer->writeElement('Cosigner_County', ''); 
                    $writer->writeElement('Cosigner_Drivers_License_Number', '');                     
                    $writer->writeElement('Cosigner_First_Name_Middle_Initial', ''); 
                    $writer->writeElement('Cosigner_Home_Phone', '');                     
                    $writer->writeElement('Cosigner_Last_Name', ''); 
                    $writer->writeElement('Cosigner_Sex', '');                     
                    $writer->writeElement('Cosigner_SSAN', ''); 
                    $writer->writeElement('Province', '');    
                    $writer->writeElement('Cosigner_Work_Phone', ''); 
                    $writer->writeElement('Cosigner_ZIP', '');                     
                    $writer->writeElement('City_Tax_Rate', ''); 
                    $writer->writeElement('Total_Cash_Amount-Leases', '');  
                    $writer->writeElement('Customer_Number', '');  
                    $writer->writeElement('Authorized_Agent_for_Dealer', '');  
                    $writer->writeElement('Delivery_Date', ''); 
                    $writer->writeElement('Delivery_Price', '');
                    $writer->writeElement('Diesel_Yes_or_No', ''); 
                    $writer->writeElement('Drivers_License_Expiration_Date', '');
                    $writer->writeElement('Buyers_Drivers_License_State', ''); 
                    $writer->writeElement('Documentation_Fee', '');
                    $writer->writeElement('Employer_Address', ''); 
                    $writer->writeElement('Employer_City', '');
                    $writer->writeElement('Employer_State', '');
                    $writer->writeElement('Employer_ZIP', ''); 
                    $writer->writeElement('Name_of_Buyers_Employer', '');
                    $writer->writeElement('Encumbered_Title_Fee', ''); 
                    $writer->writeElement('Engine_Number_of_Car_Sold', '');
                    $writer->writeElement('Engine_Size_of_Car_Sold', '');
                    $writer->writeElement('Excise_Fee_for_Title_App', ''); 
                    $writer->writeElement('Excise_Tax_Amount', '');
                    $writer->writeElement('Four_by_Four_Yes_or_No', ''); 
                    $writer->writeElement('Four_Wheel_Drive_YN', '');
                    $writer->writeElement('Freight_Charge_on_Car_Sold', '');
                    $writer->writeElement('Front_Wheel_Drive_YN', ''); 
                    $writer->writeElement('Cross_Cap_Cost_of_Vehicle', '');                    
                    $writer->writeElement('Horse_Power_of_Car_Sold', ''); 
                    $writer->writeElement('State_Inspection_Certificate_Number', '');
                    $writer->writeElement('Date_of_State_Inspection', '');
                    $writer->writeElement('Inspection_Fee', ''); 
                    $writer->writeElement('Interior_Color_of_Car_Sold', '');
                    $writer->writeElement('State_Vehicle_was_Last_Registered', ''); 
                    $writer->writeElement('License_Expiratoin_Date', '');
                    $writer->writeElement('License_Fee', '');
                    $writer->writeElement('License_Plate_Number_of_Car_Sold', ''); 
                    $writer->writeElement('Description_of_Lien', '');     
                    $writer->writeElement('Lienholder_Customer_Number', ''); 
                    $writer->writeElement('Type_of_Lien', '');
                    $writer->writeElement('Sticker_Price_of_Vehicle', '');
                    $writer->writeElement('Temporary_Certificate_Number', '');
                    $writer->writeElement('Local_Tax_Rate', ''); 
                    $writer->writeElement('Local_Tax_Amount', '');
                    $writer->writeElement('Luxury_Tax_Amount', ''); 
                    $writer->writeElement('Luxury_Tax_Rate', '');
                    $writer->writeElement('Luxury_Tax_Threshold', '');
                    $writer->writeElement('Luxury_Tax_Flag', ''); 
                    $writer->writeElement('Luxury_Tax_Basis', '');
                    $writer->writeElement('Amout_of_Luxury_Tax', ''); 
                    $writer->writeElement('Make_of_Car_Sold', '');
                    $writer->writeElement('Make_Code', '');
                    $writer->writeElement('Make_Number_of_Car_Sold', ''); 
                    $writer->writeElement('Model_Description_of_Car_Sold', '');                    
                    $writer->writeElement('Model_Number_of_Car_Sold', ''); 
                    $writer->writeElement('Manufacturers_Rebate', '');
                    $writer->writeElement('Year_Vehicle_of_Manufactured', '');
                    $writer->writeElement('Motive_Power-Gas_DSL_Other-of_Car_Sold', ''); 
                    $writer->writeElement('Manufacturer_Cert_of_Orign_Date', '');
                    $writer->writeElement('Number_of_Axles_on_Car_Sold', ''); 
                    $writer->writeElement('Number_of_Doors_on_Car_Sold', '');
                    $writer->writeElement('Number_of_Cylinders_of_Car_Sold', '');
                    $writer->writeElement('Number_of_Passengers', ''); 
                    $writer->writeElement('New_Used_Other-Car_Sold', '');                    
                    $writer->writeElement('Buyer_Occupation', ''); 
                    $writer->writeElement('Odometer_Reading_of_Car_Sold', '');
                    $writer->writeElement('Operator_Address', '');
                    $writer->writeElement('Operator_City', ''); 
                    $writer->writeElement('Operator_Phone_Number', '');
                    $writer->writeElement('Operator_State', ''); 
                    $writer->writeElement('Operator_ZIP', '');
                    $writer->writeElement('Operator_of_Vehicle_if_other_than_buyer', '');
                    $writer->writeElement('Out_of_State_Sale_YN', ''); 
                    $writer->writeElement('PCON_Parm_10_PRICE-Virginia', '');                    
                    $writer->writeElement('PDI_Insurance_Company_Address', ''); 
                    $writer->writeElement('PDI_Agent_Address', '');
                    $writer->writeElement('PDI_Agent_Name', '');
                    $writer->writeElement('PDI_Company_Phone_Number', ''); 
                    $writer->writeElement('PDI_Company_Code_Number', '');
                    $writer->writeElement('PDI_Company_City', ''); 
                    $writer->writeElement('PDI_Effective_Date', '');
                    $writer->writeElement('PDI_Expiration_Date', '');
                    $writer->writeElement('PDI_Insurance_Company_Name', ''); 
                    $writer->writeElement('PDI_Months', '');
                    $writer->writeElement('PDI_Premium', ''); 
                    $writer->writeElement('Physical_Damage_Reserve', '');
                    $writer->writeElement('PDI_Reserve_Rate', '');
                    $writer->writeElement('PDI_Company_State', ''); 
                    $writer->writeElement('PDI_Company_ZIP', '');
                    $writer->writeElement('Car_for_Personal_Business_Agriculture', '');
                    $writer->writeElement('Plate_Num_of_Car_Sold', ''); 
                    $writer->writeElement('PDI_Policy_Number', '');                    
                    $writer->writeElement('Previous_Owner_Address', '');
                    $writer->writeElement('Previous_Owner_City', ''); 
                    $writer->writeElement('Previous_Owner_Name', '');
                    $writer->writeElement('Previous_Owner_State', '');
                    $writer->writeElement('Previous_Owner_ZIP', ''); 
                    $writer->writeElement('Price_or_Cap_Cost_of_Vehicle', '');
                    $writer->writeElement('Property_Tax_Factor', ''); 
                    $writer->writeElement('Property_Tax', '');
                    $writer->writeElement('Registered_Owner_Address', '');
                    $writer->writeElement('Registered_Buyer_BCT-NY', ''); 
                    $writer->writeElement('Registered_Owner_County', '');
                    $writer->writeElement('Registered_Owner_City', '');
                    $writer->writeElement('Registered_Owner_Date_of_Birth', ''); 
                    $writer->writeElement('Registration_Effective_Date', '');
                    $writer->writeElement('Registration_Expiration_Date', '');
                    $writer->writeElement('Registration_Expiration_Year', ''); 
                    $writer->writeElement('Registration_Fee', '');
                    $writer->writeElement('Registered_Owner_Sex', '');
                    $writer->writeElement('Registered_Owner_State', ''); 
                    $writer->writeElement('Registered_State_of_Car_Sold', '');
                    $writer->writeElement('Registration_Type_of_Car_Sold', '');
                    $writer->writeElement('Year_Vehicle_was_First_Registered', ''); 
                    $writer->writeElement('Registered_Owner_ZIP', '');                    
                    $writer->writeElement('Registered_Owner_Other_than_Buyer', '');
                    $writer->writeElement('RTD_Tax_Amount-Colorado', ''); 
                    $writer->writeElement('RTD_Tax_Rate', '');
                    $writer->writeElement('Sales_Tax_Amount', '');
                    $writer->writeElement('Sales_Tax_Rate', ''); 
                    $writer->writeElement('State_Tax_Rate', '');
                    $writer->writeElement('License_Sticker_Number_of_Car_Sold', '');
                    $writer->writeElement('Stock_Number_of_Car_Sold', ''); 
                    $writer->writeElement('Tag_Number_of_Car_Sold', '');                    
                    $writer->writeElement('Tax_Base_Amount', '');
                    $writer->writeElement('Calc_for_Texas', ''); 
                    $writer->writeElement('Taxable_Price-KY', '');
                    $writer->writeElement('Taxable_YN', '');
                    $writer->writeElement('Temporary_Plate_Number_of_Car_Sold', ''); 
                    $writer->writeElement('Temp_Plate_Expiration_Date', '');
                    $writer->writeElement('Title_Fee', '');
                    $writer->writeElement('Date_of_Title', ''); 
                    $writer->writeElement('Title_Number_of_Car_Sold', '');                    
                    $writer->writeElement('State_Car_will_be_Titled_in', '');
                    $writer->writeElement('Title_Type', ''); 
                    $writer->writeElement('Temporary_Tag_Fee', '');
                    $writer->writeElement('Top_Color_of_Car_Sold', '');
                    $writer->writeElement('Total_of_All_Trade-ins', '');                     
                    $writer->writeElement('Trade_License_Sticker_Number', '');
                    $writer->writeElement('Trade_Numbers_That_Have_Tax_Credit', '');
                    $writer->writeElement('Bank_Name_of_Trade_in_1', ''); 
                    $writer->writeElement('Bank_Address_of_Trade_in_1', '');
                    $writer->writeElement('Bank_Phone_Number_of_Trade_in_1', '');
                    $writer->writeElement('Bank_City_of_Trade_in_1', '');
                    $writer->writeElement('Bank_State_of_Trade_in_1', ''); 
                    $writer->writeElement('Bank_ZIP_of_Trade_in_1', '');
                    $writer->writeElement('Carline_of_Trade_in_1', '');
                    $writer->writeElement('Color_of_Trade_in_1', '');
                    $writer->writeElement('Color_Code_of_Trade_in_1', ''); 
                    $writer->writeElement('Number_of_Cylinders_of_Trade_in_1', '');
                    $writer->writeElement('Decal_Number_of_Trade_in_1', ''); 
                    $writer->writeElement('Engine_Number_of_Trade_in_1', '');
                    $writer->writeElement('Engine_Size_of_Trade_in_1', '');
                    $writer->writeElement('Horsepower_of_Trade_in_1', ''); 
                    $writer->writeElement('ID_Number_of_Trade_in_1', '');
                    $writer->writeElement('License_Expiration_Date', ''); 
                    $writer->writeElement('License_Fee_of_Trade_in_1', '');
                    $writer->writeElement('License_Number_of_Trade_in_1', '');    
                    $writer->writeElement('Number_of_Doors_of_Trade_in_1', ''); 
                    $writer->writeElement('Number_of_Passengers_of_Trade_in_1', '');
                    $writer->writeElement('Registration_Number_of_Trade_in_1', '');
                    $writer->writeElement('Registered_State_of_Trade_in_1', ''); 
                    $writer->writeElement('Make_Number_of_Trade_in_1', '');
                    $writer->writeElement('Model_Description_of_Trade_in_1','');
                    $writer->writeElement('Tab_Number_of_Trade_in_1', '');
                    $writer->writeElement('Transmission_Power_of_Trade_in_1', '');
                    $writer->writeElement('Trim_Color_of_Trade_in_1', '');
                    $writer->writeElement('Vehicle_Class_of_Trade_in_1', '');
                    $writer->writeElement('Placeholder_1', '');                    
                    $writer->writeElement('Bank_Name_of_Trade_in_2', ''); 
                    $writer->writeElement('Bank_Address_of_Trade_in_2', '');
                    $writer->writeElement('Bank_Phone_Number_of_Trade_in_2', '');
                    $writer->writeElement('Bank_City_of_Trade_in_2', '');
                    $writer->writeElement('Bank_State_of_Trade_in_2', ''); 
                    $writer->writeElement('Bank_ZIP_of_Trade_in_2', '');
                    $writer->writeElement('Carline_of_Trade_in_2', '');
                    $writer->writeElement('Color_of_Trade_in_2', '');
                    $writer->writeElement('Color_Code_of_Trade_in_2', ''); 
                    $writer->writeElement('Number_of_Cylinders_of_Trade_in_2', '');
                    $writer->writeElement('Decal_Number_of_Trade_in_2', ''); 
                    $writer->writeElement('Engine_Number_of_Trade_in_2', '');
                    $writer->writeElement('Engine_Size_of_Trade_in_2', '');
                    $writer->writeElement('Horsepower_of_Trade_in_2', ''); 
                    $writer->writeElement('ID_Number_of_Trade_in_2', '');
                    $writer->writeElement('License_Fee_of_Trade_in_2', '');
                    $writer->writeElement('License_Number_of_Trade_in_2', '');    
                    $writer->writeElement('Number_of_Doors_of_Trade_in_2', ''); 
                    $writer->writeElement('Number_of_Passengers_of_Trade_in_2', '');
                    $writer->writeElement('Registration_Number_of_Trade_in_2', '');
                    $writer->writeElement('Registered_State_of_Trade_in_2', ''); 
                    $writer->writeElement('Make_Number_of_Trade_in_2', '');
                    $writer->writeElement('Model_Description_of_Trade_in_2','');
                    $writer->writeElement('Tab_Number_of_Trade_in_2', '');
                    $writer->writeElement('Transmission_Power_of_Trade_in_2', '');
                    $writer->writeElement('Trim_Color_of_Trade_in_2', '');
                    $writer->writeElement('Vehicle_Class_of_Trade_in_2', '');                    
                    $writer->writeElement('Bank_Name_of_Trade_in_3', ''); 
                    $writer->writeElement('Bank_Address_of_Trade_in_3', '');
                    $writer->writeElement('Bank_Phone_Number_of_Trade_in_3', '');
                    $writer->writeElement('Bank_City_of_Trade_in_3', '');
                    $writer->writeElement('Bank_State_of_Trade_in_3', ''); 
                    $writer->writeElement('Bank_ZIP_of_Trade_in_3', '');
                    $writer->writeElement('Carline_of_Trade_in_3', '');
                    $writer->writeElement('Color_of_Trade_in_3', '');
                    $writer->writeElement('Color_Code_of_Trade_in_3', ''); 
                    $writer->writeElement('Number_of_Cylinders_of_Trade_in_3', '');
                    $writer->writeElement('Decal_Number_of_Trade_in_3', ''); 
                    $writer->writeElement('Engine_Number_of_Trade_in_3', '');
                    $writer->writeElement('Engine_Size_of_Trade_in_3', '');
                    $writer->writeElement('Horsepower_of_Trade_in_3', ''); 
                    $writer->writeElement('ID_Number_of_Trade_in_3', '');
                    $writer->writeElement('License_Fee_of_Trade_in_3', '');
                    $writer->writeElement('License_Number_of_Trade_in_3', '');    
                    $writer->writeElement('Number_of_Doors_of_Trade_in_3', ''); 
                    $writer->writeElement('Number_of_Passengers_of_Trade_in_3', '');
                    $writer->writeElement('Registration_Number_of_Trade_in_3', '');
                    $writer->writeElement('Registered_State_of_Trade_in_3', ''); 
                    $writer->writeElement('Make_Number_of_Trade_in_3', '');
                    $writer->writeElement('Model_Description_of_Trade_in_3','');
                    $writer->writeElement('Tab_Number_of_Trade_in_3', '');
                    $writer->writeElement('Transmission_Power_of_Trade_in_3', '');
                    $writer->writeElement('Trim_Color_of_Trade_in_3', '');
                    $writer->writeElement('Vehicle_Class_of_Trade_in_3', '');                    
                    $writer->writeElement('Transmission_Type-5SPD_AUTO', ''); 
                    $writer->writeElement('Transfer_Registration_Fee', '');
                    $writer->writeElement('Transferred_Cars_Plate_ID','');
                    $writer->writeElement('Transferred_Plate_Number', '');
                    $writer->writeElement('Transferred_Title_Number_of_Car_Sold', '');
                    $writer->writeElement('Title_Transfer_YN', '');
                    $writer->writeElement('Trip_Color_of_Car_Sold', '');                    
                    $writer->writeElement('Turbo_Charge_Yes_No', ''); 
                    $writer->writeElement('Turbo_Charged_YN', '');
                    $writer->writeElement('Type_of_Deal-Deal_or_Lease','');
                    $writer->writeElement('Vehicle_Classification-Car_or_Truck', '');
                    $writer->writeElement('Vehicle_Gross', '');
                    $writer->writeElement('Weight_Fee_on_Commercial_Vehicle', '');
                    $writer->writeElement('Witness_1', '');
                    $writer->writeElement('Witness_2', '');
                    $writer->writeElement('Weight_of_Car_Sold', '');
                    $writer->writeElement('Year_of_Car_Sold', '');
                    $writer->writeElement('DMVFEE', '');
                    
                    /**
                     * End Data we don't have
                     */
                    
                    $writer->writeElement('Cost_of_Vehicle', $unitSale->costOfPrimaryVehicle); 
                    $writer->writeElement('Deal_Number', $unitSale->id);
                    
                    /**
                     * Start dealer data
                     */
                    $writer->writeElement('Dealer_Name', $unitSale->dealer->name); 
                    $writer->writeElement('Dealer_Address', $unitSale->dealer->locations->first()->address);
                    $writer->writeElement('Dealer_County', $unitSale->dealer->locations->first()->county); 
                    $writer->writeElement('Dealer_City', $unitSale->dealer->locations->first()->city);
                    $writer->writeElement('Dealer_Number_for_Titling', ''); 
                    $writer->writeElement('Dealer_Province', $unitSale->dealer->locations->first()->region);
                    $writer->writeElement('Dealer_State', $unitSale->dealer->locations->first()->region); 
                    $writer->writeElement('Dealer_ZIP', $unitSale->dealer->locations->first()->postalcode);
                    /**
                     * End dealer data
                     */

                    /**
                     * Start Trade In data
                     */
                    if ($unitSale->tradeIn) {
                        $tradeInCount = 1;
                        foreach($unitSale->tradeIn as $tradeIn) {
                            $writer->writeElement("Make_of_of_Trade_in_{$tradeInCount}", $tradeIn->temp_inv_mfg); 
                            $writer->writeElement("Model_Number_of_Trade_in_{$tradeInCount}", $tradeIn->temp_inv_model); 
                            $writer->writeElement("Year_of_Trade_in_{$tradeInCount}", $tradeIn->temp_inv_year);
                            $writer->writeElement("Stock_Number_of_Trade_in_{$tradeInCount}", $tradeIn->temp_inv_stock);                    
                            $writer->writeElement("Title_Number_of_Trade_in_{$tradeInCount}", '');
                            $writer->writeElement("Weight_of_Trade_in_{$tradeInCount}", $tradeIn->temp_inv_weight);
                        }
                    }
                    
                    /**
                     * End Trade In Data
                     */

                    $writer->writeElement("Sales_Person_ID",$unitSale->salesPerson ? $unitSale->salesPerson->id : '');                    
                    $writer->writeElement("Sales_Person_Firstname", $unitSale->salesPerson ? $unitSale->salesPerson->first_name : '');
                    $writer->writeElement("Sales_Person_Lastname", $unitSale->salesPerson ? $unitSale->salesPerson->last_name : '');                    
                    
                $writer->endElement();            
            $writer->endElement();
        $writer->endDocument(); 
        $xml = $writer->flush();
        
        $mappedDealerId = $unitSale->dealer_id;
        $cvrCreds = CvrCreds::where('dealer_id', $unitSale->dealer_id)->first();
        
        if ($cvrCreds) {
            $mappedDealerId = $cvrCreds->cvr_unique_id;
        }
        
        $fileName = "CVR/{$unitSale->id}_{$mappedDealerId}.gen";
        Storage::disk('s3')->put($fileName, $xml);
        return new CVRFileDTO('https://'.env('AWS_BUCKET').'.s3.amazonaws.com/'.$fileName);
    }
    
    private function writeDealOnContract(\XMLWriter &$writer, UnitSale $unitSale) : void
    {
        $carbon = new Carbon($unitSale->created_at);        
        $writer->writeElement('Deal_Date_on_Contract', $carbon->format('mdY'));
    }
}
