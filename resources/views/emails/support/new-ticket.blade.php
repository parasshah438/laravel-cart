<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Support Ticket</title>
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
        .ticket-info {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .ticket-info h3 {
            margin-top: 0;
            color: #667eea;
            font-size: 18px;
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
        .priority-high {
            color: #dc3545;
            font-weight: bold;
        }
        .priority-medium {
            color: #fd7e14;
            font-weight: bold;
        }
        .priority-low {
            color: #28a745;
            font-weight: bold;
        }
        .message-content {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            white-space: pre-wrap;
            line-height: 1.7;
        }
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
        .urgent-banner {
            background: linear-gradient(45deg, #dc3545, #ff6b6b);
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
            <h1>🎫 New Support Ticket</h1>
            <p style="margin: 10px 0 0; opacity: 0.9;">Ticket #{{ $ticket->id }} has been created</p>
        </div>

        <!-- Urgent Priority Banner -->
        @if($ticket->priority === 'high')
        <div class="urgent-banner">
            ⚠️ HIGH PRIORITY TICKET - Immediate Attention Required
        </div>
        @endif

        <!-- Content -->
        <div class="content">
            <h2 style="color: #495057; margin-bottom: 20px;">📋 Ticket Details</h2>
            
            <!-- Ticket Information -->
            <div class="ticket-info">
                <h3>Ticket Information</h3>
                
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
                    <span class="value priority-{{ $ticket->priority }}">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="label">Category:</span>
                    <span class="value">{{ $ticket->category ? ucfirst($ticket->category) : 'General' }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Status:</span>
                    <span class="value">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Created:</span>
                    <span class="value">{{ $ticket->created_at->format('M d, Y \a\t g:i A') }}</span>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="ticket-info">
                <h3>👤 Customer Information</h3>
                
                <div class="info-row">
                    <span class="label">Name:</span>
                    <span class="value">{{ $customer->name }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span class="value">{{ $customer->email }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Customer Since:</span>
                    <span class="value">{{ $customer->created_at->format('M d, Y') }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Total Tickets:</span>
                    <span class="value">{{ $customer->supportTickets->count() }}</span>
                </div>
            </div>

            <!-- Ticket Message -->
            <h3 style="color: #495057; margin: 25px 0 15px;">💬 Ticket Message</h3>
            <div class="message-content">{{ $ticket->description }}</div>

            <!-- Action Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.url') }}/admin/support/tickets/{{ $ticket->id }}" class="btn">
                    🔍 View Ticket in Admin Panel
                </a>
            </div>

            <!-- Next Steps -->
            <div style="background: #e7f3ff; border: 1px solid #b8daff; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <h4 style="color: #0056b3; margin-top: 0;">📝 Next Steps:</h4>
                <ul style="margin: 10px 0; padding-left: 20px; color: #495057;">
                    <li>Review the ticket details and customer information</li>
                    <li>Assign the ticket to an appropriate agent if needed</li>
                    <li>Respond to the customer within 24 hours</li>
                    <li>Update the ticket status as you progress</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Laravel Cart Support System</strong></p>
            <p>This is an automated notification. Please do not reply to this email.</p>
            <p style="margin-top: 15px;">
                <a href="{{ config('app.url') }}/admin/support" style="color: #667eea; text-decoration: none;">
                    Admin Support Dashboard
                </a>
            </p>
        </div>
    </div>
</body>
</html>