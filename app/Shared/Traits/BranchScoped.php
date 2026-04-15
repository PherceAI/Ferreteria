<?php

declare(strict_types=1);

namespace App\Shared\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Context;

trait BranchScoped
{
    protected static function bootBranchScoped(): void
    {
        static::addGlobalScope('branch', function (Builder $builder): void {
            if (static::shouldBypassBranchScope()) {
                return;
            }

            $branchId = Context::get('branch_id');

            if ($branchId === null) {
                if (auth()->check() && ! app()->runningInConsole()) {
                    $builder->whereRaw('1 = 0');
                }

                return;
            }

            $builder->where(
                $builder->getModel()->qualifyColumn($builder->getModel()->getBranchScopeColumn()),
                $branchId,
            );
        });

        static::creating(function (Model $model): void {
            if (static::shouldBypassBranchScope()) {
                return;
            }

            $branchId = Context::get('branch_id');
            $column = $model->getBranchScopeColumn();

            if ($branchId !== null && empty($model->getAttribute($column))) {
                $model->setAttribute($column, $branchId);
            }
        });
    }

    public function getBranchScopeColumn(): string
    {
        return property_exists($this, 'branchScopeColumn')
            ? $this->branchScopeColumn
            : 'branch_id';
    }

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query
            ->withoutGlobalScope('branch')
            ->where($this->qualifyColumn($this->getBranchScopeColumn()), $branchId);
    }

    public function scopeWithoutBranchScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('branch');
    }

    protected static function shouldBypassBranchScope(): bool
    {
        return (bool) Context::getHidden('branch_scope_bypass', false);
    }
}
