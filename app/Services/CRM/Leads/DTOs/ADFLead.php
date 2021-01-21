<?php

namespace App\Services\CRM\Leads\DTOs;

use App\Models\CRM\Leads\LeadType;

/**
 * Class ADFLead
 * 
 * @package App\Services\CRM\Leads\DTOs
 */
class ADFLead
{
    /**
     * @var string Set Default Source to ADF
     */
    const DEFAULT_SOURCE = 'ADF';

    /**
     * @var string Dealer ID for ADF Lead
     */
    private $dealerId;

    /**
     * @var string Dealer Location ID for ADF Lead
     */
    private $locationId;

    /**
     * @var string Website ID for ADF Lead
     */
    private $websiteId;

    /**
     * @var string Date Lead Was Requested
     */
    private $requestDate;


    /**
     * @var string First Name for ADF Lead
     */
    private $firstName;

    /**
     * @var string Last Name for ADF Lead
     */
    private $lastName;

    /**
     * @var string Email Address for ADF Lead
     */
    private $email;

    /**
     * @var string Phone Number for ADF Lead
     */
    private $phone;

    /**
     * @var string Comments for ADF Lead
     */
    private $comments;


    /**
     * @var string Street Address for ADF Lead
     */
    private $addrStreet;

    /**
     * @var string City for ADF Lead
     */
    private $addrCity;

    /**
     * @var string State for ADF Lead
     */
    private $addrState;

    /**
     * @var string Zip for ADF Lead
     */
    private $addrZip;


    /**
     * @var string Vehicle ID for ADF Lead
     */
    private $vehicleId;

    /**
     * @var string Vehicle Year for ADF Lead
     */
    private $vehicleYear;

    /**
     * @var string Vehicle Make for ADF Lead
     */
    private $vehicleMake;

    /**
     * @var string Vehicle Model for ADF Lead
     */
    private $vehicleModel;

    /**
     * @var string Vehicle Stock for ADF Lead
     */
    private $vehicleStock;

    /**
     * @var string Vehicle VIN for ADF Lead
     */
    private $vehicleVin;


    /**
     * @var array Vendor ID's Mapped As [source => text]
     */
    private $vendorIds;

    /**
     * @var array Vendor Provider
     */
    private $vendorProvider;

    /**
     * @var array Vendor Name
     */
    private $vendorName;

    /**
     * @var array Vendor Contact Name
     */
    private $vendorContact;

    /**
     * @var array Vendor URL
     */
    private $vendorUrl;

    /**
     * @var array Vendor Email Address
     */
    private $vendorEmail;

    /**
     * @var array Vendor Phone Number
     */
    private $vendorPhone;


    /**
     * @var array Vendor Street Address
     */
    private $vendorAddrStreet;

    /**
     * @var array Vendor City
     */
    private $vendorAddrCity;

    /**
     * @var array Vendor State
     */
    private $vendorAddrState;

    /**
     * @var array Vendor Zip Code
     */
    private $vendorAddrZip;

    /**
     * @var array Vendor Country
     */
    private $vendorAddrCountry;


    /**
     * Return Dealer ID
     * 
     * @return int $this->dealerId
     */
    public function getDealerId(): int
    {
        return $this->dealerId;
    }

    /**
     * Set Dealer ID
     * 
     * @param int $dealerId
     * @return void
     */
    public function setDealerId(int $dealerId): void
    {
        $this->dealerId = $dealerId;
    }


    /**
     * Return Location ID
     * 
     * @return int $this->locationId
     */
    public function getLocationId(): int
    {
        return $this->locationId;
    }

    /**
     * Set Dealer Location ID
     * 
     * @param int $locationId
     * @return void
     */
    public function setLocationId(int $locationId): void
    {
        $this->locationId = $locationId;
    }


    /**
     * Return Website ID
     * 
     * @return int $this->websiteId
     */
    public function getWebsiteId(): int
    {
        return $this->websiteId;
    }

    /**
     * Set Website ID
     * 
     * @param int $websiteId
     * @return void
     */
    public function setWebsiteId(int $websiteId): void
    {
        $this->websiteId = $websiteId;
    }


    /**
     * Return Request Date
     * 
     * @return string $this->requestDate
     */
    public function getRequestDate(): string
    {
        return $this->requestDate;
    }

    /**
     * Set Request Date
     * 
     * @param string $requestDate
     * @return void
     */
    public function setRequestDate(string $requestDate): void
    {
        $this->requestDate = $requestDate;
    }


    /**
     * Return Lead Type
     * 
     * @return string $this->leadType || calculate lead type
     */
    public function getLeadType(): string
    {
        // Calculate Lead Type?
        if(empty($this->leadType)) {
            if(!empty($this->getVehicleId())) {
                $this->setLeadType(LeadType::TYPE_INVENTORY);
            } else {
                $this->setLeadType(LeadType::TYPE_GENERAL);
            }
        }

        // Return Lead Type
        return $this->leadType;
    }

    /**
     * Set Lead Type
     * 
     * @param string $leadType
     * @return void
     */
    public function setLeadType(string $leadType): void
    {
        $this->leadType = $leadType;
    }



    /**
     * Return First Name
     * 
     * @return string $this->firstName
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Set First Name
     * 
     * @param string $firstName
     * @return void
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }


    /**
     * Return Last Name
     * 
     * @return string $this->lastName
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Set Last Name
     * 
     * @param string $lastName
     * @return void
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }


    /**
     * Return Email
     * 
     * @return string $this->email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set Email
     * 
     * @param string $email
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }


    /**
     * Return Preferred Contact
     * 
     * @return string 'phone' if phone exists, 'email' otherwise
     */
    public function getPreferredContact(): string
    {
        return !empty($this->getPhone()) ? 'phone' : (!empty($this->getEmail()) ? 'email' : 'phone');
    }


    /**
     * Return Phone
     * 
     * @return string $this->phone
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * Set Phone
     * 
     * @param string $phone
     * @return void
     */
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }


    /**
     * Return Comments
     * 
     * @return string $this->comments
     */
    public function getComments(): string
    {
        return $this->comments;
    }

    /**
     * Set Comments
     * 
     * @param string $comments
     * @return void
     */
    public function setComments(string $comments): void
    {
        $this->comments = $comments;
    }



    /**
     * Return Street Address
     * 
     * @return string $this->addrStreet
     */
    public function getAddrStreet(): string
    {
        return $this->addrStreet;
    }

    /**
     * Set Street Address
     * 
     * @param string $addrStreet
     * @return void
     */
    public function setAddrStreet(string $addrStreet): void
    {
        $this->addrStreet = $addrStreet;
    }


    /**
     * Return City Address
     * 
     * @return string $this->addrCity
     */
    public function getAddrCity(): string
    {
        return $this->addrCity;
    }

    /**
     * Set City Address
     * 
     * @param string $addrCity
     * @return void
     */
    public function setAddrCity(string $addrCity): void
    {
        $this->addrCity = $addrCity;
    }


    /**
     * Return State Address
     * 
     * @return string $this->addrState
     */
    public function getAddrState(): string
    {
        return $this->addrState;
    }

    /**
     * Set State Address
     * 
     * @param string $addrState
     * @return void
     */
    public function setAddrState(string $addrState): void
    {
        $this->addrState = $addrState;
    }


    /**
     * Return Zip Address
     * 
     * @return string $this->addrZip
     */
    public function getAddrZip(): string
    {
        return $this->addrZip;
    }

    /**
     * Set Zip Address
     * 
     * @param string $addrZip
     * @return void
     */
    public function setAddrZip(string $addrZip): void
    {
        $this->addrZip = $addrZip;
    }



    /**
     * Return Vehicle ID
     * 
     * @return int $this->vehicleId
     */
    public function getVehicleId(): int
    {
        return $this->vehicleId ?: 0;
    }

    /**
     * Set Vehicle ID
     * 
     * @param int $vehicleId
     * @return void
     */
    public function setVehicleId(int $vehicleId): void
    {
        $this->vehicleId = $vehicleId;
    }


    /**
     * Return Vehicle Year
     * 
     * @return string $this->vehicleYear
     */
    public function getVehicleYear(): string
    {
        return $this->vehicleYear;
    }

    /**
     * Set Vehicle Year
     * 
     * @param string $vehicleYear
     * @return void
     */
    public function setVehicleYear(string $vehicleYear): void
    {
        $this->vehicleYear = $vehicleYear;
    }


    /**
     * Return Vehicle Make
     * 
     * @return string $this->vehicleMake
     */
    public function getVehicleMake(): string
    {
        return $this->vehicleMake;
    }

    /**
     * Set Vehicle Make
     * 
     * @param string $vehicleMake
     * @return void
     */
    public function setVehicleMake(string $vehicleMake): void
    {
        $this->vehicleMake = $vehicleMake;
    }


    /**
     * Return Vehicle Model
     * 
     * @return string $this->vehicleModel
     */
    public function getVehicleModel(): string
    {
        return $this->vehicleModel;
    }

    /**
     * Set Vehicle Model
     * 
     * @param string $vehicleModel
     * @return void
     */
    public function setVehicleModel(string $vehicleModel): void
    {
        $this->vehicleModel = $vehicleModel;
    }


    /**
     * Return Vehicle Stock
     * 
     * @return string $this->vehicleStock
     */
    public function getVehicleStock(): string
    {
        return $this->vehicleStock;
    }

    /**
     * Set Vehicle ID
     * 
     * @param string $vehicleStock
     * @return void
     */
    public function setVehicleStock(string $vehicleStock): void
    {
        $this->vehicleStock = $vehicleStock;
    }


    /**
     * Return Vehicle VIN
     * 
     * @return string $this->vehicleVin
     */
    public function getVehicleVin(): string
    {
        return $this->vehicleVin;
    }

    /**
     * Set Vehicle VIN
     * 
     * @param string $vehicleVin
     * @return void
     */
    public function setVehicleVin(string $vehicleVin): void
    {
        $this->vehicleVin = $vehicleVin;
    }


    /**
     * Return Vehicle Filters
     * 
     * @return array filters for inventory model
     */
    public function getVehicleFilters(): array
    {
        // Return VIN Filters
        if(!empty($this->getVehicleVin())) {
            return ['vin' => [$this->getVehicleVin()]];
        }

        // Return Stock Filters
        if(!empty($this->getVehicleStock())) {
            return ['stock' => [$this->getVehicleStock()]];
        }

        // All Filters Exist?
        if(!empty($this->getVehicleYear()) && !empty($this->getVehicleMake()) && !empty($this->getVehicleModel())) {
            // Return Conditions Array
            return [
                'year' => [$this->getVehicleYear()],
                'make' => [$this->getVehicleMake()],
                'model' => [$this->getVehicleModel()]
            ];
        }

        // Return Empty
        return [];
    }



    /**
     * Return Vendor ID's Array
     * 
     * @return array $this->vendorIds Vendor ID's Mapped As [source => text]
     */
    public function getVendorIds(): string
    {
        return $this->vendorIds;
    }

    /**
     * Set Vendor ID's
     * 
     * @param array $vendorIds Vendor ID's Mapped As [source => text]
     * @return void
     */
    public function setVendorIds(array $vendorIds): void
    {
        $this->vendorIds = $vendorIds;
    }

    /**
     * Add Vendor ID
     * 
     * @param string $source Key for the Vendor ID
     * @param int $vendorId ID to set to vendor
     * @return void
     */
    public function addVendorId(string $source, int $vendorId): void
    {
        $this->vendorIds[$source] = $vendorId;
    }


    /**
     * Return Vendor Provider
     * 
     * @return string $this->vendorProvider
     */
    public function getVendorProvider(): string
    {
        return !empty($this->vendorProvider) ? $this->vendorProvider : self::DEFAULT_SOURCE;
    }

    /**
     * Set Vendor Provider
     * 
     * @param string $vendorProvider
     * @return void
     */
    public function setVendorProvider(string $vendorProvider): void
    {
        $this->vendorProvider = $vendorProvider;
    }


    /**
     * Return Vendor Name
     * 
     * @return string $this->vendorName
     */
    public function getVendorName(): string
    {
        return $this->vendorName;
    }

    /**
     * Set Vendor Name
     * 
     * @param string $vendorName
     * @return void
     */
    public function setVendorName(string $vendorName): void
    {
        $this->vendorName = $vendorName;
    }


    /**
     * Return Vendor Contact Name
     * 
     * @return string $this->vendorContact
     */
    public function getVendorContact(): string
    {
        return $this->vendorContact;
    }

    /**
     * Set Vendor Contact Name
     * 
     * @param string $vendorContact
     * @return void
     */
    public function setVendorContact(string $vendorContact): void
    {
        $this->vendorContact = $vendorContact;
    }


    /**
     * Return Vendor URL
     * 
     * @return string $this->vendorUrl
     */
    public function getVendorUrl(): string
    {
        return $this->vendorUrl;
    }

    /**
     * Set Vendor URL
     * 
     * @param string $vendorUrl
     * @return void
     */
    public function setVendorUrl(string $vendorUrl): void
    {
        $this->vendorUrl = $vendorUrl;
    }


    /**
     * Return Vendor Email
     * 
     * @return string $this->vendorEmail
     */
    public function getVendorEmail(): string
    {
        return $this->vendorEmail;
    }

    /**
     * Set Vendor Email
     * 
     * @param string $vendorEmail
     * @return void
     */
    public function setVendorEmail(string $vendorEmail): void
    {
        $this->vendorEmail = $vendorEmail;
    }


    /**
     * Return Vendor Phone
     * 
     * @return string $this->vendorPhone
     */
    public function getVendorPhone(): string
    {
        return $this->vendorPhone;
    }

    /**
     * Set Vendor Phone
     * 
     * @param string $vendorPhone
     * @return void
     */
    public function setVendorPhone(string $vendorPhone): void
    {
        $this->vendorPhone = $vendorPhone;
    }



    /**
     * Return Vendor Street Address
     * 
     * @return string $this->vendorAddrStreet
     */
    public function getVendorAddrStreet(): string
    {
        return $this->vendorAddrStreet;
    }

    /**
     * Set Vendor Street Address
     * 
     * @param string $addrStreet
     * @return void
     */
    public function setVendorAddrStreet(string $addrStreet): void
    {
        $this->vendorAddrStreet = $addrStreet;
    }


    /**
     * Return Vendor City Address
     * 
     * @return string $this->vendorAddrCity
     */
    public function getVendorAddrCity(): string
    {
        return $this->vendorAddrCity;
    }

    /**
     * Set Vendor City Address
     * 
     * @param string $addrCity
     * @return void
     */
    public function setVendorAddrCity(string $addrCity): void
    {
        $this->vendorAddrCity = $addrCity;
    }


    /**
     * Return Vendor State Address
     * 
     * @return string $this->vendorAddrState
     */
    public function getVendorAddrState(): string
    {
        return $this->vendorAddrState;
    }

    /**
     * Set Vendor State Address
     * 
     * @param string $addrState
     * @return void
     */
    public function setVendorAddrState(string $addrState): void
    {
        $this->vendorAddrState = $addrState;
    }


    /**
     * Return Vendor Zip Address
     * 
     * @return string $this->vendorAddrZip
     */
    public function getVendorAddrZip(): string
    {
        return $this->vendorAddrZip;
    }

    /**
     * Set Vendor Zip Address
     * 
     * @param string $addrZip
     * @return void
     */
    public function setVendorAddrZip(string $addrZip): void
    {
        $this->vendorAddrZip = $addrZip;
    }


    /**
     * Return Vendor Country Address
     * 
     * @return string $this->vendorAddrCountry
     */
    public function getVendorAddrCountry(): string
    {
        return $this->vendorAddrCountry;
    }

    /**
     * Set Vendor Country Address
     * 
     * @param string $addrCountry
     * @return void
     */
    public function setVendorAddrCountry(string $addrCountry): void
    {
        $this->vendorAddrCountry = $addrCountry;
    }
}