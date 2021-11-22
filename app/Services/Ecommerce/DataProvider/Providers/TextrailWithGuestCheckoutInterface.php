<?php

namespace App\Services\Ecommerce\DataProvider\Providers;

/**
 * Methods within this interface will have over context, but it is necessary due methods names could collide with those
 * methods from TextrailWithGuestCheckoutInterface
 */
interface TextrailWithGuestCheckoutInterface
{
    public function addItemToGuestCart(array $params, string $quoteId);

    public function createGuestCart();

    /**
     * Method to create guest cart and add item to it, this cart will be used for guest checkout, and ulterior payment and
     * order creation process.
     *
     * @param array $params
     * @return mixed
     */
    public function estimateGuestShipping(array $params);

    public function createOrderFromGuestCart(string $cartId, string $poNumber): string;

    public function addShippingInformationToGuestCart(string $cartId, array $shippingInformation): array;
}
