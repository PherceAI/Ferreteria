<?php

namespace Tests\Fixtures;

use App\Shared\Traits\Auditable;
use App\Shared\Traits\BranchScoped;
use App\Shared\Traits\Encryptable;
use Illuminate\Database\Eloquent\Model;

class OperationalRecord extends Model
{
    use Auditable, BranchScoped, Encryptable;

    protected $table = 'operational_records';

    public $timestamps = false;

    protected $fillable = [
        'branch_id',
        'name',
        'secret',
    ];

    protected array $encryptable = [
        'secret',
    ];
}
