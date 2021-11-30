<?php
namespace App\Listeners\Ecommerce;


use App\Events\Ecommerce\OrderSuccessfullyPaid;
use App\Repositories\CRM\Customer\CustomerRepositoryInterface;

class CreateCustomerFromOrder
{
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /**
     * CreateCustomerFromOrder constructor.
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function handle(OrderSuccessfullyPaid $partQtyUpdatedEvent)
    {
        $order = $partQtyUpdatedEvent->order;

        $customer = $this->customerRepository->getByEmailOrPhone(['email' => $order->customer_email, 'phone_number' => $order->phone_number]);

        if (!$customer) {
            $params = [
                'dealer_id' => $order->dealer_id,
                'display_name' => $order->shipping_name,
                'email' => $order->customer_email,
                'cell_phone' => $order->phone_number ?? null,
                'shipping_address' => $order->shipping_address ?? null,
                'shipping_city' => $order->shipping_city ?? null,
                'shipping_region' => $order->shipping_region ?? null,
                'shipping_postal_code' => $order->shipping_zip ?? null,
                'shipping_country' => $order->shipping_country ?? null,
            ];

            $this->customerRepository->create($params);
        }
    }
}