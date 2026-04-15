<?php

declare(strict_types=1);

namespace App\Domain\EtlBridge\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

abstract class TiniRawModel extends Model
{
    protected $connection = 'pgsql';

    public function save(array $options = []): bool
    {
        $this->guardWriteAccess();

        return parent::save($options);
    }

    public function delete(): ?bool
    {
        $this->guardWriteAccess();

        return parent::delete();
    }

    public function deleteOrFail(): ?bool
    {
        $this->guardWriteAccess();

        return parent::deleteOrFail();
    }

    protected function guardWriteAccess(): void
    {
        if (! app()->bound('etl.write-enabled') || app('etl.write-enabled') !== true) {
            throw new LogicException('tini_raw models are read-only for the application.');
        }
    }
}
