<?php

namespace App\Providers;

use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Account\Payment;
use App\Models\CRM\Dms\FinancingCompany;
use App\Models\CRM\Dms\Refund;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\Dms\ServiceOrder\ServiceItemTechnician;
use App\Models\CRM\Dms\ServiceOrder\Technician;
use App\Models\CRM\Dms\ServiceOrder\Type;
use App\Models\CRM\Dms\TaxCalculator;
use App\Models\Pos\Sale;
use App\Repositories\CRM\Invoice\InvoiceRepository;
use App\Repositories\CRM\Invoice\InvoiceRepositoryInterface;
use App\Repositories\CRM\Payment\PaymentRepository;
use App\Repositories\CRM\Payment\PaymentRepositoryInterface;
use App\Repositories\CRM\User\EmailFolderRepository;
use App\Repositories\CRM\User\EmailFolderRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepository;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\Dms\Docupilot\DocumentTemplatesRepository;
use App\Repositories\Dms\Docupilot\DocumentTemplatesRepositoryInterface;
use App\Repositories\Dms\FinancingCompanyRepository;
use App\Repositories\Dms\FinancingCompanyRepositoryInterface;
use App\Repositories\Dms\PurchaseOrder\PurchaseOrderReceiptRepository;
use App\Repositories\Dms\PurchaseOrder\PurchaseOrderReceiptRepositoryInterface;
use App\Repositories\Dms\Quickbooks\AccountRepository;
use App\Repositories\Dms\Quickbooks\AccountRepositoryInterface;
use App\Repositories\Dms\Quickbooks\BillRepository;
use App\Repositories\Dms\Quickbooks\BillRepositoryInterface;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalDeletedRepositoryInterface;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalDeletedRepository;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepository;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepositoryInterface;
use App\Repositories\Dms\QuoteRepository;
use App\Repositories\Dms\QuoteRepositoryInterface;
use App\Repositories\Dms\QuoteSettingRepository;
use App\Repositories\Dms\QuoteSettingRepositoryInterface;
use App\Repositories\Dms\ServiceOrder\ServiceItemTechnicianRepository;
use App\Repositories\Dms\ServiceOrder\ServiceItemTechnicianRepositoryInterface;
use App\Repositories\Dms\ServiceOrder\ServiceReportRepository;
use App\Repositories\Dms\ServiceOrder\ServiceReportRepositoryInterface;
use App\Repositories\Dms\ServiceOrder\TechnicianRepository;
use App\Repositories\Dms\ServiceOrder\TechnicianRepositoryInterface;
use App\Repositories\Dms\ServiceOrder\TypeRepository;
use App\Repositories\Dms\ServiceOrder\TypeRepositoryInterface;
use App\Repositories\Dms\ServiceOrderRepository;
use App\Repositories\Dms\ServiceOrderRepositoryInterface;
use App\Repositories\Dms\SettingsRepository;
use App\Repositories\Dms\SettingsRepositoryInterface;
use App\Repositories\Dms\TaxCalculatorRepository;
use App\Repositories\Dms\TaxCalculatorRepositoryInterface;
use App\Repositories\Dms\UnitSaleLaborRepository;
use App\Repositories\Dms\UnitSaleLaborRepositoryInterface;
use App\Repositories\Dms\UnitSaleRepository;
use App\Repositories\Dms\UnitSaleRepositoryInterface;
use App\Repositories\Pos\SaleRepository;
use App\Repositories\Pos\SaleRepositoryInterface;
use App\Repositories\Dms\Printer\SettingsRepository as PrinterSettingsRepository;
use App\Repositories\Dms\Printer\SettingsRepositoryInterface as PrinterSettingsRepositoryInterface;
use App\Repositories\Dms\Printer\FormRepository as PrinterFormRepository;
use App\Repositories\Dms\Printer\FormRepositoryInterface as PrinterFormRepositoryInterface;
use App\Repositories\Dms\Quickbooks\BillPaymentRepository;
use App\Repositories\Dms\Quickbooks\BillPaymentRepositoryInterface;
use App\Services\CRM\User\SalesPersonService;
use App\Services\CRM\User\SalesPersonServiceInterface;
use App\Services\Dms\Customer\CustomerService;
use App\Services\Dms\Customer\CustomerServiceInterface;
use App\Services\Dms\CVR\CVRGeneratorService;
use App\Services\Dms\CVR\CVRGeneratorServiceInterface;
use App\Services\Dms\Printer\InstructionsServiceInterface;
use App\Services\Dms\Printer\ZPL\InstructionsService;
use App\Services\Dms\Printer\FormServiceInterface as PrinterFormServiceInterface;
use App\Services\Dms\Printer\ESCP\FormService as PrinterFormService;
use App\Services\Dms\Quote\QuoteSettingService;
use App\Services\Dms\Quote\QuoteSettingServiceInterface;
use App\Services\Dms\UnitSale\UnitSaleService;
use App\Services\Dms\UnitSale\UnitSaleServiceInterface;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class DmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(QuoteRepositoryInterface::class, QuoteRepository::class);
        $this->app->bind(QuoteSettingRepositoryInterface::class, QuoteSettingRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(PurchaseOrderReceiptRepositoryInterface::class, PurchaseOrderReceiptRepository::class);
        $this->app->bind(ServiceOrderRepositoryInterface::class, ServiceOrderRepository::class);
        $this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);
        $this->app->bind(QuickbookApprovalRepositoryInterface::class, QuickbookApprovalRepository::class);
        $this->app->bind(QuickbookApprovalDeletedRepositoryInterface::class, QuickbookApprovalDeletedRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
        $this->app->bind(UnitSaleLaborRepositoryInterface::class, UnitSaleLaborRepository::class);
        $this->app->bind(BillRepositoryInterface::class, BillRepository::class);
        $this->app->bind(BillPaymentRepositoryInterface::class, BillPaymentRepository::class);
        $this->app->bind(PrinterSettingsRepositoryInterface::class, PrinterSettingsRepository::class);
        $this->app->bind(PrinterFormRepositoryInterface::class, PrinterFormRepository::class);
        $this->app->bind(SalesPersonServiceInterface::class, SalesPersonService::class);
        $this->app->bind(InstructionsServiceInterface::class, InstructionsService::class);
        $this->app->bind(PrinterFormServiceInterface::class, PrinterFormService::class);
        $this->app->bind(CVRGeneratorServiceInterface::class, CVRGeneratorService::class);
        $this->app->bind(ServiceReportRepositoryInterface::class, ServiceReportRepository::class);
        $this->app->bind(CustomerServiceInterface::class, CustomerService::class);
        $this->app->bind(UnitSaleRepositoryInterface::class, UnitSaleRepository::class);

        $this->app->bind(DocumentTemplatesRepositoryInterface::class, DocumentTemplatesRepository::class);

        $this->app->bind(SaleRepositoryInterface::class, function () {
            return new SaleRepository(Sale::query());
        });

        $this->app->bind(InvoiceRepositoryInterface::class, function() {
            return new InvoiceRepository(Invoice::query());
        });

        $this->app->bind(SalesPersonRepositoryInterface::class, function() {
            return new SalesPersonRepository(SalesPerson::query());
        });
        $this->app->bind(EmailFolderRepositoryInterface::class, EmailFolderRepository::class);

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

        $this->app->bind(TypeRepositoryInterface::class, function () {
            return new TypeRepository(Type::query());
        });

        $this->app->bind(UnitSaleServiceInterface::class, UnitSaleService::class);
        $this->app->bind(QuoteSettingServiceInterface::class, QuoteSettingService::class);
    }

    public function boot()
    {
        \Validator::extend('document_template_exists', 'App\Rules\Dms\Docupilot\DocumentTemplateExists@passes');

        Relation::morphMap([
            'qb_payment' => Payment::class,
            'crm_pos_sales' => Sale::class,
            'dealer_refunds' => Refund::class,
        ]);
    }
}
