# ShipRocket Complete Setup Guide - From Zero to Production 🚀

## Table of Contents
1. [ShipRocket Account Creation](#1-shiprocket-account-creation)
2. [Plans & Pricing](#2-plans--pricing)
3. [Account Verification & Setup](#3-account-verification--setup)
4. [API Keys & Integration](#4-api-keys--integration)
5. [Business Setup](#5-business-setup)
6. [Testing & Go Live](#6-testing--go-live)
7. [Startup Business Benefits](#7-startup-business-benefits)
8. [Complete Workflow](#8-complete-workflow)

---

## 1. ShipRocket Account Creation

### Step 1: Visit ShipRocket Website
- Go to: https://www.shiprocket.in/
- Click "Start Shipping" or "Sign Up" button

### Step 2: Choose Account Type
```
Business Options:
├── Individual Seller
├── Small Business (Recommended for Startups)
├── Enterprise
└── Marketplace Seller
```

### Step 3: Registration Form
```
Required Information:
├── Business Name
├── Email Address (Business Email Preferred)
├── Phone Number
├── Business Type (E-commerce/Retail/Manufacturer)
├── Monthly Shipping Volume
├── Business Registration Details
└── GST Number (if applicable)
```

### Step 4: Email Verification
- Check your email for verification link
- Click to verify and activate account

---

## 2. Plans & Pricing

### Available Plans (2024-2025)

#### 🆓 **Starter Plan (Free)**
```
Cost: ₹0/month
Features:
├── 10 Free Shipments/month
├── Basic Dashboard Access
├── Standard Courier Partners
├── Email Support
├── Basic Analytics
└── Manual Order Processing

Limitations:
├── Limited courier options
├── No priority support
├── Basic features only
└── Manual processes
```

#### 💼 **Growth Plan**
```
Cost: ₹999/month
Features:
├── Unlimited Shipments
├── Premium Courier Partners
├── API Access (Full Integration)
├── Advanced Analytics
├── Priority Email Support
├── Automated Workflows
├── Custom Branding
├── COD Reconciliation
├── Return Management
└── Inventory Management

Best For: Growing E-commerce businesses
```

#### 🚀 **Pro Plan**
```
Cost: ₹2,999/month
Features:
├── Everything in Growth Plan
├── Dedicated Account Manager
├── Phone Support
├── Advanced Integrations
├── Custom Reporting
├── White Label Solution
├── Priority Processing
├── Advanced Fraud Protection
└── Custom Pickup Scheduling

Best For: Established businesses with high volume
```

#### 🏢 **Enterprise Plan**
```
Cost: Custom Pricing
Features:
├── Everything in Pro Plan
├── Custom Integrations
├── On-premise Solutions
├── 24/7 Support
├── Custom SLA
├── Advanced Security
├── Multi-location Management
└── Dedicated Infrastructure

Best For: Large enterprises
```

### 📋 **Recommended Plan for Startups**
**Growth Plan (₹999/month)** - Best value for money with all essential features

---

## 3. Account Verification & Setup

### Step 1: Complete KYC (Know Your Customer)
```
Required Documents:
├── PAN Card
├── Aadhaar Card
├── Business Registration Certificate
├── GST Certificate (if applicable)
├── Bank Account Details
├── Address Proof
└── Cancelled Cheque
```

### Step 2: Business Profile Setup
```
Profile Information:
├── Company Logo
├── Business Address
├── Pickup Addresses
├── Return Address
├── Business Category
├── Product Categories
└── Shipping Preferences
```

### Step 3: Bank Account Verification
- Add bank account for COD settlements
- Verify with micro deposits
- Setup automatic settlements

### Step 4: GST Setup (if applicable)
- Add GST number
- Configure tax settings
- Setup invoice generation

---

## 4. API Keys & Integration

### Step 1: Generate API Credentials

#### Access API Section
1. Login to ShipRocket Dashboard
2. Go to **Settings** → **API**
3. Click **"Generate API Token"**

#### Get Your Credentials
```
API Credentials:
├── Email: your-shiprocket-email@example.com
├── Password: your-shiprocket-password
├── API Token: sr_live_xxxxxxxxxxxxxxxxxxxxx
├── Channel ID: 123456
└── API Base URL: https://apiv2.shiprocket.in/v1/external/
```

### Step 2: Test API Connection
```bash
# Test Authentication
curl -X POST "https://apiv2.shiprocket.in/v1/external/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "your-email@example.com",
    "password": "your-password"
  }'
```

### Step 3: Laravel Environment Setup
```env
# Add to your .env file
SHIPROCKET_EMAIL=your-shiprocket-email@example.com
SHIPROCKET_PASSWORD=your-shiprocket-password
SHIPROCKET_TOKEN=sr_live_xxxxxxxxxxxxxxxxxxxxx
SHIPROCKET_CHANNEL_ID=123456
SHIPROCKET_BASE_URL=https://apiv2.shiprocket.in/v1/external/
SHIPROCKET_WEBHOOK_SECRET=your-webhook-secret
```

---

## 5. Business Setup

### Step 1: Configure Pickup Locations
```
Pickup Setup:
├── Primary Warehouse Address
├── Multiple Pickup Locations (if needed)
├── Pickup Time Slots
├── Special Instructions
└── Contact Details
```

### Step 2: Setup Courier Partners
```
Available Courier Partners:
├── Blue Dart
├── DTDC
├── Delhivery
├── Ecom Express
├── Xpressbees
├── Shadowfax (Hyperlocal)
├── Ekart (Flipkart)
└── Amazon Shipping
```

### Step 3: Configure Shipping Rules
```
Shipping Configuration:
├── Weight-based Pricing
├── Zone-based Pricing
├── COD Charges
├── Fuel Surcharge
├── Handling Charges
├── Insurance Options
└── Delivery Attempts
```

### Step 4: Setup Return Process
```
Return Management:
├── Return Policy
├── Return Pickup Addresses
├── RTO (Return to Origin) Settings
├── Refund Processing
└── Return Tracking
```

---

## 6. Testing & Go Live

### Step 1: Sandbox Testing
```php
// Test Order Creation
$testOrder = [
    'order_id' => 'TEST001',
    'order_date' => '2024-12-01',
    'pickup_location' => 'Primary',
    'billing_customer_name' => 'Test Customer',
    'billing_address' => 'Test Address',
    'billing_city' => 'Mumbai',
    'billing_pincode' => '400001',
    'billing_state' => 'Maharashtra',
    'billing_country' => 'India',
    'billing_email' => 'test@example.com',
    'billing_phone' => '9999999999',
    'shipping_is_billing' => true,
    'order_items' => [
        [
            'name' => 'Test Product',
            'sku' => 'TEST-SKU',
            'units' => 1,
            'selling_price' => 100
        ]
    ],
    'payment_method' => 'COD',
    'sub_total' => 100,
    'length' => 10,
    'breadth' => 10,
    'height' => 10,
    'weight' => 0.5
];
```

### Step 2: Live Testing Checklist
- [ ] Test order creation
- [ ] Verify pickup scheduling
- [ ] Check tracking updates
- [ ] Test webhook delivery
- [ ] Verify COD settlements
- [ ] Test return process

### Step 3: Go Live Process
1. Switch from sandbox to production API
2. Update API endpoints in code
3. Configure production webhooks
4. Setup monitoring and alerts
5. Train customer support team

---

## 7. Startup Business Benefits

### 🎯 **Why ShipRocket for Startups?**

#### Cost Benefits
```
Startup Advantages:
├── No setup fees
├── Pay-per-use model
├── Negotiated courier rates
├── Reduced operational costs
├── No infrastructure investment
└── Scalable pricing
```

#### Business Growth Features
```
Growth Support:
├── Multi-channel selling
├── Marketplace integrations
├── Inventory management
├── Analytics & insights
├── Customer communication
└── Brand building tools
```

#### Technical Benefits
```
Tech Advantages:
├── Easy API integration
├── Pre-built plugins
├── Webhook support
├── Real-time tracking
├── Automated workflows
└── Mobile app access
```

### 💰 **Cost Comparison for Startups**

#### Traditional Shipping Setup
```
Initial Investment:
├── Warehouse Setup: ₹5,00,000+
├── Staff Hiring: ₹2,00,000/month
├── Vehicle/Transportation: ₹3,00,000+
├── Technology Setup: ₹1,00,000+
├── Courier Partnerships: ₹50,000+
└── Total First Year: ₹15,00,000+
```

#### ShipRocket Setup
```
Investment:
├── Setup Cost: ₹0
├── Monthly Subscription: ₹999
├── Per Shipment Cost: ₹25-₹150
├── Technology Integration: ₹0 (DIY)
└── Total First Year: ₹12,000+ (based on volume)

Savings: 90%+ cost reduction
```

---

## 8. Complete Workflow

### Phase 1: Pre-Launch (Week 1-2)
```
Day 1-3: Account Setup
├── Create ShipRocket account
├── Complete KYC verification
├── Choose and purchase plan
└── Setup business profile

Day 4-7: Technical Integration
├── Get API credentials
├── Integrate with Laravel app
├── Configure webhook endpoints
└── Setup environment variables

Day 8-14: Testing & Configuration
├── Test API endpoints
├── Configure shipping rules
├── Setup pickup locations
├── Test order workflows
└── Train team members
```

### Phase 2: Launch (Week 3)
```
Day 15-17: Soft Launch
├── Process first test orders
├── Monitor system performance
├── Handle any issues
└── Gather feedback

Day 18-21: Full Launch
├── Go live with all products
├── Monitor order volumes
├── Track customer satisfaction
└── Optimize based on data
```

### Phase 3: Optimization (Ongoing)
```
Monthly Tasks:
├── Review shipping performance
├── Analyze cost vs revenue
├── Optimize courier selection
├── Update shipping rules
├── Customer feedback review
└── Plan for scaling
```

---

## 🔧 Laravel Integration Commands

### Run These Commands After Setup:
```bash
# Run migrations for shipping tables
php artisan migrate

# Seed shipping data
php artisan db:seed --class=ShippingSeeder

# Start queue worker for background jobs
php artisan queue:work

# Test ShipRocket connection
php artisan tinker
>>> $service = new App\Services\ShipRocketService();
>>> $service->authenticate();
```

---

## 📞 Support & Resources

### ShipRocket Support
- **Email**: support@shiprocket.in
- **Phone**: 011-43499999
- **Live Chat**: Available on dashboard
- **Help Center**: https://help.shiprocket.in/

### Documentation
- **API Docs**: https://apidocs.shiprocket.in/
- **Integration Guide**: Available in dashboard
- **Video Tutorials**: YouTube channel

### Community
- **Facebook Group**: ShipRocket Sellers Community
- **LinkedIn**: ShipRocket official page
- **Webinars**: Monthly training sessions

---

## 🎉 Success Metrics to Track

### Key Performance Indicators (KPIs)
```
Shipping KPIs:
├── Order Processing Time
├── Delivery Success Rate
├── Customer Satisfaction Score
├── RTO (Return) Percentage
├── Shipping Cost vs Revenue
├── Average Delivery Time
└── Customer Complaints
```

### Monthly Review Checklist
- [ ] Shipping volume analysis
- [ ] Cost optimization opportunities
- [ ] Customer feedback review
- [ ] Courier partner performance
- [ ] Return/RTO analysis
- [ ] System uptime monitoring

---

## 💡 Pro Tips for Startups

1. **Start Small**: Begin with free plan, upgrade as you grow
2. **Test Thoroughly**: Use sandbox environment extensively
3. **Monitor Costs**: Track shipping expenses vs revenue
4. **Customer Communication**: Keep customers informed about delivery
5. **Optimize Packaging**: Right-size packages to reduce costs
6. **Multiple Courier Partners**: Don't rely on single courier
7. **Return Policy**: Have clear return/exchange policy
8. **Documentation**: Maintain proper shipping records

---

## 🚨 Common Pitfalls to Avoid

1. **Incorrect Address Formats**: Ensure proper address validation
2. **Weight Miscalculation**: Accurate product weights crucial
3. **Inadequate Packaging**: Poor packaging leads to damages
4. **Ignore RTO**: High return rates hurt business
5. **No Tracking Communication**: Keep customers informed
6. **Overestimate Delivery Time**: Set realistic expectations
7. **Ignore Analytics**: Data-driven decisions important

---

**🎯 Ready to Scale Your E-commerce Shipping?**

With this complete guide, you now have everything needed to implement professional shipping with ShipRocket in your Laravel e-commerce application. Start with the free plan, test thoroughly, and scale as your business grows!

Remember: Great shipping experience = Happy customers = Business growth! 🚀