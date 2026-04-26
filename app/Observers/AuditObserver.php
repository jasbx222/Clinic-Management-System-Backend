<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    public function created(Model $model)
    {
        $this->log('create', $model);
    }

    public function updated(Model $model)
    {
        $this->log('update', $model);
    }

    public function deleted(Model $model)
    {
        $this->log('delete', $model);
    }

    protected function log(string $action, Model $model)
    {
        if (! Auth::check()) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        if ($action === 'update') {
            $newValues = $model->getDirty();
            $oldValues = array_intersect_key($model->getOriginal(), $newValues);
        } elseif ($action === 'create') {
            $newValues = $model->getAttributes();
        } elseif ($action === 'delete') {
            $oldValues = $model->getAttributes();
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id ?? 0,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
