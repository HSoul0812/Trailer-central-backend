<?php

declare(strict_types=1);

namespace App\Jobs\Ecommerce;

use App\Contracts\LoggerServiceInterface;
use App\Exceptions\Ecommerce\TextrailSyncException;
use App\Jobs\Job;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;
use App\Services\Ecommerce\DataProvider\Providers\TextrailWithCheckoutInterface;

class SyncOrderJob extends Job
{
    /** @var int */
    private $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @param TextrailWithCheckoutInterface $provider
     * @param CompletedOrderRepositoryInterface $repository
     * @param LoggerServiceInterface $logger
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Exceptions\Ecommerce\TextrailSyncException when some thing goes wrong on Magento side
     */
    public function handle(
        TextrailWithCheckoutInterface     $provider,
        CompletedOrderRepositoryInterface $repository,
        LoggerServiceInterface            $logger): void
    {
        $logger->info(sprintf('Starting order Magento syncer for: %d', $this->orderId));

        $order = $repository->get(['id' => $this->orderId]);

        try {
            $method = $order->ecommerce_customer_id ? 'createOrderFromCart' : 'createOrderFromGuestCart';

            /** @var string $orderId */
            $orderId = $provider->$method($order->ecommerce_cart_id, $order->payment_method);

            $repository->update(['id' => $order->id, 'ecommerce_order_id' => $orderId]);

            $logger->info(
                sprintf('Magento order was successfully create for: %d', $this->orderId),
                ['ecommerce_order_id' => $orderId]
            );
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage());

            throw new TextrailSyncException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
