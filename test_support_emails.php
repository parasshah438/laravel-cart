<?php

use Illuminate\Support\Facades\Mail;
use App\Mail\NewSupportTicket;
use App\Mail\SupportTicketStatusUpdate;
use App\Mail\SupportTicketReply;
use App\Models\SupportTicket;
use App\Models\User;

/**
 * Test Email Notifications for Support Tickets
 * 
 * This file can be used to test the email notification system
 * Run: php artisan tinker
 * Then include this file: include(base_path('test_support_emails.php'));
 */

// Test functions for different email scenarios
function testNewTicketEmail() {
    $customer = User::where('role', 'customer')->first();
    $ticket = SupportTicket::where('user_id', $customer->id)->first();
    
    if ($ticket) {
        Mail::to('admin@test.com')->send(new NewSupportTicket($ticket));
        echo "✅ New ticket email sent successfully!\n";
    } else {
        echo "❌ No support ticket found for testing\n";
    }
}

function testStatusUpdateEmail() {
    $customer = User::where('role', 'customer')->first();
    $ticket = SupportTicket::where('user_id', $customer->id)->first();
    
    if ($ticket) {
        Mail::to($customer->email)->send(new SupportTicketStatusUpdate($ticket, 'open', 'resolved'));
        echo "✅ Status update email sent successfully!\n";
    } else {
        echo "❌ No support ticket found for testing\n";
    }
}

function testReplyEmail() {
    $customer = User::where('role', 'customer')->first();
    $admin = User::where('role', 'admin')->first();
    $ticket = SupportTicket::where('user_id', $customer->id)->first();
    
    if ($ticket && $admin) {
        Mail::to($customer->email)->send(new SupportTicketReply($ticket, $admin, 'This is a test reply from admin'));
        echo "✅ Reply email sent successfully!\n";
    } else {
        echo "❌ No support ticket or admin found for testing\n";
    }
}

function testAllEmails() {
    echo "🧪 Testing Support Ticket Email Notifications...\n\n";
    
    testNewTicketEmail();
    testStatusUpdateEmail();
    testReplyEmail();
    
    echo "\n✨ All email tests completed!\n";
    echo "📧 Check your mail logs/inbox for the test emails\n";
}

// Display instructions
echo "📧 Support Ticket Email Testing Functions Available:\n";
echo "• testNewTicketEmail() - Test new ticket notification to admin\n";
echo "• testStatusUpdateEmail() - Test status change notification to customer\n";
echo "• testReplyEmail() - Test reply notification to customer\n";
echo "• testAllEmails() - Run all email tests\n\n";
echo "📝 Usage in Tinker:\n";
echo "php artisan tinker\n";
echo "include(base_path('test_support_emails.php'));\n";
echo "testAllEmails();\n\n";