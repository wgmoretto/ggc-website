<?php
declare(strict_types=1);

namespace WebEngine\Plugins;

interface PaymentPluginInterface
{
    /**
     * Process a payment
     */
    public function processPayment(array $data): array;

    /**
     * Handle webhook callback
     */
    public function handleWebhook(array $data): bool;

    /**
     * Get payment URL for redirect
     */
    public function getPaymentUrl(array $data): string;

    /**
     * Verify payment status
     */
    public function verifyPayment(string $transactionId): array;

    /**
     * Refund a payment
     */
    public function refundPayment(string $transactionId, float $amount): bool;

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array;
}
