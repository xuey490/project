<?php

namespace App\Services;

use App\Models\SysMenu;
use Framework\Basic\BaseService;

class SysMenuService extends BaseService
{
    public function getList(array $params)
    {
        $query = SysMenu::where('del_flag', '0');
        
        if (!empty($params['title'])) {
            $query->where('title', 'like', "%{$params['title']}%");
        }
        
        if (isset($params['enabled']) && $params['enabled'] !== '') {
            $query->where('enabled', (int) $params['enabled']);
        }
        
        $list = $query->orderBy('sort')->get();
        return $this->buildTree($list->toArray());
    }
    
    public function getById(int $id)
    {
        return SysMenu::where('del_flag', '0')->findOrFail($id);
    }
    
    public function create(array $data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return SysMenu::create($data);
    }
    
    public function update(int $id, array $data)
    {
        $menu = SysMenu::where('del_flag', '0')->findOrFail($id);
        
        if (isset($data['pid']) && $data['pid'] == $id) {
             throw new \Exception('Parent menu cannot be self');
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        $menu->update($data);
        return $menu;
    }
    
    public function delete(array $ids): void
    {
        $this->transaction(function () use ($ids) {
            foreach ($ids as $id) {
                $menu = SysMenu::where('del_flag', '0')->findOrFail($id);
                
                // 检查是否有子菜单
                if (SysMenu::where('pid', $id)->where('del_flag', '0')->exists()) {
                    throw new \Exception("Menu ID {$id} has sub-menus, cannot delete");
                }
                
                // 解绑角色
                $menu->roles()->detach();
                
                $menu->update([
                    'del_flag' => '2',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        });
    }
    
    public function changeStatus(int $id, int $enabled): void
    {
        if ($enabled !== 0 && $enabled !== 1) {
            throw new \InvalidArgumentException('Invalid enabled status');
        }
        
        $menu = SysMenu::where('del_flag', '0')->findOrFail($id);
        $menu->update([
            'enabled' => $enabled,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Build menu tree
     */
    private function buildTree(array $elements, int $parentId = 0): array
    {
        $branch = [];
        foreach ($elements as $element) {
            if ($element['pid'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }
    
    /**
     * Get routers for user (frontend format)
     */
    public function getRouters(int $userId, bool $isAdmin = false): array
    {
        $query = SysMenu::where('del_flag', '0')
            ->where('type', '<>', 3) // Exclude buttons
            ->where('enabled', 1)
            ->orderBy('sort');
            
        if (!$isAdmin) {
            $query->whereHas('roles', function($q) use ($userId) {
                $q->whereHas('users', function($u) use ($userId) {
                    $u->where('sys_user.id', $userId);
                });
            });
        }
        
        $menus = $query->get()->toArray();
        return $this->buildRouterTree($menus);
    }
    
    private function buildRouterTree(array $menus, int $parentId = 0): array
    {
        $routers = [];
        foreach ($menus as $menu) {
            if ($menu['pid'] == $parentId) {
                $router = [
                    'name' => $menu['path'] ? ucfirst(ltrim($menu['path'], '/')) : 'NoName' . $menu['id'],
                    'path' => $parentId == 0 ? '/' . ltrim($menu['path'], '/') : $menu['path'],
                    'hidden' => $menu['is_show'] == 0,
                    'redirect' => $menu['type'] == 1 ? 'noRedirect' : null,
                    'component' => $menu['component'] ?? 'Layout',
                    'alwaysShow' => $menu['type'] == 1 && $menu['pid'] == 0,
                    'meta' => [
                        'title' => $menu['title'],
                        'icon' => $menu['icon'],
                        'noCache' => $menu['is_cache'] == 0,
                        'link' => $menu['is_link'] ? $menu['link_url'] : null
                    ]
                ];
                
                $children = $this->buildRouterTree($menus, $menu['id']);
                if (!empty($children)) {
                    $router['children'] = $children;
                }
                
                $routers[] = $router;
            }
        }
        return $routers;
    }
}
