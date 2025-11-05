<?php
declare(strict_types=1);

namespace WebEngine\Plugins\Stripe;

use WebEngine\Plugins\PluginBase;
use WebEngine\Plugins\PaymentPluginInterface;
use WebEngine\Database\Database;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripePlugin extends PluginBase implements PaymentPluginInterface
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->name = 'Stripe Payment Gateway';
        $this->version = '1.0.0';
        $this->description = 'Accept payments via Stripe with webhook support';
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
            'publishable_key' => '',
            'secret_key' => '',
            'webhook_secret' => '',
            'currency' => 'usd'
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
        try {
            Stripe::setApiKey($this->getConfigValue('secret_key'));

            $username = $data['username'];
            $packageId = $data['package_id'];

            // Get package details
            $sql = "SELECT * FROM GGC_CREDITS_CONFIG WHERE id = ?";
            $package = $this->db->fetch($sql, [$packageId]);

            if (!$package) {
                return ['success' => false, 'error' => 'Package not found'];
            }

            // Create Stripe Checkout Session
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $this->getConfigValue('currency', 'usd'),
                        'product_data' => [
                            'name' => "{$package['credits']} Credits",
                            'description' => "Credits for {$_ENV['SERVER_NAME']}",
                        ],
                        'unit_amount' => (int)($package['price'] * 100), // Stripe uses cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $_ENV['APP_URL'] . '/donation/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $_ENV['APP_URL'] . '/donation/cancel',
                'client_reference_id' => $username,
                'metadata' => [
                    'username' => $username,
                    'credits' => $package['credits'],
                    'package_id' => $packageId
                ]
            ]);

            // Store pending transaction
            $sql = "INSERT INTO GGC_STRIPE_TRANSACTIONS
                    (username, session_id, payment_status, amount, currency, credits, created_at)
                    VALUES (?, ?, 'pending', ?, ?, ?, GETDATE())";

            $this->db->execute($sql, [
                $username,
                $session->id,
                $package['price'],
                $this->getConfigValue('currency', 'usd'),
                $package['credits']
            ]);

            return [
                'success' => true,
                'session_id' => $session->id,
                'payment_url' => $session->url
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getPaymentUrl(array $data): string
    {
        $result = $this->processPayment($data);
        return $result['payment_url'] ?? '';
    }

    public function handleWebhook(array $data): bool
    {
        try {
            $payload = file_get_contents('php://input');
            $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $this->getConfigValue('webhook_secret')
            );

            // Handle different event types
            switch ($event->type) {
                case 'checkout.session.completed':
                    return $this->handleCheckoutCompleted($event->data->object);

                case 'payment_intent.succeeded':
                    return $this->handlePaymentSucceeded($event->data->object);

                case 'payment_intent.payment_failed':
                    return $this->handlePaymentFailed($event->data->object);

                default:
                    return true;
            }
        } catch (SignatureVerificationException $e) {
            error_log('Stripe webhook signature verification failed: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log('Stripe webhook error: ' . $e->getMessage());
            return false;
        }
    }

    private function handleCheckoutCompleted($session): bool
    {
        $sessionId = $session->id;
        $username = $session->metadata->username ?? $session->client_reference_id;
        $credits = (int)($session->metadata->credits ?? 0);

        // Update transaction status
        $sql = "UPDATE GGC_STRIPE_TRANSACTIONS
                SET payment_status = 'completed', payment_intent_id = ?
                WHERE session_id = ?";

        $this->db->execute($sql, [$session->payment_intent, $sessionId]);

        // Add credits to account
        $sql = "UPDATE MEMB_INFO SET credits = ISNULL(credits, 0) + ? WHERE memb___id = ?";
        $this->db->execute($sql, [$credits, $username]);

        // Log credit addition
        $sql = "INSERT INTO GGC_CREDITS_LOGS (username, amount, reason, created_at)
                VALUES (?, ?, 'Stripe Payment', GETDATE())";
        $this->db->execute($sql, [$username, $credits]);

        return true;
    }

    private function handlePaymentSucceeded($paymentIntent): bool
    {
        $paymentIntentId = $paymentIntent->id;

        $sql = "UPDATE GGC_STRIPE_TRANSACTIONS
                SET payment_status = 'succeeded'
                WHERE payment_intent_id = ?";

        return $this->db->execute($sql, [$paymentIntentId]);
    }

    private function handlePaymentFailed($paymentIntent): bool
    {
        $paymentIntentId = $paymentIntent->id;

        $sql = "UPDATE GGC_STRIPE_TRANSACTIONS
                SET payment_status = 'failed'
                WHERE payment_intent_id = ?";

        return $this->db->execute($sql, [$paymentIntentId]);
    }

    public function verifyPayment(string $transactionId): array
    {
        $sql = "SELECT * FROM GGC_STRIPE_TRANSACTIONS WHERE session_id = ?";
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
        try {
            Stripe::setApiKey($this->getConfigValue('secret_key'));

            // Get payment intent ID
            $sql = "SELECT payment_intent_id FROM GGC_STRIPE_TRANSACTIONS WHERE session_id = ?";
            $transaction = $this->db->fetch($sql, [$transactionId]);

            if (!$transaction || !$transaction['payment_intent_id']) {
                return false;
            }

            // Create refund
            \Stripe\Refund::create([
                'payment_intent' => $transaction['payment_intent_id'],
                'amount' => (int)($amount * 100) // Stripe uses cents
            ]);

            // Update transaction status
            $sql = "UPDATE GGC_STRIPE_TRANSACTIONS SET payment_status = 'refunded' WHERE session_id = ?";
            $this->db->execute($sql, [$transactionId]);

            return true;
        } catch (\Exception $e) {
            error_log('Stripe refund error: ' . $e->getMessage());
            return false;
        }
    }

    public function getSupportedCurrencies(): array
    {
        return ['usd', 'eur', 'gbp', 'cad', 'aud', 'brl', 'jpy', 'cny'];
    }

    public function getPublishableKey(): string
    {
        return $this->getConfigValue('publishable_key', '');
    }
}
