<?php
declare(strict_types=1);

namespace WebEngine\Plugins\PayPal;

use WebEngine\Plugins\PluginBase;
use WebEngine\Plugins\PaymentPluginInterface;
use WebEngine\Database\Database;

class PayPalPlugin extends PluginBase implements PaymentPluginInterface
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->name = 'PayPal Payment Gateway';
        $this->version = '1.0.0';
        $this->description = 'Accept payments via PayPal with IPN webhook support';
        $this->enabled = false;
        $this->config = [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function install(): bool
    {
        $sql = "INSERT INTO GGC_PLUGINS (name, version, enabled, config, installed_at)
                VALUES (?, ?, 0, ?, GETDATE())";

        $config = json_encode([
            'client_id' => '',
            'client_secret' => '',
            'mode' => 'sandbox', // sandbox or live
            'webhook_id' => '',
            'currency' => 'USD'
        ]);

        return $this->db->execute($sql, [$this->name, $this->version, $config]);
    }

    public function uninstall(): bool
    {
        $sql = "DELETE FROM GGC_PLUGINS WHERE name = ?";
        return $this->db->execute($sql, [$this->name]);
    }

    public function enable(): bool
    {
        $sql = "UPDATE GGC_PLUGINS SET enabled = 1 WHERE name = ?";
        $this->enabled = true;
        return $this->db->execute($sql, [$this->name]);
    }

    public function disable(): bool
    {
        $sql = "UPDATE GGC_PLUGINS SET enabled = 0 WHERE name = ?";
        $this->enabled = false;
        return $this->db->execute($sql, [$this->name]);
    }

    public function processPayment(array $data): array
    {
        $username = $data['username'];
        $packageId = $data['package_id'];

        // Get package details
        $sql = "SELECT * FROM GGC_CREDITS_CONFIG WHERE id = ?";
        $package = $this->db->fetch($sql, [$packageId]);

        if (!$package) {
            return ['success' => false, 'error' => 'Package not found'];
        }

        // Generate unique order ID
        $orderId = 'ORDER_' . time() . '_' . uniqid();

        // Store pending transaction
        $sql = "INSERT INTO GGC_PAYPAL_TRANSACTIONS
                (username, transaction_id, payment_status, amount, currency, credits, created_at)
                VALUES (?, ?, 'pending', ?, ?, ?, GETDATE())";

        $this->db->execute($sql, [
            $username,
            $orderId,
            $package['price'],
            $this->getConfigValue('currency', 'USD'),
            $package['credits']
        ]);

        return [
            'success' => true,
            'order_id' => $orderId,
            'payment_url' => $this->getPaymentUrl([
                'order_id' => $orderId,
                'amount' => $package['price'],
                'description' => "{$package['credits']} Credits",
                'username' => $username
            ])
        ];
    }

    public function getPaymentUrl(array $data): string
    {
        $mode = $this->getConfigValue('mode', 'sandbox');
        $baseUrl = $mode === 'sandbox'
            ? 'https://www.sandbox.paypal.com/cgi-bin/webscr'
            : 'https://www.paypal.com/cgi-bin/webscr';

        $params = [
            'cmd' => '_xclick',
            'business' => $this->getConfigValue('business_email'),
            'item_name' => $data['description'],
            'amount' => $data['amount'],
            'currency_code' => $this->getConfigValue('currency', 'USD'),
            'custom' => $data['username'],
            'invoice' => $data['order_id'],
            'notify_url' => $_ENV['APP_URL'] . '/api/plugins/paypal/webhook',
            'return' => $_ENV['APP_URL'] . '/donation/success',
            'cancel_return' => $_ENV['APP_URL'] . '/donation/cancel'
        ];

        return $baseUrl . '?' . http_build_query($params);
    }

    public function handleWebhook(array $data): bool
    {
        // Verify IPN with PayPal
        if (!$this->verifyIPN($data)) {
            return false;
        }

        $paymentStatus = $data['payment_status'] ?? '';
        $transactionId = $data['txn_id'] ?? '';
        $orderId = $data['invoice'] ?? '';
        $username = $data['custom'] ?? '';
        $amount = (float)($data['mc_gross'] ?? 0);

        // Update transaction status
        $sql = "UPDATE GGC_PAYPAL_TRANSACTIONS
                SET payment_status = ?, transaction_id = ?, payer_email = ?
                WHERE transaction_id = ?";

        $this->db->execute($sql, [
            strtolower($paymentStatus),
            $transactionId,
            $data['payer_email'] ?? '',
            $orderId
        ]);

        // If payment completed, add credits
        if (strtolower($paymentStatus) === 'completed') {
            // Get credits amount
            $sql = "SELECT credits FROM GGC_PAYPAL_TRANSACTIONS WHERE transaction_id = ?";
            $transaction = $this->db->fetch($sql, [$orderId]);

            if ($transaction) {
                // Add credits to account
                $sql = "UPDATE MEMB_INFO SET credits = ISNULL(credits, 0) + ? WHERE memb___id = ?";
                $this->db->execute($sql, [$transaction['credits'], $username]);

                // Log credit addition
                $sql = "INSERT INTO GGC_CREDITS_LOGS (username, amount, reason, created_at)
                        VALUES (?, ?, 'PayPal Payment', GETDATE())";
                $this->db->execute($sql, [$username, $transaction['credits']]);
            }
        }

        return true;
    }

    private function verifyIPN(array $data): bool
    {
        $mode = $this->getConfigValue('mode', 'sandbox');
        $url = $mode === 'sandbox'
            ? 'https://www.sandbox.paypal.com/cgi-bin/webscr'
            : 'https://www.paypal.com/cgi-bin/webscr';

        $data['cmd'] = '_notify-validate';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Connection: Close']);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response === 'VERIFIED';
    }

    public function verifyPayment(string $transactionId): array
    {
        $sql = "SELECT * FROM GGC_PAYPAL_TRANSACTIONS WHERE transaction_id = ?";
        $transaction = $this->db->fetch($sql, [$transactionId]);

        if (!$transaction) {
            return ['success' => false, 'error' => 'Transaction not found'];
        }

        return [
            'success' => true,
            'status' => $transaction['payment_status'],
            'amount' => $transaction['amount'],
            'credits' => $transaction['credits']
        ];
    }

    public function refundPayment(string $transactionId, float $amount): bool
    {
        // Would need PayPal API credentials to implement actual refund
        // For now, just mark as refunded in database
        $sql = "UPDATE GGC_PAYPAL_TRANSACTIONS SET payment_status = 'refunded' WHERE transaction_id = ?";
        return $this->db->execute($sql, [$transactionId]);
    }

    public function getSupportedCurrencies(): array
    {
        return ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'BRL'];
    }
}
