<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends Model
{
    public const STATUS_OFFER = 'offer';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_PROCESSING = 'in_progress';
    public const STATUS_READY = 'ready';
    public const STATUS_READY_FOR_PICKUP = 'ready';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RESERVATION_EXPIRED = 'reservation_expired';

    public const STATUSES = [self::STATUS_OFFER, self::STATUS_IN_PROGRESS, self::STATUS_READY, self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_RESERVATION_EXPIRED];
    public const EXPIRING_RESERVATION_STATUSES = [self::STATUS_OFFER];
    public const RESERVING_STATUSES = [self::STATUS_OFFER, self::STATUS_IN_PROGRESS, self::STATUS_READY];
    public const STATUS_LABELS = [self::STATUS_OFFER => 'Angebot', self::STATUS_IN_PROGRESS => 'In Bearbeitung', self::STATUS_READY => 'Abholbereit', self::STATUS_COMPLETED => 'Erledigt', self::STATUS_CANCELLED => 'Storniert', self::STATUS_RESERVATION_EXPIRED => 'Reservierung abgelaufen'];

    protected $fillable = ['shipping_price_gross', 'shipping_method', 'carton_count', 'offer_number', 'customer_id', 'user_id', 'status', 'template_type', 'document_type', 'subtotal', 'total', 'reserved_until', 'completed_at', 'cancelled_at', 'reservation_released_at', 'notes'];

    protected function casts(): array
    {
        return ['shipping_price_gross' => 'decimal:2', 'carton_count' => 'integer', 'subtotal' => 'decimal:2', 'total' => 'decimal:2', 'reserved_until' => 'datetime', 'completed_at' => 'datetime', 'cancelled_at' => 'datetime', 'reservation_released_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (Offer $offer): void {
            if (! $offer->offer_number) {
                $year = now()->format('Y');
                $lastNumber = Offer::query()->where('offer_number', 'like', 'ANG-' . $year . '-%')->orderByDesc('id')->value('offer_number');
                $lastSequence = $lastNumber ? (int) substr($lastNumber, -3) : 0;
                $offer->offer_number = 'ANG-' . $year . '-' . str_pad((string) ($lastSequence + 1), 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(OfferItem::class); }
    public function internalNotes(): HasMany { return $this->hasMany(OfferInternalNote::class)->latest(); }
    public function isReservationActive(): bool { return in_array($this->status, self::RESERVING_STATUSES, true); }
    public function isFinal(): bool { return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_RESERVATION_EXPIRED], true); }
    public function isCompleted(): bool { return $this->status === self::STATUS_COMPLETED; }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_OFFER => 'Angebot', self::STATUS_IN_PROGRESS => 'In Bearbeitung', self::STATUS_READY => 'Abholbereit', self::STATUS_COMPLETED => 'Erledigt', self::STATUS_CANCELLED => 'Storniert', self::STATUS_RESERVATION_EXPIRED => 'Reservierung abgelaufen', default => $this->status,
        };
    }

    public function templateLabel(): string
    {
        return match ($this->template_type) {
            'with_company' => 'Mit Firmendaten & Logo', 'without_company' => 'Ohne Firmendaten & Logo', default => $this->template_type,
        };
    }
}
