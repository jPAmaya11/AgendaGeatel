<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'notebook_id',
        'owner_id',
        'title',
        'category',
        'content',
        'is_pinned',
        'position',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function notebook()
    {
        return $this->belongsTo(Notebook::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'note_shares')
            ->withPivot('can_edit')
            ->withTimestamps();
    }

    public function sharedWith()
    {
        return $this->belongsToMany(User::class, 'note_shares')
            ->withPivot('can_edit') // view | edit
            ->withTimestamps();
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function canEdit(User $user): bool
    {
        if ($this->isOwner($user)) {
            return true;
        }

        return $this->sharedWith()
            ->where('user_id', $user->id)
            ->wherePivot('can_edit', true)
            ->exists();
    }

    public function isSharedWith(User $user): bool
    {
        return $this->sharedWith()
            ->where('user_id', $user->id)
            ->exists();
    }
}
