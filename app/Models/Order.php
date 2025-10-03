<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    protected $fillable = ['order_number','customer_id','status','total'];
    use HasFactory;
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PLACED = 'placed';
    public const STATUS_PAID = 'paid';
    public const STATUS_FULFILLED = 'fulfilled';
    public const STATUS_CANCELLED = 'cancelled';


    public function lines()
    {
        return $this->hasMany(OrderLine::class);
    }


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    public function recalcTotal()
    {
        $total = $this->lines()->sum('line_total');
        $this->total = $total;
        $this->save();
        return $total;
    }
}
