<?php
// app/Models/PermissionsGroup.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionsGroup extends Model
{
    use HasFactory;

    protected $table = 'permissions_group';
    public $timestamps = false;

    protected $fillable = [
        'group_id',
        'name',
        'target'
    ];

    protected $primaryKey = 'group_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // 获取目标数组
    public function getPermissionTarget()
    {
        return explode('|', $this->target);
    }


    // 添加权限组 TODO:对target进行拆分校验
    public static function addGroup(string $groupId, string $name, string $target)
    {
        try {

            // 检查ID是否已存在
            if (self::where('group_id', $groupId)->exists()) {
                return [
                    'success' => false,
                    'message' => '权限组ID已存在'
                ];
            }

            $group = self::create([
                'group_id' => $groupId,
                'name' => $name,
                'target' => $target
            ]);

            return $group;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '组创建失败: ' . $e->getMessage()
            ];
        }
    }

    // 检查是否包含某个目标
    public function hasTarget($target): bool
    {
        $targets = $this->getPermissionTarget();
        return in_array($target, $targets);
    }

    // 根据权限组ID查询权限组名称
    public static function getGroupName($groupId)
    {
        return self::where('group_id', $groupId)->value('name');
    }

    // 获取所有权限组
    public static function getAllGroups()
    {
        return self::orderBy('group_id')->get(['group_id', 'name']);
    }

    // 根据权限组ID删除权限组
    public static function deleteGroup(string $groupId)
    {
        try {
            // 查找权限组
            $group = self::where('group_id', $groupId)->first();

            if (!$group) {
                return [
                    'success' => false,
                    'message' => '权限组不存在'
                ];
            }

            // 删除权限组
            $deleted = $group->delete();

            if ($deleted) {
                return [
                    'success' => true,
                    'message' => '权限组删除成功'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => '权限组删除失败'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '删除权限组时发生错误: ' . $e->getMessage()
            ];
        }
    }

    // 查询所有权限组
    public static function getGroupList($queryCount = 0, $queryWord = '', $limit = 20)
    {
        $query = self::query();

        if (!empty($queryWord)) {
            $query->where('name', 'like', '%' . $queryWord . '%');
        }

        return $query->offset($queryCount)
            ->limit($limit)
            ->get();
    }

    // 获取权限组数量
    public static function getGroupCount(?string $queryWord = ''): int
    {
        $query = self::query();

        if (!empty($queryWord)) {
            $query->where('name', 'like', '%' . $queryWord . '%');
        }

        return $query->count();
    }


    // 修改权限组
    public static function editGroup(string $permissionsGroupId, string $permissionsGroupName, string $classList)
    {
        try {
            // 查找对应的权限组
            $permissionsGroup = PermissionsGroup::where('group_id', $permissionsGroupId)
                ->first();

            if (!$permissionsGroup) {
                return [
                    'success' => false,
                    'message' => '权限组不存在'
                ];
            }

            $permissionsGroup->name = $permissionsGroupName;
            $permissionsGroup->target = $classList;
            $permissionsGroup->save();

            return [
                'success' => true,
                'message' => '权限组修改成功',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '修改权限组失败: ' . $e->getMessage()
            ];
        }
    }
}
