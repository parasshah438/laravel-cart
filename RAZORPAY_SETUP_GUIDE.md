# 💳 RAZORPAY PAYMENT SETUP GUIDE

This guide will help you set up Razorpay payment integration in your Laravel e-commerce application.

## 🔧 Environment Configuration

Add the following variables to your `.env` file:

```env
# Razorpay Payment Configuration
RAZORPAY_KEY=your_razorpay_key_id
RAZORPAY_SECRET=your_razorpay_key_secret
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret
RAZORPAY_SKIP_SSL_VERIFICATION=false
```

## 🚀 Getting Razorpay Credentials

### Step 1: Create Razorpay Account
1. Go to [https://razorpay.com](https://razorpay.com)
2. Sign up for a new account or login to existing account
3. Complete KYC verification for live payments

### Step 2: Get API Keys
1. Login to Razorpay Dashboard
2. Go to **Settings** → **API Keys**
3. Generate new API keys for your application
4. Copy the **Key ID** and **Key Secret**

### Step 3: Test Mode Setup
For testing purposes, you can use Razorpay test mode:

```env
# Test Mode Configuration
RAZORPAY_KEY=rzp_test_xxxxxxxxxx
RAZORPAY_SECRET=your_test_secret_key
RAZORPAY_WEBHOOK_SECRET=your_test_webhook_secret
RAZORPAY_SKIP_SSL_VERIFICATION=true
```

### Step 4: Live Mode Setup
For production use:

```env
# Live Mode Configuration
RAZORPAY_KEY=rzp_live_xxxxxxxxxx
RAZORPAY_SECRET=your_live_secret_key
RAZORPAY_WEBHOOK_SECRET=your_live_webhook_secret
RAZORPAY_SKIP_SSL_VERIFICATION=false
```

## 🔒 Webhook Configuration

### Setup Webhook URL
1. In Razorpay Dashboard, go to **Settings** → **Webhooks**
2. Create a new webhook with URL: `https://yourdomain.com/webhook/razorpay`
3. Select events to listen for:
   - `payment.captured`
   - `payment.failed`
   - `order.paid`
4. Copy the webhook secret and add to `.env`

### Local Development Webhook Testing
For local testing, use ngrok or similar tool:

```bash
# Install ngrok
npm install -g ngrok

# Expose your local server
ngrok http 8000

# Use the ngrok URL for webhook
# Example: https://abc123.ngrok.io/webhook/razorpay
```

## ✅ Testing the Integration

### Test Payment Flow
1. Add items to cart
2. Go to checkout
3. Select "Online Payment (Razorpay)"
4. Use test card details:
   - **Card Number**: 4111 1111 1111 1111
   - **Expiry**: Any future date
   - **CVV**: Any 3 digits
   - **Name**: Any name

### Test API Connection
```bash
# Test Razorpay connection
curl -u rzp_test_xxxxxxxxxx:your_test_secret_key \
  -X GET https://api.razorpay.com/v1/payments
```

Or visit: `http://127.0.0.1:8000/payment/test-connection`

## 🎯 Payment Methods Supported

- **Credit/Debit Cards**: Visa, MasterCard, RuPay, American Express
- **Net Banking**: All major banks
- **UPI**: Google Pay, PhonePe, Paytm, BHIM
- **Wallets**: Paytm, Mobikwik, Ola Money, etc.
- **EMI**: Available for cards and banks

## 🔍 Logs and Debugging

All payment activities are logged in:
- `storage/logs/laravel.log`
- Razorpay Dashboard → **Payments** section

### Enable Debug Mode
```env
# Add to .env for detailed logs
APP_DEBUG=true
LOG_LEVEL=debug
```

### Check Payment Status
```bash
# Using artisan tinker
php artisan tinker

# Check order payment status
$order = App\Models\Order::where('order_number', 'ORDxxxxxxxxx')->first();
echo $order->payment_status;
echo $order->razorpay_payment_id;
```

## 🛡️ Security Best Practices

1. **Never expose secret keys** in frontend code
2. **Verify webhook signatures** (already implemented)
3. **Use HTTPS** in production
4. **Store sensitive data securely**
5. **Regular key rotation** for production

## 📱 Mobile/Responsive Support

The payment interface is fully responsive and supports:
- Mobile browsers
- Progressive Web Apps (PWA)
- Native app integrations

## 🚨 Common Issues & Solutions

### Issue 1: SSL Certificate Error
```env
# Temporary fix for local development
RAZORPAY_SKIP_SSL_VERIFICATION=true
```

### Issue 2: Payment Stuck in Pending
- Check webhook configuration
- Verify webhook URL is accessible
- Check logs for webhook signature verification

### Issue 3: Payment Failed but Order Created
- Orders are created before payment for tracking
- Failed payments are marked appropriately
- No charges occur for failed payments

## 📞 Support

For Razorpay related issues:
- **Documentation**: [https://razorpay.com/docs](https://razorpay.com/docs)
- **Support Email**: support@razorpay.com
- **Dashboard**: [https://dashboard.razorpay.com](https://dashboard.razorpay.com)

For application issues:
- Check `storage/logs/laravel.log`
- Enable debug mode for detailed errors
- Test in Razorpay test mode first

## 🎉 Going Live Checklist

- [ ] KYC verification completed in Razorpay
- [ ] Live API keys generated and configured
- [ ] Webhook URL updated to production domain
- [ ] SSL certificate installed
- [ ] Test transactions completed successfully
- [ ] Payment confirmation emails working
- [ ] Order tracking system verified
- [ ] Refund process tested
- [ ] Security audit completed

---

**🔐 Security Notice**: Keep your Razorpay secret keys secure and never commit them to version control. Use environment variables and secure deployment practices.