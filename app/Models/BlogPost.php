<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','title','slug','excerpt','body','thumbnail','category','published_at'];
    protected $casts = ['published_at' => 'datetime'];

    public function author() { return $this->belongsTo(User::class, 'user_id'); }

    public function getRouteKeyName() { return 'slug'; }
}
