<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\SupportChat;
use App\Models\SupportChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Mail\SupportTicketStatusUpdate;
use App\Mail\SupportTicketReply as SupportTicketReplyMail;

class AdminSupportController extends Controller
{
    public function __construct()
    {
        // Ensure only admin users can access these routes
        //$this->middleware(['auth', 'admin']);
    }

    // ================================================================================================
    // 📊 ADMIN DASHBOARD & ANALYTICS
    // ================================================================================================

    /**
     * Admin Support Dashboard
     */
    public function dashboard()
    {
        // Dashboard statistics
        $totalTickets = SupportTicket::count();
        $openTickets = SupportTicket::where('status', 'open')->count();
        $inProgressTickets = SupportTicket::where('status', 'in_progress')->count();
        $todayTickets = SupportTicket::whereDate('created_at', Carbon::today())->count();
        $closedTickets = SupportTicket::where('status', 'closed')->count();
        $resolvedTickets = SupportTicket::where('status', 'resolved')->count();
        $urgentTickets = SupportTicket::where('priority', 'high')->count();
        $unassignedTickets = SupportTicket::whereNull('assigned_agent_id')->count();

        // Recent tickets
        $recentTickets = SupportTicket::with(['user', 'assignedAgent'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get all agents for assignment dropdowns
        $agents = User::where('role', 'agent')->orWhere('role', 'admin')->get();

        // Agent performance statistics
        $agentStats = User::where('role', 'agent')
            ->withCount([
                'assignedTickets',
                'assignedTickets as open_tickets_count' => function($query) {
                    $query->where('status', 'open');
                },
                'assignedTickets as closed_tickets_count' => function($query) {
                    $query->where('status', 'closed');
                }
            ])
            ->get();

        // Ticket trends (last 7 days)
        $ticketTrends = SupportTicket::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.support.dashboard', compact(
            'totalTickets', 'openTickets', 'inProgressTickets', 'todayTickets', 
            'closedTickets', 'resolvedTickets', 'urgentTickets', 'unassignedTickets',
            'recentTickets', 'agents', 'agentStats', 'ticketTrends'
        ));
    }

    // ================================================================================================
    // 🎫 TICKET MANAGEMENT
    // ================================================================================================

    /**
     * List all tickets for admin
     */
    public function tickets(Request $request)
    {
        $query = SupportTicket::with(['user', 'assignedAgent', 'latestReply']);

        // Filters
        if ($request->filled('status')) {
            if ($request->status === 'open') {
                $query->open();
            } elseif ($request->status === 'closed') {
                $query->closed();
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('agent')) {
            if ($request->agent === 'unassigned') {
                $query->whereNull('assigned_agent_id');
            } else {
                $query->where('assigned_agent_id', $request->agent);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->orderBy('last_activity_at', 'desc')
                        ->paginate(20)
                        ->withQueryString();

        // Get agents for assignment dropdown
        $agents = User::where('role', 'agent')->orWhere('role', 'admin')->get();

        return view('admin.support.tickets.index', compact('tickets', 'agents'));
    }

    /**
     * Show create ticket form
     */
    public function create()
    {
        $agents = User::where('role', 'agent')->orWhere('role', 'admin')->get();
        $customers = User::where('role', 'customer')->get();
        
        return view('admin.support.tickets.create', compact('agents', 'customers'));
    }

    /**
     * Store new ticket
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'category' => 'nullable|string',
            'assigned_agent_id' => 'nullable|exists:users,id',
        ]);

        $validated['status'] = 'open';
        $validated['last_activity_at'] = now();

        $ticket = SupportTicket::create($validated);

        return redirect()->route('admin.support.tickets.show', $ticket)
                        ->with('success', 'Ticket created successfully.');
    }

    /**
     * Show edit ticket form
     */
    public function edit(SupportTicket $ticket)
    {
        $agents = User::where('role', 'agent')->orWhere('role', 'admin')->get();
        $customers = User::where('role', 'customer')->get();
        
        return view('admin.support.tickets.edit', compact('ticket', 'agents', 'customers'));
    }

    /**
     * Update ticket
     */
    public function update(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:open,in_progress,resolved,closed',
            'category' => 'nullable|string',
            'assigned_agent_id' => 'nullable|exists:users,id',
        ]);

        $validated['last_activity_at'] = now();
        $ticket->update($validated);

        return redirect()->route('admin.support.tickets.show', $ticket)
                        ->with('success', 'Ticket updated successfully.');
    }

    /**
     * Delete ticket
     */
    public function destroy(SupportTicket $ticket)
    {
        $ticket->delete();

        return redirect()->route('admin.support.tickets.index')
                        ->with('success', 'Ticket deleted successfully.');
    }

    /**
     * Show specific ticket for admin
     */
    public function show(SupportTicket $ticket)
    {
        $ticket->load([
            'user',
            'assignedAgent',
            'replies.user'
        ]);

        // Get available agents for assignment
        $agents = User::where('role', 'agent')->orWhere('role', 'admin')->get();

        return view('admin.support.tickets.show', compact('ticket', 'agents'));
    }

    /**
     * Admin reply to ticket
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:5|max:5000',
            'is_internal_note' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('error', 'Please enter a valid message.');
        }

        try {
            DB::beginTransaction();

            // Create the reply
            SupportTicketReply::create([
                'support_ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'message' => $request->message,
                'is_staff_reply' => true,
                'is_internal_note' => $request->boolean('is_internal_note', false),
            ]);

            // Update ticket status if not internal note
            if (!$request->boolean('is_internal_note', false)) {
                $ticket->update([
                    'status' => 'pending', // Customer needs to respond
                    'last_activity_at' => now(),
                ]);

                // Send email notification to customer about new admin reply
                try {
                    Mail::to($ticket->user->email)->send(new SupportTicketReply($ticket, Auth::user(), $request->message));
                } catch (\Exception $e) {
                    Log::error('Failed to send admin reply email notification: ' . $e->getMessage());
                    // Don't fail the entire operation if email fails
                }
            }

            DB::commit();

            $message = $request->boolean('is_internal_note', false) ? 'Internal note added successfully.' : 'Reply sent successfully.';
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin ticket reply error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Assign ticket to agent
     */
    public function assign(Request $request, SupportTicket $ticket)
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('error', 'Please select a valid agent.');
        }

        // Verify the user is an agent
        $agent = User::where('id', $request->agent_id)->where('role', 'agent')->first();
        if (!$agent) {
            return back()->with('error', 'Selected user is not an agent.');
        }

        $ticket->update([
            'assigned_agent_id' => $request->agent_id,
            'status' => 'assigned',
            'last_activity_at' => now(),
        ]);

        // Add system message
        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => "Ticket assigned to {$agent->name}",
            'is_staff_reply' => true,
            'is_internal_note' => true,
        ]);

        return back()->with('success', "Ticket assigned to {$agent->name} successfully.");
    }

    /**
     * Close ticket (admin)
     */
    public function close(Request $request, SupportTicket $ticket)
    {
        if ($ticket->isClosed()) {
            return back()->with('error', 'This ticket is already closed.');
        }

        $oldStatus = $ticket->status;

        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by_admin' => true,
            'last_activity_at' => now(),
        ]);

        // Add system message
        if ($request->filled('close_message')) {
            SupportTicketReply::create([
                'support_ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'message' => $request->close_message,
                'is_staff_reply' => true,
                'is_internal_note' => false,
            ]);
        }

        // Send email notification to customer about ticket closure
        try {
            Mail::to($ticket->user->email)->send(new SupportTicketStatusUpdate($ticket, $oldStatus, 'closed'));
        } catch (\Exception $e) {
            Log::error('Failed to send ticket closure email notification: ' . $e->getMessage());
            // Don't fail the entire operation if email fails
        }

        return back()->with('success', 'Ticket closed successfully.');
    }

    /**
     * Reopen ticket
     */
    public function reopen(SupportTicket $ticket)
    {
        if (!$ticket->isClosed()) {
            return back()->with('error', 'This ticket is not closed.');
        }

        $oldStatus = $ticket->status;

        $ticket->update([
            'status' => 'open',
            'closed_at' => null,
            'closed_by_admin' => false,
            'last_activity_at' => now(),
        ]);

        // Add system message
        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => 'Ticket reopened by admin',
            'is_staff_reply' => true,
            'is_internal_note' => true,
        ]);

        // Send email notification to customer about ticket reopening
        try {
            Mail::to($ticket->user->email)->send(new SupportTicketStatusUpdate($ticket, $oldStatus, 'open'));
        } catch (\Exception $e) {
            Log::error('Failed to send ticket reopen email notification: ' . $e->getMessage());
            // Don't fail the entire operation if email fails
        }

        return back()->with('success', 'Ticket reopened successfully.');
    }

    /**
     * Add internal note
     */
    public function addInternalNote(Request $request, SupportTicket $ticket)
    {
        $validator = Validator::make($request->all(), [
            'note' => 'required|string|min:5|max:5000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('error', 'Please enter a valid note.');
        }

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->note,
            'is_staff_reply' => true,
            'is_internal_note' => true,
        ]);

        return back()->with('success', 'Internal note added successfully.');
    }

    /**
     * Update ticket priority
     */
    public function updatePriority(Request $request, SupportTicket $ticket)
    {
        $validator = Validator::make($request->all(), [
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('error', 'Please select a valid priority.');
        }

        $oldPriority = $ticket->priority;
        $ticket->update([
            'priority' => $request->priority,
            'last_activity_at' => now(),
        ]);

        // Add system message
        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => "Priority changed from {$oldPriority} to {$request->priority}",
            'is_staff_reply' => true,
            'is_internal_note' => true,
        ]);

        return back()->with('success', 'Priority updated successfully.');
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:open,pending,assigned,resolved,closed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('error', 'Please select a valid status.');
        }

        $oldStatus = $ticket->status;
        $updateData = [
            'status' => $request->status,
            'last_activity_at' => now(),
        ];

        // Handle closed status
        if ($request->status === 'closed' && !$ticket->isClosed()) {
            $updateData['closed_at'] = now();
            $updateData['closed_by_admin'] = true;
        } elseif ($request->status !== 'closed' && $ticket->isClosed()) {
            $updateData['closed_at'] = null;
            $updateData['closed_by_admin'] = false;
        }

        $ticket->update($updateData);

        // Add system message
        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => "Status changed from {$oldStatus} to {$request->status}",
            'is_staff_reply' => true,
            'is_internal_note' => true,
        ]);

        // Send email notification to customer about status change
        if ($oldStatus !== $request->status) {
            try {
                Mail::to($ticket->user->email)->send(new SupportTicketStatusUpdate($ticket, $oldStatus, $request->status));
            } catch (\Exception $e) {
                Log::error('Failed to send status update email notification: ' . $e->getMessage());
                // Don't fail the entire operation if email fails
            }
        }

        return back()->with('success', 'Status updated successfully.');
    }

    // ================================================================================================
    // 👥 AGENT MANAGEMENT
    // ================================================================================================

    /**
     * List agents and their performance
     */
    public function agents()
    {
        $agents = User::where('role', 'agent')
            ->withCount([
                'assignedTickets',
                'assignedTickets as open_tickets_count' => function($query) {
                    $query->open();
                },
                'assignedTickets as closed_tickets_count' => function($query) {
                    $query->closed();
                },
                'assignedTickets as this_month_count' => function($query) {
                    $query->whereMonth('created_at', Carbon::now()->month);
                }
            ])
            ->get();

        return view('admin.support.agents.index', compact('agents'));
    }

    /**
     * Show agent's tickets
     */
    public function agentTickets(User $agent)
    {
        if ($agent->role !== 'agent') {
            abort(404, 'Agent not found.');
        }

        $tickets = $agent->assignedTickets()
            ->with(['user', 'latestReply'])
            ->orderBy('last_activity_at', 'desc')
            ->paginate(20);

        return view('admin.support.agents.tickets', compact('agent', 'tickets'));
    }

    // ================================================================================================
    // 💬 LIVE CHAT MANAGEMENT
    // ================================================================================================

    /**
     * List all chat sessions
     */
    public function chats()
    {
        $chats = SupportChat::with(['user', 'agent'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.support.chats.index', compact('chats'));
    }

    /**
     * Show specific chat
     */
    public function showChat(SupportChat $chat)
    {
        $chat->load(['user', 'agent', 'messages.user']);
        return view('admin.support.chats.show', compact('chat'));
    }

    /**
     * Join a chat as an agent
     */
    public function joinChat(SupportChat $chat)
    {
        $user = Auth::user();
        
        // Update chat status and assign agent
        $chat->update([
            'status' => 'active',
            'agent_id' => $user->id,
            'agent_name' => $user->name,
        ]);

        // Add system message
        SupportChatMessage::create([
            'support_chat_id' => $chat->id,
            'user_id' => $user->id,
            'message' => "{$user->name} has joined the chat",
            'is_from_agent' => true,
            'message_type' => SupportChatMessage::TYPE_SYSTEM,
        ]);

        return redirect()->route('admin.support.chats.show', $chat)
            ->with('success', 'You have joined the chat successfully');
    }

    /**
     * Send a message as an agent
     */
    public function sendChatMessage(Request $request, SupportChat $chat)
    {
        // Debug: Log the incoming request
        \Log::info('Admin chat message request:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            \Log::error('Admin chat message validation failed:', $validator->errors()->toArray());
            return response()->json(['error' => 'Invalid message', 'details' => $validator->errors()], 400);
        }

        $user = Auth::user();

        // Don't allow messages on ended chats
        if ($chat->status === 'ended') {
            return response()->json(['error' => 'This chat has ended'], 400);
        }

        try {
            $message = SupportChatMessage::create([
                'support_chat_id' => $chat->id,
                'user_id' => $user->id,
                'message' => $request->message,
                'is_from_agent' => true,
                'message_type' => SupportChatMessage::TYPE_MESSAGE,
            ]);

            $message->load('user');

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_type' => 'Agent',
                    'created_at' => $message->created_at->format('g:i A'),
                    'sender_name' => $message->user->name,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Agent chat message error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send message'], 500);
        }
    }

    /**
     * End a chat session
     */
    public function endChat(SupportChat $chat)
    {
        $user = Auth::user();
        
        $chat->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        // Add system message
        SupportChatMessage::create([
            'support_chat_id' => $chat->id,
            'user_id' => $user->id,
            'message' => "Chat ended by {$user->name}",
            'is_from_agent' => true,
            'message_type' => SupportChatMessage::TYPE_SYSTEM,
        ]);

        return redirect()->route('admin.support.chats')
            ->with('success', 'Chat session ended successfully');
    }

    // ================================================================================================
    // 📈 ANALYTICS & REPORTS
    // ================================================================================================

    /**
     * Analytics dashboard
     */
    public function analytics()
    {
        // Ticket trends
        $ticketTrends = SupportTicket::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Resolution time analytics
        $avgResolutionTime = SupportTicket::closed()
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as avg_hours')
            ->value('avg_hours');

        // Category breakdown
        $categoryStats = SupportTicket::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get();

        // Priority breakdown
        $priorityStats = SupportTicket::selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get();

        return view('admin.support.analytics', compact(
            'ticketTrends', 
            'avgResolutionTime', 
            'categoryStats', 
            'priorityStats'
        ));
    }

    // ================================================================================================
    // ⚙️ SETTINGS & CONFIGURATION
    // ================================================================================================

    /**
     * Support settings
     */
    public function settings()
    {
        return view('admin.support.settings');
    }

    /**
     * Update support settings
     */
    public function updateSettings(Request $request)
    {
        // Settings logic here
        return back()->with('success', 'Settings updated successfully.');
    }

    // ================================================================================================
    // 👥 USER MANAGEMENT
    // ================================================================================================

    /**
     * List users for role management
     */
    public function users()
    {
        $users = User::paginate(50);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Update user role
     */
    public function updateUserRole(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:customer,agent,admin',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('error', 'Please select a valid role.');
        }

        $user->update(['role' => $request->role]);

        return back()->with('success', "User role updated to {$request->role} successfully.");
    }
}