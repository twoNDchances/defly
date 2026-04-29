<?php

namespace App\Models;

use App\Enums\Defender\DeploymentStatus;
use App\Enums\Defender\Status;
use App\Observers\DefenderObserver;
use App\Traits\Models\Labellable;
use App\Traits\Models\Owner;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'proxy_port', 'environment_variables', 'status', 'details', 'deployment_status', 'deployment_details', 'description', 'created_by'])]
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
            'description' => 'string',
            'created_by' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function principles()
    {
        return $this->belongsToMany(Principle::class, 'defenders_principles', 'defender', 'principle')
            ->withPivot('order')
            ->orderByPivot('order');
    }

    public function decisions()
    {
        return $this->belongsToMany(Decision::class, 'defenders_decisions', 'defender', 'decision')
            ->withPivot('order')
            ->orderByPivot('order');
    }
}
