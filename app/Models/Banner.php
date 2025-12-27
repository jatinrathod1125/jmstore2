<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
     protected $fillable = ['title', 'html', 'css', 'is_active', 'js', 'preview_image'];
}
