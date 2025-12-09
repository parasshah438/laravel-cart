# 🗄️ **COMPREHENSIVE SALE FEATURES DATABASE STRUCTURE**

## 📊 **Core Sale Tables**

### **1. Sales Events (Main Sale Management)**
```sql
CREATE TABLE sale_events (done) (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,                    -- 'Black Friday 2025', 'Summer Sale'
    slug VARCHAR(255) UNIQUE NOT NULL,             -- 'black-friday-2025'
    description TEXT,
    type ENUM('flash_sale', 'mega_sale', 'deal_of_day', 'festival_sale', 'seasonal_sale', 'brand_day', 'category_sale') NOT NULL,
    status ENUM('draft', 'scheduled', 'active', 'paused', 'ended', 'cancelled') DEFAULT 'draft',
    
    -- Timing
    starts_at TIMESTAMP NOT NULL,
    ends_at TIMESTAMP NOT NULL,
    early_access_starts_at TIMESTAMP NULL,         -- VIP early access
    
    -- Display & UI
    banner_image VARCHAR(500),
    mobile_banner_image VARCHAR(500),
    theme_color VARCHAR(7) DEFAULT '#ff6b6b',      -- Sale theme color
    landing_page_template VARCHAR(100) DEFAULT 'default',
    
    -- Settings
    is_featured BOOLEAN DEFAULT FALSE,             -- Featured on homepage
    is_public BOOLEAN DEFAULT TRUE,                -- Public or private sale
    requires_membership BOOLEAN DEFAULT FALSE,      -- Members only
    max_discount_percentage DECIMAL(5,2) DEFAULT 0,
    
    -- SEO & Marketing
    meta_title VARCHAR(255),
    meta_description TEXT,
    
    -- Analytics
    total_participants INT DEFAULT 0,
    total_orders INT DEFAULT 0,
    total_revenue DECIMAL(15,2) DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### **2. Sale Products (Products in Sale)**
```sql
CREATE TABLE sale_products (done) (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_event_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    
    -- Discount Settings
    discount_type ENUM('percentage', 'fixed_amount', 'buy_x_get_y', 'bundle') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,        -- 20 (for 20%), 100 (for ₹100 off)
    
    -- Special Pricing
    sale_price DECIMAL(10,2) NOT NULL,            -- Final sale price
    original_price DECIMAL(10,2) NOT NULL,        -- Original price for comparison
    max_discount_amount DECIMAL(10,2) NULL,       -- Max discount cap (for percentage)
    
    -- Quantity & Inventory
    sale_quantity_limit INT NULL,                 -- Limited quantity for this sale
    sold_quantity INT DEFAULT 0,                  -- How many sold in this sale
    per_user_limit INT DEFAULT 0,                 -- Max per user (0 = unlimited)
    
    -- Flash Sale Specific
    flash_sale_duration_minutes INT DEFAULT 0,    -- 0 = no flash sale timing
    
    -- Priority & Display
    sort_order INT DEFAULT 0,                     -- Display order in sale
    is_featured_in_sale BOOLEAN DEFAULT FALSE,    -- Featured product in this sale
    
    -- Timing (can override sale event timing)
    starts_at TIMESTAMP NULL,                     -- If different from sale event
    ends_at TIMESTAMP NULL,                       -- If different from sale event
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sale_event_id) REFERENCES sale_events(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_sale_product (sale_event_id, product_id)
);
```

### **3. Bundle Deals & Combo Offers**
```sql
CREATE TABLE bundle_deals (done) (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,                   -- 'Complete Electronics Bundle'
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    
    -- Bundle Settings
    bundle_type ENUM('fixed_combo', 'buy_x_get_y', 'mix_match', 'tiered_discount') NOT NULL,
    min_products INT DEFAULT 2,                   -- Minimum products to qualify
    max_products INT DEFAULT 0,                   -- 0 = unlimited
    
    -- Pricing
    discount_type ENUM('percentage', 'fixed_amount') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    bundle_price DECIMAL(10,2) NULL,              -- Fixed bundle price (optional)
    
    -- Conditions
    sale_event_id BIGINT UNSIGNED NULL,           -- Optional: Part of specific sale
    category_ids JSON NULL,                       -- Specific categories only
    brand_ids JSON NULL,                          -- Specific brands only
    
    -- Display
    image VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    
    -- Timing
    starts_at TIMESTAMP NULL,
    ends_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sale_event_id) REFERENCES sale_events(id) ON DELETE SET NULL
);
```

### **4. Bundle Products (Products in Bundle)**
```sql
CREATE TABLE bundle_products (done) (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bundle_deal_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    
    -- Product Role in Bundle
    is_primary BOOLEAN DEFAULT FALSE,             -- Main product in bundle
    is_optional BOOLEAN DEFAULT FALSE,            -- Optional add-on
    min_quantity INT DEFAULT 1,
    max_quantity INT DEFAULT 1,
    
    -- Pricing in Bundle
    bundle_product_price DECIMAL(10,2) NULL,      -- Special price in this bundle
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (bundle_deal_id) REFERENCES bundle_deals(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_bundle_product (bundle_deal_id, product_id)
);
```

## 🎯 **Discount & Coupon System**

### **5. Dynamic Coupons & Offers**
```sql
CREATE TABLE dynamic_coupons (done) (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,             -- 'FLASH20', 'SUMMER2025'
    name VARCHAR(255) NOT NULL,
    description TEXT,
    
    -- Coupon Type & Value
    type ENUM('percentage', 'fixed_cart', 'fixed_product', 'free_shipping', 'buy_x_get_y', 'cashback') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    
    -- Usage Limits
    usage_limit INT DEFAULT 0,                    -- 0 = unlimited
    used_count INT DEFAULT 0,
    per_user_limit INT DEFAULT 1,
    
    -- Conditions
    min_order_amount DECIMAL(10,2) DEFAULT 0,
    max_discount_amount DECIMAL(10,2) NULL,
    
    -- Product/Category Restrictions
    applicable_products JSON NULL,                -- Specific product IDs
    applicable_categories JSON NULL,              -- Specific category IDs  
    applicable_brands JSON NULL,                  -- Specific brand IDs
    
    -- User Restrictions
    user_groups JSON NULL,                        -- ['premium', 'vip', 'new_users']
    first_order_only BOOLEAN DEFAULT FALSE,
    
    -- Payment Method Offers
    payment_methods JSON NULL,                    -- ['razorpay', 'card', 'wallet']
    
    -- Timing
    starts_at TIMESTAMP NOT NULL,
    ends_at TIMESTAMP NOT NULL,
    
    -- Sale Integration
    sale_event_id BIGINT UNSIGNED NULL,           -- Part of specific sale
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    is_auto_apply BOOLEAN DEFAULT FALSE,          -- Auto-apply if conditions met
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sale_event_id) REFERENCES sale_events(id) ON DELETE SET NULL
);
```

### **6. Tiered Discounts**
```sql
CREATE TABLE tiered_discounts (done) (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,                   -- 'Volume Discount - Electronics'
    description TEXT,
    
    -- Tier Settings
    tier_type ENUM('quantity', 'amount') NOT NULL, -- Based on qty or cart amount
    
    -- Conditions
    product_ids JSON NULL,                        -- Specific products
    category_ids JSON NULL,                       -- Specific categories
    brand_ids JSON NULL,                          -- Specific brands
    
    -- Sale Integration
    sale_event_id BIGINT UNSIGNED NULL,
    
    -- Status & Timing
    is_active BOOLEAN DEFAULT TRUE,
    starts_at TIMESTAMP NULL,
    ends_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sale_event_id) REFERENCES sale_events(id) ON DELETE SET NULL
);
```

### **7. Tier Rules (Discount Tiers)**
```sql
CREATE TABLE tier_rules (done) (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tiered_discount_id BIGINT UNSIGNED NOT NULL,
    
    -- Tier Conditions
    min_quantity INT DEFAULT 0,                   -- Min qty to qualify
    min_amount DECIMAL(10,2) DEFAULT 0,           -- Min amount to qualify
    
    -- Tier Benefits
    discount_type ENUM('percentage', 'fixed_amount') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    
    -- Display
    tier_name VARCHAR(100),                       -- 'Silver Tier', 'Gold Tier'
    tier_order INT DEFAULT 0,                     -- Display order
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tiered_discount_id) REFERENCES tiered_discounts(id) ON DELETE CASCADE
);
```

## 🎮 **Gamification & User Engagement**

### **8. Sale Challenges & Campaigns**
```sql
CREATE TABLE sale_challenges (done) (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,                   -- 'Spend ₹2000 & Get ₹200 Back'
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    
    -- Challenge Type
    type ENUM('spend_amount', 'buy_quantity', 'visit_days', 'share_social', 'invite_friends', 'complete_profile') NOT NULL,
    
    -- Challenge Conditions
    target_value DECIMAL(10,2) NOT NULL,          -- ₹2000 spend, 5 products, etc.
    target_days INT DEFAULT 1,                    -- For multi-day challenges
    
    -- Rewards
    reward_type ENUM('cashback', 'discount_coupon', 'free_product', 'points', 'badge') NOT NULL,
    reward_value DECIMAL(10,2) NOT NULL,
    reward_product_id BIGINT UNSIGNED NULL,       -- For free product reward
    
    -- Challenge Settings
    max_participants INT DEFAULT 0,               -- 0 = unlimited
    current_participants INT DEFAULT 0,
    per_user_attempts INT DEFAULT 1,              -- How many times user can attempt
    
    -- Sale Integration
    sale_event_id BIGINT UNSIGNED NULL,
    
    -- Display
    banner_image VARCHAR(500),
    icon VARCHAR(500),
    badge_image VARCHAR(500),
    
    -- Timing
    starts_at TIMESTAMP NOT NULL,
    ends_at TIMESTAMP NOT NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sale_event_id) REFERENCES sale_events(id) ON DELETE SET NULL,
    FOREIGN KEY (reward_product_id) REFERENCES products(id) ON DELETE SET NULL
);
```

### **9. User Challenge Participation**
```sql
CREATE TABLE user_challenge_participations (done) (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    sale_challenge_id BIGINT UNSIGNED NOT NULL,
    
    -- Progress Tracking
    current_progress DECIMAL(10,2) DEFAULT 0,     -- Current achievement (₹1500 of ₹2000)
    target_progress DECIMAL(10,2) NOT NULL,       -- Target to achieve
    progress_percentage DECIMAL(5,2) DEFAULT 0,   -- 75.00%
    
    -- Status
    status ENUM('active', 'completed', 'failed', 'rewarded') DEFAULT 'active',
    
    -- Completion & Reward
    completed_at TIMESTAMP NULL,
    reward_claimed_at TIMESTAMP NULL,
    reward_coupon_code VARCHAR(50) NULL,          -- Generated coupon code
    
    -- Tracking
    orders_count INT DEFAULT 0,                   -- Orders made for this challenge
    amount_spent DECIMAL(10,2) DEFAULT 0,         -- Total amount spent
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sale_challenge_id) REFERENCES sale_challenges(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_challenge (user_id, sale_challenge_id)
);
```

### **10. Spin Wheel & Lucky Draws**
```sql
CREATE TABLE spin_wheel_campaigns (done)(
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,                   -- 'Black Friday Spin Wheel'
    description TEXT,
    
    -- Campaign Settings
    max_spins_per_user INT DEFAULT 1,
    total_spins_allowed INT DEFAULT 0,            -- 0 = unlimited
    current_total_spins INT DEFAULT 0,
    
    -- Eligibility Conditions
    min_order_amount DECIMAL(10,2) DEFAULT 0,     -- Must spend to spin
    requires_purchase BOOLEAN DEFAULT FALSE,       -- Must purchase to spin
    first_time_users_only BOOLEAN DEFAULT FALSE,
    
    -- Sale Integration
    sale_event_id BIGINT UNSIGNED NULL,
    
    -- Display
    wheel_config JSON,                            -- Wheel segments configuration
    background_image VARCHAR(500),
    
    -- Timing
    starts_at TIMESTAMP NOT NULL,
    ends_at TIMESTAMP NOT NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sale_event_id) REFERENCES sale_events(id) ON DELETE SET NULL
);
```

### **11. Spin Wheel Prizes**
```sql
CREATE TABLE spin_wheel_prizes (done)(
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    spin_wheel_campaign_id BIGINT UNSIGNED NOT NULL,
    
    -- Prize Details
    prize_name VARCHAR(255) NOT NULL,             -- '20% Off Coupon'
    prize_type ENUM('discount_coupon', 'cashback', 'free_product', 'free_shipping', 'points', 'nothing') NOT NULL,
    prize_value DECIMAL(10,2) DEFAULT 0,
    
    -- Prize Settings
    probability_percentage DECIMAL(5,2) NOT NULL, -- 15.50% chance
    max_winners INT DEFAULT 0,                    -- 0 = unlimited
    current_winners INT DEFAULT 0,
    
    -- Prize Configuration
    coupon_config JSON NULL,                      -- Coupon generation settings
    product_id BIGINT UNSIGNED NULL,              -- For free product prize
    
    -- Display
    display_text VARCHAR(255),                    -- Text shown on wheel
    icon VARCHAR(500),
    color VARCHAR(7) DEFAULT '#ffd700',           -- Segment color
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (spin_wheel_campaign_id) REFERENCES spin_wheel_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);
```

### **12. User Spin History**
```sql
CREATE TABLE user_spin_history(done)(
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    spin_wheel_campaign_id BIGINT UNSIGNED NOT NULL,
    spin_wheel_prize_id BIGINT UNSIGNED NOT NULL,
    
    -- Prize Won
    prize_won VARCHAR(255) NOT NULL,              -- What they won
    prize_value DECIMAL(10,2) DEFAULT 0,
    
    -- Coupon Generated (if applicable)
    generated_coupon_code VARCHAR(50) NULL,
    coupon_claimed BOOLEAN DEFAULT FALSE,
    coupon_claimed_at TIMESTAMP NULL,
    
    -- Order Context (if spin after purchase)
    order_id BIGINT UNSIGNED NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (spin_wheel_campaign_id) REFERENCES spin_wheel_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (spin_wheel_prize_id) REFERENCES spin_wheel_prizes(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);
```

## 🔔 **Notifications & Alerts**

### **13. Sale Notifications & Alerts**
```sql
CREATE TABLE sale_notifications (done) (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    
    -- Notification Type
    type ENUM('sale_started', 'sale_ending_soon', 'wishlist_on_sale', 'flash_deal_available', 'early_access_invite', 'price_drop_alert') NOT NULL,
    
    -- Related Entities
    sale_event_id BIGINT UNSIGNED NULL,
    product_id BIGINT UNSIGNED NULL,
    
    -- Notification Content
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    action_url VARCHAR(500) NULL,                 -- Where to redirect
    
    -- Scheduling
    scheduled_for TIMESTAMP NULL,                 -- When to send (for scheduled notifications)
    sent_at TIMESTAMP NULL,
    
    -- Status
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    
    -- Channels
    sent_via_email BOOLEAN DEFAULT FALSE,
    sent_via_push BOOLEAN DEFAULT FALSE,
    sent_via_sms BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sale_event_id) REFERENCES sale_events(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);
```

### **14. Wishlist Sale Alerts**
```sql
CREATE TABLE wishlist_sale_alerts (done)(
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    
    -- Alert Settings
    desired_price DECIMAL(10,2) NULL,             -- Alert when price drops below this
    percentage_drop DECIMAL(5,2) NULL,            -- Alert when drops by X%
    
    -- Alert Status
    is_active BOOLEAN DEFAULT TRUE,
    last_notified_at TIMESTAMP NULL,
    notification_count INT DEFAULT 0,
    
    -- Sale Context
    current_sale_event_id BIGINT UNSIGNED NULL,   -- Current sale affecting this product
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (current_sale_event_id) REFERENCES sale_events(id) ON DELETE SET NULL,
    UNIQUE KEY unique_wishlist_alert (user_id, product_id)
);
```

## 📊 **Analytics & Tracking**

### **15. Sale Analytics & Performance**
```sql
CREATE TABLE sale_analytics  (done)(
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_event_id BIGINT UNSIGNED NOT NULL,
    
    -- Date Tracking
    analytics_date DATE NOT NULL,                 -- Daily analytics
    hour_of_day TINYINT NULL,                     -- 0-23 for hourly analytics
    
    -- Performance Metrics
    page_views INT DEFAULT 0,
    unique_visitors INT DEFAULT 0,
    products_viewed INT DEFAULT 0,
    add_to_cart_count INT DEFAULT 0,
    checkout_initiated INT DEFAULT 0,
    orders_completed INT DEFAULT 0,
    
    -- Revenue Metrics
    gross_revenue DECIMAL(15,2) DEFAULT 0,
    net_revenue DECIMAL(15,2) DEFAULT 0,
    total_discount_given DECIMAL(15,2) DEFAULT 0,
    avg_order_value DECIMAL(10,2) DEFAULT 0,
    
    -- Conversion Metrics
    view_to_cart_rate DECIMAL(5,2) DEFAULT 0,     -- %
    cart_to_order_rate DECIMAL(5,2) DEFAULT 0,    -- %
    overall_conversion_rate DECIMAL(5,2) DEFAULT 0, -- %
    
    -- Product Performance
    top_selling_product_id BIGINT UNSIGNED NULL,
    top_product_revenue DECIMAL(15,2) DEFAULT 0,
    
    -- Traffic Sources
    organic_traffic INT DEFAULT 0,
    paid_traffic INT DEFAULT 0,
    social_traffic INT DEFAULT 0,
    email_traffic INT DEFAULT 0,
    direct_traffic INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sale_event_id) REFERENCES sale_events(id) ON DELETE CASCADE,
    FOREIGN KEY (top_selling_product_id) REFERENCES products(id) ON DELETE SET NULL,
    UNIQUE KEY unique_daily_analytics (sale_event_id, analytics_date, hour_of_day)
);
```

### **16. User Sale Behavior Tracking**
```sql
CREATE TABLE user_sale_behaviors (done)(
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,                 -- NULL for guest users
    session_id VARCHAR(255) NOT NULL,             -- Track guest behavior
    
    -- Sale Context
    sale_event_id BIGINT UNSIGNED NOT NULL,
    
    -- Behavior Tracking
    action_type ENUM('view_sale', 'view_product', 'add_to_cart', 'remove_from_cart', 'add_to_wishlist', 'apply_coupon', 'checkout_start', 'checkout_complete', 'share_sale') NOT NULL,
    
    -- Related Entities
    product_id BIGINT UNSIGNED NULL,
    coupon_code VARCHAR(50) NULL,
    
    -- Context Data
    device_type ENUM('desktop', 'mobile', 'tablet') NOT NULL,
    user_agent TEXT,
    ip_address VARCHAR(45),
    referrer_url VARCHAR(500) NULL,
    
    -- Metadata
    action_metadata JSON NULL,                    -- Additional action-specific data
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (sale_event_id) REFERENCES sale_events(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    
    INDEX idx_user_behavior (user_id, sale_event_id, created_at),
    INDEX idx_session_behavior (session_id, sale_event_id, created_at)
);
```

## 🎨 **UI/UX & Display Management**

### **17. Sale Banners & Promotions**
```sql
CREATE TABLE sale_banners (done) (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    
    -- Banner Type & Position
    type ENUM('hero_banner', 'category_banner', 'product_strip', 'popup_modal', 'notification_bar', 'sticky_footer') NOT NULL,
    position ENUM('homepage_top', 'homepage_middle', 'category_top', 'product_sidebar', 'cart_page', 'checkout_page') NOT NULL,
    
    -- Display Content
    title VARCHAR(255),
    subtitle VARCHAR(255),
    description TEXT,
    cta_text VARCHAR(100),                        -- 'Shop Now', 'Get Deal'
    cta_url VARCHAR(500),
    
    -- Images & Media
    desktop_image VARCHAR(500),
    mobile_image VARCHAR(500),
    background_color VARCHAR(7) DEFAULT '#ffffff',
    text_color VARCHAR(7) DEFAULT '#000000',
    
    -- Targeting
    sale_event_id BIGINT UNSIGNED NULL,           -- Specific sale
    product_categories JSON NULL,                 -- Show for specific categories
    user_segments JSON NULL,                      -- ['new_users', 'premium', 'vip']
    
    -- Display Rules
    display_priority INT DEFAULT 0,               -- Higher = shown first
    max_impressions_per_user INT DEFAULT 0,       -- 0 = unlimited
    
    -- A/B Testing
    variant_name VARCHAR(100) NULL,               -- 'A', 'B', 'Control'
    ab_test_group_id BIGINT UNSIGNED NULL,
    
    -- Timing
    starts_at TIMESTAMP NULL,
    ends_at TIMESTAMP NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sale_event_id) REFERENCES sale_events(id) ON DELETE SET NULL
);
```

### **18. Banner Impressions & Clicks**
```sql
CREATE TABLE banner_interactions (done) (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_banner_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,                 -- NULL for guest
    session_id VARCHAR(255) NOT NULL,
    
    -- Interaction Details
    interaction_type ENUM('impression', 'click', 'close', 'hover') NOT NULL,
    
    -- Context
    page_url VARCHAR(500),
    device_type ENUM('desktop', 'mobile', 'tablet') NOT NULL,
    user_agent TEXT,
    ip_address VARCHAR(45),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sale_banner_id) REFERENCES sale_banners(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_banner_stats (sale_banner_id, interaction_type, created_at)
);
```

## 🔄 **Integration Tables**

### **19. Sale Orders (Orders during sale events)**
```sql
CREATE TABLE sale_orders  (done)(
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    sale_event_id BIGINT UNSIGNED NULL,
    
    -- Sale Context
    coupons_used JSON NULL,                       -- Applied coupon codes
    bundles_purchased JSON NULL,                  -- Bundle deal IDs
    challenges_completed JSON NULL,               -- Challenge IDs completed
    
    -- Sale Metrics
    original_amount DECIMAL(15,2) NOT NULL,       -- Before any sale discounts
    sale_discount_amount DECIMAL(15,2) DEFAULT 0, -- Total discount from sale
    final_amount DECIMAL(15,2) NOT NULL,          -- After sale discounts
    
    -- Attribution
    referral_source VARCHAR(255) NULL,            -- How user found the sale
    campaign_source VARCHAR(255) NULL,            -- Marketing campaign
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (sale_event_id) REFERENCES sale_events(id) ON DELETE SET NULL,
    UNIQUE KEY unique_sale_order (order_id)
);
```

### **20. User Sale Preferences**
```sql
CREATE TABLE user_sale_preferences (done) (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    
    -- Notification Preferences
    email_sale_notifications BOOLEAN DEFAULT TRUE,
    push_sale_notifications BOOLEAN DEFAULT TRUE,
    sms_sale_notifications BOOLEAN DEFAULT FALSE,
    
    -- Interest Categories
    preferred_sale_categories JSON NULL,          -- Category IDs user is interested in
    preferred_brands JSON NULL,                   -- Brand IDs user follows
    
    -- Price Alert Settings
    price_drop_threshold DECIMAL(5,2) DEFAULT 10.00, -- Alert when price drops by X%
    max_price_notifications_per_day INT DEFAULT 5,
    
    -- Sale Timing Preferences
    preferred_notification_time TIME DEFAULT '09:00:00',
    timezone VARCHAR(50) DEFAULT 'Asia/Kolkata',
    
    -- VIP Settings
    early_access_enabled BOOLEAN DEFAULT TRUE,
    exclusive_deals_enabled BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_preferences (user_id)
);
```

## 📈 **Indexes for Performance**

```sql
-- Performance indexes for sale queries
CREATE INDEX idx_sale_events_active ON sale_events(status, starts_at, ends_at);
CREATE INDEX idx_sale_products_event ON sale_products(sale_event_id, starts_at, ends_at);
CREATE INDEX idx_sale_analytics_date ON sale_analytics(sale_event_id, analytics_date);
CREATE INDEX idx_user_behaviors_tracking ON user_sale_behaviors(sale_event_id, created_at);
CREATE INDEX idx_notifications_scheduled ON sale_notifications(scheduled_for, sent_at);
CREATE INDEX idx_wishlist_alerts_active ON wishlist_sale_alerts(is_active, user_id);
```

---

## 🎯 **Summary**

This comprehensive database structure supports:

✅ **20 Core Tables** covering all sale features  
✅ **Flash Sales & Mega Events** with full scheduling  
✅ **Dynamic Pricing & Bundles** with complex rules  
✅ **Gamification** (challenges, spin wheel, rewards)  
✅ **Advanced Analytics** with user behavior tracking  
✅ **Notification System** with multi-channel delivery  
✅ **A/B Testing** capabilities for banners  
✅ **VIP/Premium Features** with early access  
✅ **Performance Optimized** with proper indexing  

This structure will power your Amazon/Flipkart-style sale system! 🚀