<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Form Submission</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #0d6efd, #0056b3);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        
        .content {
            padding: 30px;
        }
        
        .contact-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid #0d6efd;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 15px;
            align-items: flex-start;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            width: 120px;
            flex-shrink: 0;
        }
        
        .info-value {
            color: #212529;
            flex: 1;
            word-break: break-word;
        }
        
        .message-content {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
            white-space: pre-wrap;
            line-height: 1.7;
        }
        
        .timestamp {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 25px;
            text-align: center;
            color: #0d6efd;
            font-weight: 500;
        }
        
        .action-buttons {
            text-align: center;
            margin: 25px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin: 0 10px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545862;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #e9ecef;
        }
        
        .priority-high {
            background: #fff3cd;
            border-left-color: #ffc107;
        }
        
        .priority-urgent {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 0;
            }
            
            .header, .content {
                padding: 20px;
            }
            
            .info-row {
                flex-direction: column;
            }
            
            .info-label {
                width: auto;
                margin-bottom: 5px;
            }
            
            .btn {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📧 New Contact Form Submission</h1>
            <p>You have received a new message from your website</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <!-- Contact Information -->
            <div class="contact-info {{ $contact->subject === 'Technical Issue' ? 'priority-urgent' : ($contact->subject === 'Order Issue' ? 'priority-high' : '') }}">
                <div class="info-row">
                    <div class="info-label">👤 Name:</div>
                    <div class="info-value">{{ $contact->name }}</div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">📧 Email:</div>
                    <div class="info-value">
                        <a href="mailto:{{ $contact->email }}" style="color: #0d6efd; text-decoration: none;">
                            {{ $contact->email }}
                        </a>
                    </div>
                </div>
                
                @if($contact->phone)
                <div class="info-row">
                    <div class="info-label">📱 Phone:</div>
                    <div class="info-value">
                        <a href="tel:{{ $contact->phone }}" style="color: #0d6efd; text-decoration: none;">
                            {{ $contact->phone }}
                        </a>
                    </div>
                </div>
                @endif
                
                <div class="info-row">
                    <div class="info-label">🏷️ Subject:</div>
                    <div class="info-value">
                        <strong>{{ $contact->subject }}</strong>
                        @if($contact->subject === 'Technical Issue')
                            <span style="background: #dc3545; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 8px;">URGENT</span>
                        @elseif($contact->subject === 'Order Issue')
                            <span style="background: #ffc107; color: #000; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 8px;">HIGH PRIORITY</span>
                        @endif
                    </div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">🆔 Ticket ID:</div>
                    <div class="info-value"><code>#{{ str_pad($contact->id, 6, '0', STR_PAD_LEFT) }}</code></div>
                </div>
            </div>
            
            <!-- Message Content -->
            <div style="margin-top: 25px;">
                <h3 style="color: #495057; margin-bottom: 15px; display: flex; align-items: center;">
                    💬 Message Content
                </h3>
                <div class="message-content">{{ $contact->message }}</div>
            </div>
            
            <!-- Quick Actions -->
            <div class="action-buttons">
                <a href="mailto:{{ $contact->email }}?subject=Re: {{ $contact->subject }}&body=Hi {{ $contact->name }},%0D%0A%0D%0AThank you for contacting us regarding: {{ $contact->subject }}%0D%0A%0D%0A" 
                   class="btn">
                    ↩️ Reply to Customer
                </a>
                
                @if($contact->phone)
                <a href="tel:{{ $contact->phone }}" class="btn btn-secondary">
                    📞 Call Customer
                </a>
                @endif
            </div>
            
            <!-- Timestamp -->
            <div class="timestamp">
                📅 Received on {{ $contact->created_at->format('l, F j, Y \a\t g:i A T') }}
                <br>
                <small style="opacity: 0.7;">{{ $contact->created_at->diffForHumans() }}</small>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0;">
                <strong>{{ config('app.name', 'Laravel Cart') }}</strong> - Contact Management System
            </p>
            <p style="margin: 5px 0 0;">
                This email was automatically generated when a customer submitted a contact form on your website.
            </p>
        </div>
    </div>
</body>
</html>