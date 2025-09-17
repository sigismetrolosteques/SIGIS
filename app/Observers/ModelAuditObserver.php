<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use App\Models\Audit;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

class ModelAuditObserver
{
    protected function audit(Model $model, string $action)
    {
        // Sólo auditar si el modelo tiene key (evita problemas)
        $old = null;
        $new = null;

        if ($action === 'created') {
            $new = $model->getAttributes();
        } elseif ($action === 'deleted') {
            $old = $model->getOriginal();
        } elseif ($action === 'updated') {
            $old = $model->getOriginal();
            $new = $model->getAttributes();
        }

        // Evitar auditar la tabla audits para no loop
        if ($model instanceof \App\Models\Audit) {
            return;
        }

        Audit::create([
            'user_id' => Auth::id(),
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'old_values' => $old ? $this->filterAuditArray($old) : null,
            'new_values' => $new ? $this->filterAuditArray($new) : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ]);
    }

    protected function filterAuditArray(array $arr): array
    {
        // elimina campos que no quieras guardar (por ejemplo password, remember_token)
        $exclude = ['password', 'remember_token'];
        foreach ($exclude as $key) {
            if (array_key_exists($key, $arr)) {
                $arr[$key] = null;
            }
        }
        return $arr;
    }

    public function created(Model $model)
    {
        $this->audit($model, 'created');
    }

    public function updated(Model $model)
    {
        $this->audit($model, 'updated');
    }

    public function deleted(Model $model)
    {
        $this->audit($model, 'deleted');
    }

    // opcional: restored or forceDeleted si deseas
}
