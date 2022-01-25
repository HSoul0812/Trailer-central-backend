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

        $customer = $this->customerRepository->getByEmailOrPhone(
            [
                'email' => $order->customer_email,
                'phone_number' => $order->phone_number,
                'dealer_id' => $order->dealer_id,
            ]
        );

        $names = explode(" ", $order->shipping_name);

        $firstName = $names[0] ?? '';
        unset($names[0]);
        $lastName  = join(" ", $names);

        $displayName = $this->buildDisplayName($firstName, $lastName, $order);

        if (!$customer) {
            $params = [
                'dealer_id' => $order->dealer_id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'display_name' => $displayName,
                'email' => $order->customer_email,
                'cell_phone' => $order->phone_number ?? null,
                'home_phone' => $order->phone_number ?? null,
                'work_phone' => $order->phone_number ?? null,
                'shipping_address' => $order->shipping_address ?? null,
                'address' => $order->shipping_address ?? null,
                'shipping_city' => $order->shipping_city ?? null,
                'city' => $order->shipping_city ?? null,
                'shipping_region' => $order->shipping_region ?? null,
                'region' => $order->shipping_region ?? null,
                'shipping_postal_code' => $order->shipping_zip ?? null,
                'postal_code' => $order->shipping_zip ?? null,
                'shipping_country' => $order->shipping_country ?? null,
                'country' => $order->shipping_country ?? null,
            ];

            $this->customerRepository->create($params);
        }
    }

    /**
     * @param $firstName
     * @param string $lastName
     * @param \App\Models\Ecommerce\CompletedOrder\CompletedOrder $order
     * @return string
     */
    private function buildDisplayName($firstName, string $lastName, \App\Models\Ecommerce\CompletedOrder\CompletedOrder $order): string
    {
        return $firstName . '-' . $lastName . '-' . $order->customer_email;
    }
}
