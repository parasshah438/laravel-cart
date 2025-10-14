<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function cartItems()
    {
        return $this->hasManyThrough(CartItem::class, Cart::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the user's notification preferences.
     */
    public function notificationPreferences()
    {
        return $this->hasOne(UserNotificationPreference::class);
    }

    /**
     * Get or create notification preferences for this user.
     */
    public function getNotificationPreferences()
    {
        return $this->notificationPreferences ?? 
               $this->notificationPreferences()->create(UserNotificationPreference::getDefaults());
    }

    // ================================================================================================
    // 📝 REVIEW RELATIONSHIPS
    // ================================================================================================
    
    /**
     * Get all reviews written by this user
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get all review helpfulness votes by this user
     */
    public function reviewVotes()
    {
        return $this->hasMany(ReviewHelpfulness::class);
    }

    /**
     * Check if user has reviewed a specific product
     */
    public function hasReviewedProduct($productId): bool
    {
        return $this->reviews()->where('product_id', $productId)->exists();
    }

    /**
     * Get user's review for a specific product
     */
    public function getReviewForProduct($productId)
    {
        return $this->reviews()->where('product_id', $productId)->first();
    }

    /**
     * Get user's review statistics
     */
    public function getReviewStatsAttribute(): array
    {
        $reviews = $this->reviews()->approved();
        
        return [
            'total_reviews' => $reviews->count(),
            'average_rating' => round($reviews->avg('rating'), 1),
            'helpful_votes_received' => $this->reviews()->sum('helpful_count'),
            'verified_reviews' => $reviews->where('verified_purchase', true)->count()
        ];
    }

    // ================================================================================================
    // 🎫 SUPPORT SYSTEM RELATIONSHIPS
    // ================================================================================================
    
    /**
     * Get all support tickets created by this user
     */
    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Get all ticket replies by this user
     */
    public function ticketReplies()
    {
        return $this->hasMany(SupportTicketReply::class);
    }

    /**
     * Get all chat sessions as customer
     */
    public function supportChats()
    {
        return $this->hasMany(SupportChat::class);
    }

    /**
     * Get all chat sessions as agent (for staff)
     */
    public function agentChats()
    {
        return $this->hasMany(SupportChat::class, 'agent_id');
    }

    /**
     * Get all chat messages by this user
     */
    public function chatMessages()
    {
        return $this->hasMany(SupportChatMessage::class);
    }

    /**
     * Check if user has open support tickets
     */
    public function hasOpenTickets(): bool
    {
        return $this->supportTickets()->open()->exists();
    }

    /**
     * Get user's active chat session
     */
    public function getActiveChatAttribute()
    {
        return $this->supportChats()
            ->whereIn('status', [SupportChat::STATUS_WAITING, SupportChat::STATUS_ACTIVE])
            ->latest()
            ->first();
    }

    /**
     * Check if user is a support agent
     */
    public function isSupportAgent(): bool
    {
        // You can implement role-based logic here
        // For now, we'll check if they have any assigned tickets
        return $this->assignedTickets()->exists();
    }

    /**
     * Get user's support statistics
     */
    public function getSupportStatsAttribute(): array
    {
        return [
            'total_tickets' => $this->supportTickets()->count(),
            'open_tickets' => $this->supportTickets()->open()->count(),
            'closed_tickets' => $this->supportTickets()->closed()->count(),
            'total_chats' => $this->supportChats()->count(),
            'avg_ticket_response_time' => $this->getAverageTicketResponseTime(),
        ];
    }

    /**
     * Get average ticket response time in hours
     */
    private function getAverageTicketResponseTime(): ?float
    {
        $tickets = $this->supportTickets()
            ->whereNotNull('first_response_at')
            ->get();

        if ($tickets->isEmpty()) {
            return null;
        }

        $totalHours = $tickets->sum(function ($ticket) {
            return $ticket->created_at->diffInHours($ticket->first_response_at);
        });

        return round($totalHours / $tickets->count(), 1);
    }

    // ================================================================================================
    // 🔐 ROLE & PERMISSION METHODS
    // ================================================================================================

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is agent
     */
    public function isAgent()
    {
        return $this->role === 'agent';
    }

    /**
     * Check if user is customer
     */
    public function isCustomer()
    {
        return $this->role === 'customer' || is_null($this->role);
    }

    /**
     * Check if user can manage support (admin or agent)
     */
    public function canManageSupport()
    {
        return in_array($this->role, ['admin', 'agent']);
    }

    // ================================================================================================
    // 🎫 ADMIN/AGENT SUPPORT RELATIONSHIPS
    // ================================================================================================

    /**
     * Tickets assigned to this agent
     */
    public function assignedTickets()
    {
        return $this->hasMany(SupportTicket::class, 'assigned_agent_id');
    }

    /**
     * Chat sessions handled by this agent
     */
    public function managedChats()
    {
        return $this->hasMany(SupportChat::class, 'agent_id');
    }

    /**
     * Staff replies made by this agent
     */
    public function staffReplies()
    {
        return $this->hasMany(SupportTicketReply::class)->where('is_staff_reply', true);
    }

    // ================================================================================================
    // 📊 AGENT PERFORMANCE METHODS
    // ================================================================================================

    /**
     * Get agent's open tickets count
     */
    public function getOpenTicketsCountAttribute()
    {
        return $this->assignedTickets()->open()->count();
    }

    /**
     * Get agent's closed tickets count
     */
    public function getClosedTicketsCountAttribute()
    {
        return $this->assignedTickets()->closed()->count();
    }

    /**
     * Get agent's average response time (in hours)
     */
    public function getAverageResponseTimeAttribute()
    {
        $replies = $this->staffReplies()
            ->whereHas('ticket', function($query) {
                $query->where('assigned_agent_id', $this->id);
            })
            ->with('ticket')
            ->get();

        if ($replies->isEmpty()) {
            return null;
        }

        $totalHours = $replies->sum(function ($reply) {
            return $reply->ticket->created_at->diffInHours($reply->created_at);
        });

        return round($totalHours / $replies->count(), 1);
    }
}
