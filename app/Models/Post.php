<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "category_id",
        "title",
        "slug",
        "likes",
        "dislikes",
        "content",
    ];

    protected $appends = [
        "title_with_author"
    ];

    protected $casts = [
        "created_at" => "datetime:Y-m-d"
    ];

    /**protected $with = [
        "user:id,name,email",
    ];*/

    // El metodo booted se ejecuta despues de que se haya creado el modelo. Osea despues que se use el metodo Post
    // Este metodo se ejecuta una sola vez. En este caso estamos creando un scope global que se ejecutara en todas las consultas Eloquent
    protected static function booted() {
        static::addGlobalScope("currentMonth", function (Builder $builder) {
            $builder->whereMonth("created_at", now()->month);
        });
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class)->withDefault([
            "id" => -1,
            "name" => "No existe",
        ]);
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany {
        return $this->belongsToMany(Tag::class);
    }

    public function sortedTags(): BelongsToMany {
        return $this->belongsToMany(Tag::class)
            ->orderBy("tag");
    }

    public function setTitleAttribute(string $title) {
        $this->attributes["title"] = $title;
        $this->attributes["slug"] = Str::slug($title);
    }

    public function getTitleWithAuthorAttribute(): string {
        return sprintf("%s - %s", $this->title, $this->user->name);
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeWhereHasTagsWithTags(Builder $builder): Builder
    {
        return $builder
            ->select(["id", "title"])
            ->with("tags:id,tag")
            ->whereHas("tags");
    }
}
