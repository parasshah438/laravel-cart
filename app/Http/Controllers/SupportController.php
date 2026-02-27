<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\SupportChat;
use App\Models\SupportChatMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Mail\ContactFormSubmitted;
use App\Mail\NewSupportTicket;
use App\Mail\SupportTicketStatusUpdate;
use App\Mail\SupportTicketReply as SupportTicketReplyMail;

class SupportController extends Controller
{
    // ================================================================================================
    // 🎫 SUPPORT TICKET MANAGEMENT
    // ================================================================================================

    /**
     * Show the support dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        $stats = [
            'total_tickets' => $user->supportTickets()->count(),
            'open_tickets' => $user->supportTickets()->open()->count(),
            'closed_tickets' => $user->supportTickets()->closed()->count(),
            'active_chat' => $user->activeChat,
        ];

        $recentTickets = $user->supportTickets()
            ->with(['latestReply.user'])
            ->orderBy('last_activity_at', 'desc')
            ->limit(5)
            ->get();

        return view('support.index', compact('stats', 'recentTickets'));
    }

    /**
     * Show all tickets for the user
     */
    public function tickets(Request $request)
    {
        $user = Auth::user();
        $query = $user->supportTickets()->with(['latestReply.user']);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'open') {
                $query->open();
            } elseif ($request->status === 'closed') {
                $query->closed();
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search in subject and description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('last_activity_at', 'desc')
                        ->paginate(10)
                        ->withQueryString();

        return view('support.tickets.index', compact('tickets'));
    }

    /**
     * Show form to create new ticket
     */
    public function create()
    {
        // Get user's recent orders for order-related tickets
        $recentOrders = Auth::user()->orders()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('support.tickets.create', compact('recentOrders'));
    }

    /**
     * Store a new support ticket
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'description' => 'required|string|min:10|max:5000',
            'category' => 'required|in:' . implode(',', [
                SupportTicket::CATEGORY_GENERAL,
                SupportTicket::CATEGORY_ORDER,
                SupportTicket::CATEGORY_PRODUCT,
                SupportTicket::CATEGORY_BILLING,
                SupportTicket::CATEGORY_TECHNICAL,
                SupportTicket::CATEGORY_COMPLAINT
            ]),
            'priority' => 'required|in:' . implode(',', [
                SupportTicket::PRIORITY_LOW,
                SupportTicket::PRIORITY_NORMAL,
                SupportTicket::PRIORITY_HIGH,
                SupportTicket::PRIORITY_URGENT
            ]),
            'order_id' => 'nullable|exists:orders,id',
            'product_id' => 'nullable|exists:products,id',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below.');
        }

        try {
            DB::beginTransaction();

            $ticket = SupportTicket::create([
                'user_id' => Auth::id(),
                'subject' => $request->subject,
                'description' => $request->description,
                'category' => $request->category,
                'priority' => $request->priority,
                'order_id' => $request->order_id,
                'product_id' => $request->product_id,
                'last_activity_at' => now(),
            ]);

            // Create initial message as a reply
            SupportTicketReply::create([
                'support_ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'message' => $request->description,
                'is_staff_reply' => false,
                'is_internal_note' => false,
            ]);

            // Send notification email to admin users
            try {
                $adminUsers = User::where('role', 'admin')->orWhere('role', 'agent')->get();
                foreach ($adminUsers as $admin) {
                    Mail::to($admin->email)->send(new NewSupportTicket($ticket));
                }
                Log::info('New ticket notification emails sent to admins for ticket #' . $ticket->id);
            } catch (\Exception $e) {
                Log::error('Failed to send new ticket notification emails: ' . $e->getMessage());
                // Don't fail the ticket creation if email fails
            }

            DB::commit();

            return redirect()->route('support.show', $ticket)
                ->with('success', 'Your support ticket has been created successfully. Ticket #' . $ticket->id);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Support ticket creation error: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Something went wrong while creating your ticket. Please try again.');
        }
    }

    /**
     * Show a specific ticket
     */
    public function show(SupportTicket $ticket)
    {
        // Ensure user can only see their own tickets
        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to view this ticket.');
        }

        $ticket->load([
            'user',
            'order',
            'product',
            'assignedAgent',
            'replies.user'
        ]);

        return view('support.tickets.show', compact('ticket'));
    }

    /**
     * Add a reply to a ticket
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        // Ensure user can only reply to their own tickets
        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to reply to this ticket.');
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:5|max:5000',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->with('error', 'Please enter a valid message.');
        }

        try {
            $reply = SupportTicketReply::create([
                'support_ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'message' => $request->message,
                'is_staff_reply' => false,
                'is_internal_note' => false,
            ]);

            // Update ticket last activity
            $ticket->update(['last_activity_at' => now()]);

            // Send notification email to admin users
            try {
                $adminUsers = User::where('role', 'admin')->orWhere('role', 'agent')->get();
                foreach ($adminUsers as $admin) {
                    Mail::to($admin->email)->send(new SupportTicketReplyMail($ticket, $reply));
                }
                Log::info('Reply notification emails sent to admins for ticket #' . $ticket->id);
            } catch (\Exception $e) {
                Log::error('Failed to send reply notification emails: ' . $e->getMessage());
                // Don't fail the reply if email fails
            }

            return back()->with('success', 'Your reply has been added successfully.');

        } catch (\Exception $e) {
            Log::error('Support ticket reply error: ' . $e->getMessage());
            
            return back()->with('error', 'Something went wrong while adding your reply. Please try again.');
        }
    }

    /**
     * Close a ticket
     */
    public function close(SupportTicket $ticket)
    {
        // Ensure user can only close their own tickets
        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to close this ticket.');
        }

        if ($ticket->isClosed()) {
            return back()->with('error', 'This ticket is already closed.');
        }

        $ticket->close();

        return back()->with('success', 'Ticket has been closed successfully.');
    }

    // ================================================================================================
    // 💬 LIVE CHAT FUNCTIONALITY
    // ================================================================================================

    /**
     * Show chat interface
     */
    public function chat()
    {
        $user = Auth::user();
        $activeChat = $user->activeChat;

        // If no active chat, create a new one
        if (!$activeChat) {
            $activeChat = SupportChat::create([
                'user_id' => $user->id,
                'status' => SupportChat::STATUS_WAITING,
                'subject' => 'Support Chat - ' . now()->format('M j, Y g:i A'),
            ]);

            // Add welcome message
            SupportChatMessage::create([
                'support_chat_id' => $activeChat->id,
                'user_id' => $user->id,
                'message' => 'Hello! How can we help you today?',
                'is_from_agent' => false,
                'message_type' => SupportChatMessage::TYPE_SYSTEM,
            ]);
        }

        $activeChat->load(['messages.user']);

        return view('support.chat', compact('activeChat'));
    }

    /**
     * Send a chat message
     */
    public function sendMessage(Request $request)
    {
        // Debug: Log the incoming request
        \Log::info('Chat message request:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'chat_id' => 'required|exists:support_chats,id',
        ]);

        if ($validator->fails()) {
            \Log::error('Chat message validation failed:', $validator->errors()->toArray());
            return response()->json(['error' => 'Invalid message', 'details' => $validator->errors()], 400);
        }

        $user = Auth::user();
        $chat = SupportChat::find($request->chat_id);

        // Ensure user owns this chat
        if ($chat->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Don't allow messages on ended chats
        if ($chat->hasEnded()) {
            return response()->json(['error' => 'This chat has ended'], 400);
        }

        try {
            $message = SupportChatMessage::create([
                'support_chat_id' => $chat->id,
                'user_id' => $user->id,
                'message' => $request->message,
                'is_from_agent' => false,
                'message_type' => SupportChatMessage::TYPE_MESSAGE,
            ]);

            $message->load('user');

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'message' => $message->message, // Use raw message instead of formatted_message
                    'sender_type' => $message->sender_type,
                    'created_at' => $message->created_at->format('g:i A'),
                    'sender_name' => $message->user->name,
                    'user' => [
                        'name' => $message->user->name,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Chat message error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send message'], 500);
        }
    }

    /**
     * Start a new chat session
     */
    public function startChat(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Check if user has an active chat
            $activeChat = $user->activeChat;
            if ($activeChat) {
                return redirect()->route('support.chat')->with('info', 'You already have an active chat session.');
            }

            // Create new chat session
            $chat = SupportChat::create([
                'user_id' => $user->id,
                'status' => 'waiting',
                'started_at' => now(),
            ]);

            // Send initial system message
            $chat->messages()->create([
                'user_id' => $user->id,
                'message' => 'Chat session started. Please wait for an agent to join.',
                'sender_type' => 'system',
            ]);

            return redirect()->route('support.chat')->with('success', 'Chat session started successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to start chat session: ' . $e->getMessage());
            return redirect()->route('support.chat')->with('error', 'Failed to start chat session.');
        }
    }

    /**
     * End a chat session
     */
    public function endChat(SupportChat $chat)
    {
        try {
            $user = Auth::user();
            
            // Ensure user owns this chat
            if ($chat->user_id !== $user->id) {
                abort(403, 'Unauthorized access to chat session.');
            }

            // End the chat
            $chat->update([
                'status' => 'ended',
                'ended_at' => now(),
            ]);

            // Send system message
            $chat->messages()->create([
                'user_id' => $user->id,
                'message' => 'Chat session ended by customer.',
                'sender_type' => 'system',
            ]);

            return redirect()->route('support.chat')->with('success', 'Chat session ended successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to end chat session: ' . $e->getMessage());
            return redirect()->route('support.chat')->with('error', 'Failed to end chat session.');
        }
    }

    // ================================================================================================
    // 📞 CONTACT & HELP PAGES
    // ================================================================================================

    /**
     * Show the contact form
     */
    public function contact()
    {
        return view('support.contact');
    }

    /**
     * Handle contact form submission
     */
    public function submitContact(Request $request)
    {
        // Validate the form data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:2000',
        ], [
            'name.required' => 'Please enter your name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'subject.required' => 'Please enter a subject.',
            'message.required' => 'Please enter your message.',
            'message.min' => 'Your message must be at least 10 characters long.',
            'message.max' => 'Your message cannot exceed 2000 characters.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below and try again.');
        }

        try {
            // Store the contact in database
            $contact = Contact::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => Contact::STATUS_NEW,
            ]);

            // Send email notification to admin
            $adminEmail = config('mail.admin_email', 'admin@laravel-cart.com');

            try {
                Mail::to($adminEmail)->queue(new ContactFormSubmitted($contact));
            } catch (\Exception $mailException) {
                // Log mail error but don't fail the request
                Log::error('Failed to queue contact form email: ' . $mailException->getMessage());
            }

            return back()
                ->with('success', 'Thank you for contacting us! We have received your message and will get back to you within 24 hours.')
                ->without('error');

        } catch (\Exception $e) {
            Log::error('Contact form submission error: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Something went wrong while sending your message. Please try again later.');
        }
    }

    /**
     * Show help/FAQ page
     */
    public function help()
    {
        $faqs = [
            [
                'category' => 'Orders & Shipping',
                'questions' => [
                    [
                        'question' => 'How can I track my order?',
                        'answer' => 'You can track your order by logging into your account and visiting the "My Orders" section. Each order will have a tracking number and current status.'
                    ],
                    [
                        'question' => 'What are your shipping options?',
                        'answer' => 'We offer standard shipping (5-7 business days), express shipping (2-3 business days), and overnight shipping. Shipping costs vary by location and order value.'
                    ],
                    [
                        'question' => 'Can I change or cancel my order?',
                        'answer' => 'Orders can be modified or cancelled within 1 hour of placement. After that, please contact our support team for assistance.'
                    ]
                ]
            ],
            [
                'category' => 'Returns & Refunds',
                'questions' => [
                    [
                        'question' => 'What is your return policy?',
                        'answer' => 'We offer a 30-day return policy for most items. Items must be in original condition with all packaging and tags intact.'
                    ],
                    [
                        'question' => 'How do I process a return?',
                        'answer' => 'To process a return, go to your order history, select the item you want to return, and follow the return process. You\'ll receive a prepaid return label.'
                    ],
                    [
                        'question' => 'When will I receive my refund?',
                        'answer' => 'Refunds are processed within 3-5 business days after we receive your returned item. The funds will appear in your original payment method within 5-10 business days.'
                    ]
                ]
            ],
            [
                'category' => 'Account & Technical',
                'questions' => [
                    [
                        'question' => 'How do I reset my password?',
                        'answer' => 'Click on "Forgot Password" on the login page and enter your email address. You\'ll receive instructions to reset your password.'
                    ],
                    [
                        'question' => 'Can I change my email address?',
                        'answer' => 'Yes, you can update your email address in your account settings. You\'ll need to verify the new email address before the change takes effect.'
                    ],
                    [
                        'question' => 'Why am I having trouble placing an order?',
                        'answer' => 'Common issues include payment method problems, shipping address errors, or out-of-stock items. Please check these details or contact support for assistance.'
                    ]
                ]
            ]
        ];

        return view('support.help', compact('faqs'));
    }

    /**
     * Show FAQ page
     */
    public function faq()
    {
        $faqs = [
            [
                'category' => 'Orders & Shipping',
                'questions' => [
                    [
                        'question' => 'How can I track my order?',
                        'answer' => 'You can track your order by logging into your account and visiting the "My Orders" section. Each order will have a tracking number and current status.'
                    ],
                    [
                        'question' => 'What are your shipping options?',
                        'answer' => 'We offer standard shipping (5-7 business days), express shipping (2-3 business days), and overnight shipping. Shipping costs vary by location and order value.'
                    ],
                    [
                        'question' => 'Can I change or cancel my order?',
                        'answer' => 'Orders can be modified or cancelled within 1 hour of placement. After that, please contact our support team for assistance.'
                    ]
                ]
            ],
            [
                'category' => 'Returns & Refunds',
                'questions' => [
                    [
                        'question' => 'What is your return policy?',
                        'answer' => 'We offer a 30-day return policy for most items. Items must be in original condition with all packaging and tags intact.'
                    ],
                    [
                        'question' => 'How do I process a return?',
                        'answer' => 'To process a return, go to your order history, select the item you want to return, and follow the return process. You\'ll receive a prepaid return label.'
                    ],
                    [
                        'question' => 'When will I receive my refund?',
                        'answer' => 'Refunds are processed within 3-5 business days after we receive your returned item. The funds will appear in your original payment method within 5-10 business days.'
                    ]
                ]
            ],
            [
                'category' => 'Account & Technical',
                'questions' => [
                    [
                        'question' => 'How do I reset my password?',
                        'answer' => 'Click on "Forgot Password" on the login page and enter your email address. You\'ll receive instructions to reset your password.'
                    ],
                    [
                        'question' => 'Can I change my email address?',
                        'answer' => 'Yes, you can update your email address in your account settings. You\'ll need to verify the new email address before the change takes effect.'
                    ],
                    [
                        'question' => 'Why am I having trouble placing an order?',
                        'answer' => 'Common issues include payment method problems, shipping address errors, or out-of-stock items. Please check these details or contact support for assistance.'
                    ]
                ]
            ]
        ];

        return view('support.faq', compact('faqs'));
    }
}
