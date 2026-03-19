<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait DataScopeTrait
{
    /**
     * Data Scope Scope
     * @param Builder $query
     * @param mixed $user
     */
    public function scopeDataScope($query, $user)
    {
        if (!$user || empty($user->roles)) {
            return $query;
        }

        // 1=All, 2=Custom, 3=Dept, 4=Dept+Sub, 5=Self
        $isAll = false;
        $conditions = [];

        foreach ($user->roles as $role) {
            $ds = $role->data_scope;
            if ($ds == '1') {
                $isAll = true;
                break;
            }
            if ($ds == '2') {
                // Custom: role_dept table
                 $deptIds = $role->depts->pluck('dept_id')->toArray();
                 if (!empty($deptIds)) {
                     $conditions[] = ['type' => 'dept', 'ids' => $deptIds];
                 }
            } elseif ($ds == '3') {
                // Dept
                $conditions[] = ['type' => 'dept', 'ids' => [$user->dept_id]];
            } elseif ($ds == '4') {
                // Dept + Sub
                $conditions[] = ['type' => 'dept_sub', 'dept_id' => $user->dept_id];
            } elseif ($ds == '5') {
                // Self
                $conditions[] = ['type' => 'self', 'user_id' => $user->user_id];
            }
        }

        if ($isAll) {
            return $query;
        }
        
        // If no roles or conditions, maybe deny all? Or allow? 
        // Usually if no data scope is defined, we might default to Self. 
        // But for now if no conditions, return query (no restriction) or empty?
        // Let's assume strict: if not All, must match one condition.
        if (empty($conditions)) {
            // No permissions granted explicitly in roles (and not All)
            // -> return empty result?
            // Or maybe the user has no roles with data scope.
            return $query->whereRaw('1=0'); 
        }

        $query->where(function ($q) use ($conditions) {
            foreach ($conditions as $cond) {
                if ($cond['type'] == 'dept') {
                    $q->orWhereIn('dept_id', $cond['ids']);
                } elseif ($cond['type'] == 'dept_sub') {
                     $q->orWhereIn('dept_id', function($subQ) use ($cond) {
                         $subQ->select('dept_id')
                              ->from('sys_dept')
                              ->where('dept_id', $cond['dept_id'])
                              ->orWhereRaw("CONCAT(',', ancestors, ',') LIKE ?", ['%,' . $cond['dept_id'] . ',%']);
                     });
                } elseif ($cond['type'] == 'self') {
                    // Heuristic for column name
                    $table = $this->getTable();
                    $col = $table == 'sys_user' ? 'user_id' : 'created_by'; // Default columns
                    // Note: sys_article uses author_id
                    if ($table == 'sys_article') $col = 'author_id';
                    
                    $q->orWhere($col, $cond['user_id']);
                }
            }
        });
        
        return $query;
    }
}
