<?php


namespace App\Providers;


use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Dms\FinancingCompany;
use App\Models\CRM\Dms\ServiceOrder\ServiceItemTechnician;
use App\Models\CRM\Dms\ServiceOrder\Technician;
use App\Models\CRM\Dms\TaxCalculator;
use App\Models\CRM\User\SalesPerson;
use App\Models\Pos\Sale;
use App\Repositories\CRM\Invoice\InvoiceRepository;
use App\Repositories\CRM\Invoice\InvoiceRepositoryInterface;
use App\Repositories\CRM\Payment\PaymentRepository;
use App\Repositories\CRM\Payment\PaymentRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepository;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\Dms\FinancingCompanyRepository;
use App\Repositories\Dms\FinancingCompanyRepositoryInterface;
use App\Repositories\Dms\PurchaseOrder\PurchaseOrderReceiptRepository;
use App\Repositories\Dms\PurchaseOrder\PurchaseOrderReceiptRepositoryInterface;
use App\Repositories\Dms\Quickbooks\AccountRepository;
use App\Repositories\Dms\Quickbooks\AccountRepositoryInterface;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepository;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepositoryInterface;
use App\Repositories\Dms\QuoteRepository;
use App\Repositories\Dms\QuoteRepositoryInterface;
use App\Repositories\Dms\ServiceOrder\ServiceItemTechnicianRepository;
use App\Repositories\Dms\ServiceOrder\ServiceItemTechnicianRepositoryInterface;
use App\Repositories\Dms\ServiceOrder\TechnicianRepository;
use App\Repositories\Dms\ServiceOrder\TechnicianRepositoryInterface;
use App\Repositories\Dms\ServiceOrderRepository;
use App\Repositories\Dms\ServiceOrderRepositoryInterface;
use App\Repositories\Dms\SettingsRepository;
use App\Repositories\Dms\SettingsRepositoryInterface;
use App\Repositories\Dms\TaxCalculatorRepository;
use App\Repositories\Dms\TaxCalculatorRepositoryInterface;
use App\Repositories\Pos\SaleRepository;
use App\Repositories\Pos\SaleRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class DmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(QuoteRepositoryInterface::class, QuoteRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(PurchaseOrderReceiptRepositoryInterface::class, PurchaseOrderReceiptRepository::class);
        $this->app->bind(ServiceOrderRepositoryInterface::class, ServiceOrderRepository::class);
        $this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);
        $this->app->bind(QuickbookApprovalRepositoryInterface::class, QuickbookApprovalRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);

        $this->app->bind(SaleRepositoryInterface::class, function () {
            return new SaleRepository(Sale::query());
        });

        $this->app->bind(InvoiceRepositoryInterface::class, function() {
            return new InvoiceRepository(Invoice::query());
        });

        $this->app->bind(SalesPersonRepositoryInterface::class, function() {
            return new SalesPersonRepository(SalesPerson::query());
        });

        $this->app->bind(FinancingCompanyRepositoryInterface::class, function() {
            return new FinancingCompanyRepository(FinancingCompany::query());
        });

        $this->app->bind(ServiceItemTechnicianRepositoryInterface::class, function () {
            return new ServiceItemTechnicianRepository(ServiceItemTechnician::query());
        });

        $this->app->bind(TaxCalculatorRepositoryInterface::class, function () {
            return new TaxCalculatorRepository(TaxCalculator::query());
        });

        $this->app->bind(TechnicianRepositoryInterface::class, function () {
            return new TechnicianRepository(Technician::query());
        });
    }
}
