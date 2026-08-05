<?php
namespace App\Models;
use App\Models\Club;
use App\Models\EventRegistration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Event extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'description',
        'location',
        'date',
        'capacity',
        'image',
        'club_id',
    ];
protected function casts(): array
{
    return [
        'date' => 'datetime',
    ];
}
    public function club()
    {
        return $this->belongsTo(Club::class);
    }
    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }
}