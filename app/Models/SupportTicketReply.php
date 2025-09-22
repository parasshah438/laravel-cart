<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class SupportTicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'message',
        'is_staff_reply',
        'is_internal_note',
        'attachments',
    ];

    protected $casts = [
        'is_staff_reply' => 'boolean',
        'is_internal_note' => 'boolean',
        'attachments' => 'array',
    ];

    /**
     * Get the ticket this reply belongs to
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    /**
     * Get the user who wrote this reply
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only customer replies
     */
    public function scopeCustomerReplies($query)
    {
        return $query->where('is_staff_reply', false);
    }

    /**
     * Scope to get only staff replies
     */
    public function scopeStaffReplies($query)
    {
        return $query->where('is_staff_reply', true);
    }

    /**
     * Scope to get public replies (not internal notes)
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal_note', false);
    }

    /**
     * Scope to get internal notes only
     */
    public function scopeInternalNotes($query)
    {
        return $query->where('is_internal_note', true);
    }

    /**
     * Check if this reply is from staff
     */
    public function isFromStaff(): bool
    {
        return $this->is_staff_reply;
    }

    /**
     * Check if this is an internal note
     */
    public function isInternalNote(): bool
    {
        return $this->is_internal_note;
    }

    /**
     * Get the author type (customer/staff)
     */
    public function getAuthorTypeAttribute(): string
    {
        return $this->is_staff_reply ? 'Staff' : 'Customer';
    }

    /**
     * Get formatted message with attachments
     */
    public function getFormattedMessageAttribute(): string
    {
        $message = nl2br(e($this->message));
        
        if ($this->attachments && count($this->attachments) > 0) {
            $message .= '<br><br><strong>Attachments:</strong><br>';
            foreach ($this->attachments as $attachment) {
                $message .= '<a href="' . Storage::url($attachment) . '" target="_blank">' . basename($attachment) . '</a><br>';
            }
        }
        
        return $message;
    }

    /**
     * Boot method to update ticket activity when reply is created
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($reply) {
            // Update ticket's last activity and first response time
            $ticket = $reply->ticket;
            
            $updateData = ['last_activity_at' => now()];
            
            // If this is the first staff reply, set first_response_at
            if ($reply->is_staff_reply && !$ticket->first_response_at) {
                $updateData['first_response_at'] = now();
            }
            
            // Update ticket status if needed
            if ($reply->is_staff_reply && $ticket->status === SupportTicket::STATUS_WAITING_CUSTOMER) {
                $updateData['status'] = SupportTicket::STATUS_IN_PROGRESS;
            } elseif (!$reply->is_staff_reply && $ticket->status === SupportTicket::STATUS_IN_PROGRESS) {
                $updateData['status'] = SupportTicket::STATUS_WAITING_CUSTOMER;
            }
            
            $ticket->update($updateData);
        });
    }
}