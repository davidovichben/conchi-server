<?php

namespace App\Models;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends BaseModel
{
    use HasFactory;

    protected $fillable = ['code', 'discount', 'start_date', 'end_date', 'is_active'];

    /**
     * Constructor for the Coupon class.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (isset($this->discount) && ($this->discount < 0 || $this->discount > 100)) {
            throw new Exception("Discount must be a percentage between 0 and 100.");
        }

        $this->is_active = $this->is_active ?? false;
    }

    /**
     * Get the coupon code.
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Get the discount amount.
     */
    public function getDiscount(): int
    {
        return $this->discount;
    }

    /**
     * Check if the coupon is still valid.
     */
    public function isValid(): bool
    {
        // Check if the coupon is active
        if (!$this->is_active) {
            return false;
        }

        // Check if the current date is within the start and end date range
        $currentDate = new DateTime();

        if ($this->start_date && $currentDate < new DateTime($this->start_date)) {
            return false; // Coupon is not valid yet
        }

        if ($this->end_date && $currentDate > new DateTime($this->end_date)) {
            return false; // Coupon has expired
        }

        return true;
    }

    /**
     * Apply the coupon to a given amount.
     */
    public function apply(float $amount): float
    {
        if (!$this->isValid()) {
            throw new Exception("Coupon is no longer valid.");
        }

        return max(0, $amount - ($amount * ($this->discount / 100)));
    }

    /**
     * Create a new coupon instance.
     */
    public static function createInstance(array $attributes)
    {
        $coupon = new self($attributes);
        $coupon->save();

        return $coupon;
    }

    /**
     * Update the coupon instance.
     */
    public function updateInstance(array $attributes)
    {
        $this->fill($attributes);
        $this->save();
    }

    public function getStartDate(): ?DateTime
    {
        return $this->start_date ? new DateTime($this->start_date) : null;
    }
    
    public function getEndDate(): ?DateTime
    {
        return $this->end_date ? new DateTime($this->end_date) : null;
    }

    /**
     * Check if the coupon is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Set the coupon's active status.
     */
    public function setActive(bool $isActive): void
    {
        $this->is_active = $isActive;
        $this->save();
    }
}
