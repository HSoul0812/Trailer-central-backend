<?php

namespace App\Services\Quickbooks;

use App\Repositories\Parts\VendorRepositoryInterface;
use App\Repositories\Dms\Quickbooks\AccountRepositoryInterface;

/**
 * Class AccountService
 *
 * @package App\Services\Quickbooks
 */
class AccountService
{

    /**
     * @var VendorRepositoryInterface
     */
    private $vendorRepository;

    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    public function __construct(VendorRepositoryInterface $vendorRepository, AccountRepositoryInterface $accountRepository)
    {
        $this->vendorRepository = $vendorRepository;
        $this->accountRepository = $accountRepository;
    }

    public function getFlooringDebtAccount(int $vendorId)
    {
        $vendor = $this->vendorRepository->get(['vendor_id' => $vendorId]);
        $flooringDebutAccName = 'Flooring Debt - ' . $vendor->name;

        return $this->accountRepository->get([
            'dealer_id' => $vendor->dealer_id,
            'name' => $flooringDebutAccName,
            'type' => 'Credit Card'
        ]);
    }
}
