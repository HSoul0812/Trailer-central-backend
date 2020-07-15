<?php


namespace App\Models\CRM\Dms;


use App\Models\CRM\User\Customer;
use App\Models\CRM\User\SalesPerson;

/**
 * Interface SaleInterface
 *
 * interface for any model that is considered a sale (e.g. pos sales, unit sales); add methods that apply to all kinds of sale here
 *
 * @package App\Models\CRM\Dms
 */
interface GenericSaleInterface
{
    /**
     * @return SalesPerson
     */
    public function salesPerson();

    /**
     * @return Customer
     */
    public function customer();

    /**
     * total cost to dealer
     * @return float
     */
    public function dealerCost();

    /**
     * unit price x qty, pre-tax, pre-discount
     * @return float
     */
    public function subtotal();

    /**
     * total discount
     * @return float
     */
    public function discount();

    /**
     * Total taxes
     * @return float
     */
    public function taxTotal();

    /**
     * @return mixed
     */
    public function createdAt();
}
