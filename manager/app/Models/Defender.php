<?php

namespace App\Models;

use App\Enums\Defender\DeploymentStatus;
use App\Enums\Defender\Status;
use App\Observers\DefenderObserver;
use App\Traits\Models\Labellable;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'proxy_port', 'environment_variables', 'status', 'details', 'deployment_status', 'deployment_details', 'last_response_details', 'description', 'created_by'])]
#[ObservedBy(DefenderObserver::class)]
class Defender extends Model
{
    use HasUuids, Labellable, Owner;

    protected function casts()
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'proxy_port' => 'integer',
            'environment_variables' => 'array',
            'status' => Status::class,
            'details' => 'array',
            'deployment_status' => DeploymentStatus::class,
            'deployment_details' => 'array',
            'last_response_details' => 'array',
            'description' => 'string',
            'created_by' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function principles()
    {
        return $this->belongsToMany(Principle::class, 'defenders_principles', 'defender', 'principle')
            ->withPivot('order', 'is_applied')
            ->orderByPivot('order');
    }

    public function decisions()
    {
        return $this->belongsToMany(Decision::class, 'defenders_decisions', 'defender', 'decision')
            ->withPivot('order', 'is_implemented')
            ->orderByPivot('order');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'created_by');
    }

    public function guards()
    {
        return $this->belongsToMany(Guard::class, 'guards_defenders', 'defender', 'guard');
    }

    #[Scope]
    protected function visibleTo(Builder $query, ?User $user): void
    {
        $query->where(function (Builder $query) use ($user): void {
            $query->whereDoesntHave('guards');

            if (! $user) {
                return;
            }

            $query->orWhere($query->qualifyColumn('created_by'), $user->getKey())
                ->orWhereHas('guards', fn (Builder $query): Builder => $query
                    ->active()
                    ->whereHas('users', fn (Builder $query): Builder => $query->whereKey($user->getKey())));
        });
    }
}
