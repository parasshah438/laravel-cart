<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Ticket Update</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .status-update {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .status-resolved {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .status-closed {
            border-left-color: #6c757d;
            background: #e2e3e5;
        }
        .status-in-progress {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        .status-open {
            border-left-color: #007bff;
            background: #cce5ff;
        }
        .ticket-info {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: 600;
            color: #495057;
        }
        .value {
            color: #6c757d;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            color: white;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-resolved { background: #28a745; }
        .status-closed { background: #6c757d; }
        .status-in-progress { background: #ffc107; color: #212529; }
        .status-open { background: #007bff; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin: 15px 0;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #e9ecef;
        }
        .success-banner {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 5px;
            }
            .header, .content {
                padding: 20px;
            }
            .info-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📋 Ticket Status Update</h1>
            <p style="margin: 10px 0 0; opacity: 0.9;">Your support ticket has been updated</p>
        </div>

        <!-- Success Banner for Resolved/Closed -->
        @if(in_array($newStatus, ['resolved', 'closed']))
        <div class="success-banner">
            ✅ Great News! Your ticket has been {{ $newStatus === 'resolved' ? 'resolved' : 'closed' }}
        </div>
        @endif

        <!-- Content -->
        <div class="content">
            <p>Hello <strong>{{ $customer->name }}</strong>,</p>
            
            <p>Your support ticket has been updated. Here are the details:</p>
            
            <!-- Status Update -->
            <div class="status-update status-{{ str_replace('_', '-', $newStatus) }}">
                <h3 style="margin-top: 0; color: #495057;">📊 Status Update</h3>
                
                @if($oldStatus && $oldStatus !== $newStatus)
                <p><strong>Previous Status:</strong> 
                    <span class="status-badge status-{{ str_replace('_', '-', $oldStatus) }}">
                        {{ ucfirst(str_replace('_', ' ', $oldStatus)) }}
                    </span>
                </p>
                <p style="font-size: 24px; margin: 10px 0;">⬇️</p>
                @endif
                
                <p><strong>Current Status:</strong> 
                    <span class="status-badge status-{{ str_replace('_', '-', $newStatus) }}">
                        {{ ucfirst(str_replace('_', ' ', $newStatus)) }}
                    </span>
                </p>
                
                <p style="margin-bottom: 0;"><strong>Updated:</strong> {{ now()->format('M d, Y \a\t g:i A') }}</p>
            </div>

            <!-- Ticket Information -->
            <div class="ticket-info">
                <h3 style="margin-top: 0; color: #495057;">🎫 Ticket Details</h3>
                
                <div class="info-row">
                    <span class="label">Ticket ID:</span>
                    <span class="value">#{{ $ticket->id }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Subject:</span>
                    <span class="value">{{ $ticket->subject }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Priority:</span>
                    <span class="value">{{ ucfirst($ticket->priority) }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Category:</span>
                    <span class="value">{{ $ticket->category ? ucfirst($ticket->category) : 'General' }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Created:</span>
                    <span class="value">{{ $ticket->created_at->format('M d, Y \a\t g:i A') }}</span>
                </div>
                
                @if($ticket->assignedAgent)
                <div class="info-row">
                    <span class="label">Assigned Agent:</span>
                    <span class="value">{{ $ticket->assignedAgent->name }}</span>
                </div>
                @endif
            </div>

            <!-- Status-specific messages -->
            @if($newStatus === 'resolved')
            <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <h4 style="color: #155724; margin-top: 0;">🎉 Ticket Resolved!</h4>
                <p style="color: #155724; margin-bottom: 0;">
                    Great news! Your support ticket has been resolved. If you're satisfied with the solution, 
                    the ticket will be automatically closed in 24 hours. If you need further assistance, 
                    please reply to add additional comments.
                </p>
            </div>
            @elseif($newStatus === 'closed')
            <div style="background: #e2e3e5; border: 1px solid #d6d8db; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <h4 style="color: #383d41; margin-top: 0;">✅ Ticket Closed</h4>
                <p style="color: #383d41; margin-bottom: 0;">
                    This support ticket has been closed. If you need further assistance with this issue 
                    or have a new question, please create a new support ticket.
                </p>
            </div>
            @elseif($newStatus === 'in_progress')
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <h4 style="color: #856404; margin-top: 0;">⏳ Work in Progress</h4>
                <p style="color: #856404; margin-bottom: 0;">
                    Our team is actively working on your request. We'll keep you updated on our progress 
                    and let you know when we have more information or need additional details from you.
                </p>
            </div>
            @endif

            <!-- Action Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.url') }}/support/tickets/{{ $ticket->id }}" class="btn">
                    👀 View Full Ticket Details
                </a>
            </div>

            <!-- Additional Help -->
            <div style="background: #e7f3ff; border: 1px solid #b8daff; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <h4 style="color: #0056b3; margin-top: 0;">💡 Need More Help?</h4>
                <ul style="margin: 10px 0; padding-left: 20px; color: #495057;">
                    <li><a href="{{ config('app.url') }}/support" style="color: #0056b3;">Visit our Support Center</a></li>
                    <li><a href="{{ config('app.url') }}/help" style="color: #0056b3;">Browse our Help Documentation</a></li>
                    <li><a href="{{ config('app.url') }}/faq" style="color: #0056b3;">Check our FAQ</a></li>
                    <li><a href="{{ config('app.url') }}/support/tickets/create" style="color: #0056b3;">Create a new support ticket</a></li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Laravel Cart Support Team</strong></p>
            <p>Thank you for your patience as we work to resolve your inquiry.</p>
            <p style="margin-top: 15px;">
                <a href="{{ config('app.url') }}/support" style="color: #667eea; text-decoration: none;">
                    Support Center
                </a> | 
                <a href="{{ config('app.url') }}/contact" style="color: #667eea; text-decoration: none;">
                    Contact Us
                </a>
            </p>
        </div>
    </div>
</body>
</html>