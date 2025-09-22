<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Reply on Support Ticket</title>
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
        .reply-info {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .agent-reply {
            border-left-color: #667eea;
            background: #e7f3ff;
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
        .reply-content {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            white-space: pre-wrap;
            line-height: 1.7;
        }
        .agent-badge {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
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
            <h1>💬 New Reply Received</h1>
            <p style="margin: 10px 0 0; opacity: 0.9;">Someone replied to your support ticket</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Hello <strong>{{ $customer->name }}</strong>,</p>
            
            <p>You have received a new reply on your support ticket. Here are the details:</p>

            <!-- Reply Information -->
            <div class="reply-info {{ $reply->user->isAgent() ? 'agent-reply' : '' }}">
                <h3 style="margin-top: 0; color: #495057;">
                    💬 New Reply 
                    @if($reply->user->isAgent())
                    <span class="agent-badge">Support Agent</span>
                    @endif
                </h3>
                
                <div class="info-row">
                    <span class="label">From:</span>
                    <span class="value">
                        {{ $agent->name }}
                        @if($reply->user->isAgent()) (Support Agent) @endif
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="label">Replied on:</span>
                    <span class="value">{{ $reply->created_at->format('M d, Y \a\t g:i A') }}</span>
                </div>
                
                @if($reply->is_internal)
                <div class="info-row">
                    <span class="label">Type:</span>
                    <span class="value" style="color: #ffc107; font-weight: bold;">Internal Note</span>
                </div>
                @endif
            </div>

            <!-- Reply Content -->
            @if(!$reply->is_internal)
            <h3 style="color: #495057; margin: 25px 0 15px;">📝 Reply Message</h3>
            <div class="reply-content">{{ $reply->message }}</div>
            @else
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <h4 style="color: #856404; margin-top: 0;">🔒 Internal Note</h4>
                <p style="color: #856404; margin-bottom: 0;">
                    This reply contains internal notes for our support team and is not visible to customers.
                </p>
            </div>
            @endif

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
                    <span class="label">Status:</span>
                    <span class="value">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Priority:</span>
                    <span class="value">{{ ucfirst($ticket->priority) }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Total Replies:</span>
                    <span class="value">{{ $ticket->replies->count() }}</span>
                </div>
            </div>

            <!-- Action Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.url') }}/support/tickets/{{ $ticket->id }}" class="btn">
                    💬 View & Reply to Ticket
                </a>
            </div>

            <!-- Response Guidelines -->
            <div style="background: #e7f3ff; border: 1px solid #b8daff; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <h4 style="color: #0056b3; margin-top: 0;">📋 Next Steps:</h4>
                <ul style="margin: 10px 0; padding-left: 20px; color: #495057;">
                    @if($reply->user->isAgent())
                    <li>Review the agent's response carefully</li>
                    <li>If the issue is resolved, please confirm in your reply</li>
                    <li>If you need further clarification, don't hesitate to ask</li>
                    <li>Provide any additional information that might be helpful</li>
                    @else
                    <li>This reply is from another user or system notification</li>
                    <li>Please review the content and respond if needed</li>
                    @endif
                </ul>
            </div>

            <!-- Quick Actions -->
            <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center;">
                <h4 style="color: #495057; margin-top: 0;">Quick Actions</h4>
                <p style="margin: 15px 0;">
                    <a href="{{ config('app.url') }}/support/tickets/{{ $ticket->id }}" style="color: #667eea; text-decoration: none; margin: 0 15px;">
                        📖 View Full Conversation
                    </a>
                    <a href="{{ config('app.url') }}/support/tickets" style="color: #667eea; text-decoration: none; margin: 0 15px;">
                        📋 My All Tickets
                    </a>
                    <a href="{{ config('app.url') }}/support" style="color: #667eea; text-decoration: none; margin: 0 15px;">
                        🏠 Support Center
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Laravel Cart Support Team</strong></p>
            <p>You're receiving this email because you have an active support ticket with us.</p>
            <p style="margin-top: 15px;">
                <a href="{{ config('app.url') }}/support/tickets/{{ $ticket->id }}" style="color: #667eea; text-decoration: none;">
                    Reply to this ticket
                </a> | 
                <a href="{{ config('app.url') }}/support" style="color: #667eea; text-decoration: none;">
                    Support Center
                </a>
            </p>
        </div>
    </div>
</body>
</html>