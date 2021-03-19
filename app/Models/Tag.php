<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tags';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    public function articles()
    {
        return $this->morphByMany(Article::class, 'taggable');
    }

    public function tasks()
    {
        return $this->morphByMany(Task::class, 'taggable');
    }

    public function materials()
    {
        return $this->morphByMany(Material::class, 'taggable');
    }

    public function taggables()
    {
        return $this->morphTo('taggable');
    }

}
