# 🔔 **NOTIFICATION PREFERENCES STORAGE GUIDE**
## Laravel Cart - Database Table for User Notification Settings

---

## 📊 **STORAGE SOLUTION**

### **✅ WHAT'S IMPLEMENTED**

Your notification preferences are now stored in a **dedicated database table** called:

```
🗄️ user_notification_preferences
```

This table stores all user notification settings and preferences.

---

## 🗃️ **DATABASE SCHEMA**

### **Table: `user_notification_preferences`**

| Column | Type | Default | Description |
|--------|------|---------|-------------|
| `id` | bigint | AUTO_INCREMENT | Primary key |
| `user_id` | bigint | FOREIGN KEY | Links to users table |
| `email_notifications` | boolean | `true` | Enable/disable email notifications |
| `push_notifications` | boolean | `false` | Enable/disable push notifications |
| `order_updates` | boolean | `true` | Order status notifications |
| `payment_alerts` | boolean | `true` | Payment success/failure alerts |
| `review_reminders` | boolean | `true` | Review request reminders |
| `review_responses` | boolean | `true` | Review response notifications |
| `promotional_emails` | boolean | `false` | Marketing/promotional emails |
| `wishlist_sales` | boolean | `true` | Wishlist item sale alerts |
| `email_frequency` | enum | `'daily'` | instant, daily, weekly, never |
| `quiet_hours` | enum | `'night'` | none, evening, night, weekend |
| `created_at` | timestamp | NOW() | Record creation time |
| `updated_at` | timestamp | NOW() | Last update time |

### **Indexes**
- ✅ **Primary Key**: `id`
- ✅ **Unique Index**: `user_id` (one preference record per user)
- ✅ **Foreign Key**: `user_id` references `users(id)` with CASCADE DELETE
- ✅ **Performance Index**: `(user_id, email_notifications)`

---

## 💻 **HOW TO USE**

### **1. Accessing User Preferences**

```php
// Get user's notification preferences
$user = Auth::user();
$preferences = $user->getNotificationPreferences();

// Check specific preference
if ($preferences->order_updates) {
    // User wants order update notifications
}

// Check if user wants specific notification type
if ($preferences->wantsNotification('order_placed')) {
    // Send order placed notification
}
```

### **2. Creating/Updating Preferences**

```php
// Create preferences for new user (auto-creates with defaults)
$preferences = $user->getNotificationPreferences();

// Update specific preferences
$user->notificationPreferences()->updateOrCreate(
    ['user_id' => $user->id],
    [
        'email_notifications' => true,
        'promotional_emails' => false,
        'email_frequency' => 'weekly'
    ]
);
```

### **3. Checking Notification Eligibility**

```php
$preferences = $user->getNotificationPreferences();

// Check if user wants email notifications
if ($preferences->wantsEmailNotification()) {
    // Send email (respects quiet hours)
}

// Check if user is in quiet hours
if (!$preferences->isInQuietHours()) {
    // Send notification
}

// Check specific notification type
if ($preferences->wantsNotification('payment_success')) {
    // Send payment notification
}
```

---

## 🔧 **MODEL RELATIONSHIPS**

### **User Model** (`app/Models/User.php`)

```php
// Get notification preferences relationship
public function notificationPreferences()
{
    return $this->hasOne(UserNotificationPreference::class);
}

// Get or create preferences with defaults
public function getNotificationPreferences()
{
    return $this->notificationPreferences ?? 
           $this->notificationPreferences()->create(UserNotificationPreference::getDefaults());
}
```

### **UserNotificationPreference Model** (`app/Models/UserNotificationPreference.php`)

```php
// Belongs to user
public function user()
{
    return $this->belongsTo(User::class);
}

// Helper methods
public function wantsNotification(string $type): bool
public function isInQuietHours(): bool
public function wantsEmailNotification(): bool
public function wantsPushNotification(): bool
```

---

## 🎯 **CONTROLLER INTEGRATION**

### **NotificationController** (`app/Http/Controllers/NotificationController.php`)

```php
// Show preferences page with current settings
public function preferences(): View
{
    $user = Auth::user();
    $preferences = $user->getNotificationPreferences();
    return view('notifications.preferences', compact('preferences'));
}

// Update preferences
public function updatePreferences(Request $request): JsonResponse
{
    $validated = $request->validate([...]);
    $user = Auth::user();
    
    $preferences = $user->notificationPreferences;
    if ($preferences) {
        $preferences->update($validated);
    } else {
        $user->notificationPreferences()->create($validated);
    }
    
    return response()->json(['success' => true]);
}
```

---

## 📝 **DEFAULT PREFERENCES**

When a user first visits the preferences page, these defaults are created:

```php
[
    'email_notifications' => true,    // Enable email notifications
    'push_notifications' => false,    // Disable push notifications
    'order_updates' => true,          // Enable order updates
    'payment_alerts' => true,         // Enable payment alerts
    'review_reminders' => true,       // Enable review reminders
    'review_responses' => true,       // Enable review responses
    'promotional_emails' => false,    // Disable promotional emails
    'wishlist_sales' => true,         // Enable wishlist sale alerts
    'email_frequency' => 'daily',     // Daily email digest
    'quiet_hours' => 'night',         // Quiet during night (10 PM - 8 AM)
]
```

---

## 🔍 **NOTIFICATION TYPE MAPPING**

The `wantsNotification()` method maps notification types to preferences:

```php
'order_placed', 'order_shipped', 'order_delivered', 'order_cancelled' → order_updates
'payment_success', 'payment_failed' → payment_alerts
'review_request' → review_reminders
'review_response' → review_responses
'promotion', 'promotional' → promotional_emails
'wishlist_sale' → wishlist_sales
'system', 'welcome' → always send (important notifications)
```

---

## ⏰ **QUIET HOURS LOGIC**

```php
'none' → Never in quiet hours
'evening' → 6:00 PM - 9:00 AM
'night' → 10:00 PM - 8:00 AM  
'weekend' → Saturday and Sunday
```

---

## 🧪 **TESTING**

### **Database Verification**

```sql
-- Check table exists
SHOW TABLES LIKE 'user_notification_preferences';

-- View table structure
DESCRIBE user_notification_preferences;

-- Check user preferences
SELECT * FROM user_notification_preferences WHERE user_id = 1;
```

### **Test Routes**

```
GET  /notifications/preferences        // View preferences page
POST /notifications/preferences        // Update preferences
```

---

## 🚀 **INTEGRATION WITH NOTIFICATION SYSTEM**

### **Before Sending Notifications**

```php
use App\Services\NotificationService;

// Check user preferences before sending
$user = User::find($userId);
$preferences = $user->getNotificationPreferences();

if ($preferences->wantsNotification('order_placed') && 
    $preferences->wantsEmailNotification()) {
    
    NotificationService::sendOrderNotification($orderId, 'order_placed', $userId);
}
```

### **Email Frequency Handling**

```php
// For instant notifications
if ($preferences->email_frequency === 'instant' && 
    $preferences->wantsEmailNotification()) {
    // Send email immediately
}

// For digest notifications (daily/weekly)
// Collect notifications and send in batches
// (Implementation would depend on your email queue system)
```

---

## 🎉 **SUMMARY**

✅ **Table Created**: `user_notification_preferences`
✅ **Model Ready**: `UserNotificationPreference`
✅ **Controller Updated**: Saves/loads preferences
✅ **Relationships**: User ↔ NotificationPreferences
✅ **Helper Methods**: Check preferences, quiet hours, etc.
✅ **Default Values**: Sensible defaults for new users
✅ **Type Mapping**: Maps notification types to preferences
✅ **Production Ready**: Proper indexes and constraints

Your notification preferences are now **properly stored in the database** and ready for production use! 🎊