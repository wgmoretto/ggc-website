# WebEngine CMS - Plugin System

The WebEngine CMS includes a flexible plugin system that allows you to extend functionality with payment gateways and other features.

## Available Plugins

### 1. PayPal Payment Gateway

Accept payments via PayPal with IPN (Instant Payment Notification) webhook support.

**Features:**
- PayPal Standard Checkout
- IPN webhook for automatic payment verification
- Support for multiple currencies (USD, EUR, GBP, CAD, AUD, BRL)
- Sandbox mode for testing
- Automatic credit addition on successful payment

**Configuration:**
1. Install the plugin from Admin → Plugins
2. Configure with your PayPal business email
3. Set mode to `sandbox` for testing or `live` for production
4. Choose your currency
5. Configure IPN webhook URL in your PayPal account:
   ```
   https://yoursite.com/api/plugins/PayPal/webhook
   ```

**Environment Variables:**
No additional environment variables required. Configuration is done through the admin panel.

---

### 2. Stripe Payment Gateway

Accept credit card payments with Stripe Checkout.

**Features:**
- Stripe Checkout Session (embedded payment form)
- Webhook support for payment notifications
- Support for multiple currencies (USD, EUR, GBP, CAD, AUD, BRL, JPY, CNY)
- PCI-compliant (Stripe handles all sensitive data)
- Automatic credit addition on successful payment
- Refund support

**Configuration:**
1. Install the plugin from Admin → Plugins
2. Get your API keys from [Stripe Dashboard](https://dashboard.stripe.com/apikeys)
3. Configure:
   - **Publishable Key**: Your Stripe publishable key (pk_...)
   - **Secret Key**: Your Stripe secret key (sk_...)
   - **Webhook Secret**: Your webhook signing secret (whsec_...)
   - **Currency**: Choose your preferred currency
4. Add webhook endpoint in Stripe Dashboard:
   ```
   https://yoursite.com/api/plugins/Stripe/webhook
   ```
   Enable these events:
   - `checkout.session.completed`
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`

**Environment Variables:**
No additional environment variables required. Configuration is done through the admin panel.

---

## Plugin Management

### Install a Plugin

1. Go to **Admin Panel** → **Plugins**
2. Find the plugin you want to install
3. Click **Install**
4. Configure the plugin with your API credentials
5. Click **Enable** to activate

### Configure a Plugin

1. Go to **Admin Panel** → **Plugins**
2. Click **Configure** on the installed plugin
3. Enter your API credentials and settings
4. Click **Save Configuration**

### Enable/Disable a Plugin

- **Enable**: Click the **Enable** button next to the plugin
- **Disable**: Click the **Disable** button next to the plugin

Note: Disabling a plugin will stop it from processing new payments but will not delete existing transaction data.

### Uninstall a Plugin

1. **Disable** the plugin first
2. Click **Uninstall**
3. Confirm the action

**Warning**: Uninstalling will remove the plugin configuration from the database.

---

## Using Payment Plugins

Once a payment plugin is installed and enabled, it will automatically be available on the donation page.

**User Flow:**
1. User visits `/donation`
2. User selects a credit package
3. User chooses payment method (PayPal or Stripe)
4. User completes payment on the payment gateway
5. Webhook automatically adds credits to user account
6. User receives confirmation

---

## Testing Payments

### PayPal Sandbox Testing

1. Create a PayPal developer account at https://developer.paypal.com/
2. Create sandbox test accounts (buyer and seller)
3. Set plugin mode to `sandbox`
4. Use sandbox credentials for testing
5. Test credit card: Use PayPal sandbox test accounts

### Stripe Testing

1. Use Stripe test mode API keys (they start with `pk_test_` and `sk_test_`)
2. Test credit cards:
   - **Success**: `4242 4242 4242 4242`
   - **Decline**: `4000 0000 0000 0002`
   - **3D Secure**: `4000 0025 0000 3155`
   - Any future expiry date (e.g., 12/34)
   - Any 3-digit CVC
   - Any ZIP code

---

## Database Tables

### PayPal Transactions
```sql
GGC_PAYPAL_TRANSACTIONS
- id (PK)
- username
- transaction_id (PayPal transaction ID)
- payment_status (pending, completed, refunded)
- amount
- currency
- credits
- payer_email
- created_at
```

### Stripe Transactions
```sql
GGC_STRIPE_TRANSACTIONS
- id (PK)
- username
- session_id (Stripe Checkout Session ID)
- payment_intent_id (Stripe Payment Intent ID)
- payment_status (pending, completed, succeeded, failed, refunded)
- amount
- currency
- credits
- customer_email
- created_at
```

### Plugin Registry
```sql
GGC_PLUGINS
- id (PK)
- name
- version
- enabled
- config (JSON)
- installed_at
```

---

## Webhook Security

### PayPal IPN Verification
PayPal IPN messages are automatically verified by sending them back to PayPal for validation. Only verified messages are processed.

### Stripe Webhook Signature
Stripe webhooks are verified using the webhook signing secret. The signature is validated before processing any events.

**Important**: Always use HTTPS in production to ensure webhook data is encrypted in transit.

---

## Troubleshooting

### PayPal Issues

**Credits not added after payment:**
1. Check that IPN is configured correctly in PayPal settings
2. Verify the IPN URL matches: `https://yoursite.com/api/plugins/PayPal/webhook`
3. Check server logs at `storage/logs/app.log`
4. Verify payment status in `GGC_PAYPAL_TRANSACTIONS` table
5. Test IPN with PayPal's IPN Simulator

**Payments redirecting to wrong URL:**
1. Verify `APP_URL` in `.env` is set correctly
2. Check that mode is set to correct value (sandbox/live)

### Stripe Issues

**Webhook not receiving events:**
1. Verify webhook URL is added in Stripe Dashboard
2. Check that webhook secret is configured correctly
3. Verify events are enabled: `checkout.session.completed`, `payment_intent.succeeded`
4. Check server logs at `storage/logs/app.log`
5. Use Stripe Dashboard's webhook testing tool

**Payment succeeds but credits not added:**
1. Check `GGC_STRIPE_TRANSACTIONS` table for payment status
2. Verify webhook events are being received
3. Check that the plugin is enabled
4. Review server error logs

---

## Security Best Practices

1. **Always use HTTPS** in production
2. **Keep API keys secret** - never commit them to version control
3. **Use test/sandbox mode** during development
4. **Verify webhook signatures** (automatically done by plugins)
5. **Monitor transaction logs** regularly
6. **Set up webhook retry logic** in payment provider dashboard
7. **Regularly update** Stripe SDK via `composer update`

---

## Support

For issues with:
- **PayPal**: Visit https://developer.paypal.com/support/
- **Stripe**: Visit https://support.stripe.com/
- **Plugin System**: Open an issue on GitHub

---

## Extending the Plugin System

### Creating a New Plugin

1. Create a new class extending `PluginBase`
2. Implement required methods:
   ```php
   abstract public function getName(): string;
   abstract public function getVersion(): string;
   abstract public function getDescription(): string;
   abstract public function install(): bool;
   abstract public function uninstall(): bool;
   abstract public function enable(): bool;
   abstract public function disable(): bool;
   ```

3. For payment plugins, implement `PaymentPluginInterface`:
   ```php
   public function processPayment(array $data): array;
   public function handleWebhook(array $data): bool;
   public function getPaymentUrl(array $data): string;
   public function verifyPayment(string $transactionId): array;
   public function refundPayment(string $transactionId, float $amount): bool;
   public function getSupportedCurrencies(): array;
   ```

4. Register your plugin in `PluginManager::registerPlugins()`

5. Create database tables in the `install()` method

---

## License

The plugin system is part of WebEngine CMS and is licensed under the MIT License.
