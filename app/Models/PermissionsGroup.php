<?php
// app/Models/PermissionsGroup.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

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

    // 缓存权限目标数组
    private ?array $permissionTargetCache = null;

    // 获取目标数组
    public function getPermissionTarget(): array
    {
        if ($this->permissionTargetCache === null) {
            $this->permissionTargetCache = explode('|', $this->target);
        }
        return $this->permissionTargetCache;
    }

    // 添加权限组
    public static function addGroup(string $groupId, string $name, string $target): Model|array
    {

        if (self::where('group_id', $groupId)->exists()) {
            return [
                'success' => false,
                'message' => '权限组ID已存在'
            ];
        }

        try {
            $result = self::insert([
                'group_id' => $groupId,
                'name' => $name,
                'target' => $target
            ]);

            if (!$result) {
                return [
                    'success' => false,
                    'message' => '组创建失败'
                ];
            }

            return self::find($groupId);
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '组创建失败: ' . $e->getMessage()
            ];
        }
    }

    // 检查是否包含某个目标
    public function hasTarget(string $target): bool
    {
        $targets = $this->getPermissionTarget();
        return in_array($target, $targets, true);
    }

    // 根据权限组ID查询权限组名称
    public static function getGroupName(string $groupId): ?string
    {
        return self::where('group_id', $groupId)
            ->value('name');
    }

    // 获取所有权限组（简略信息）
    public static function getAllGroups(): Collection
    {
        return self::select(['group_id', 'name'])
            ->orderBy('group_id')
            ->get();
    }

    // 根据权限组ID删除权限组
    public static function deleteGroup(string $groupId): array
    {
        try {
            $deleted = self::where('group_id', $groupId)->delete();

            if ($deleted) {
                return [
                    'success' => true,
                    'message' => '权限组删除成功'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => '权限组不存在或删除失败'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '删除权限组时发生错误: ' . $e->getMessage()
            ];
        }
    }

    // 查询权限组列表
    public static function getGroupList(int $queryCount = 0, ?string $queryWord = '', int $limit = 20): Collection
    {
        $query = self::query();

        if (!empty($queryWord)) {
            $query->where('name', 'like', $queryWord . '%');
        }

        return $query->select(['group_id', 'name', 'target'])
            ->orderBy('group_id')
            ->offset($queryCount)
            ->limit($limit)
            ->get();
    }

    // 获取权限组数量
    public static function getGroupCount(?string $queryWord = ''): int
    {
        $query = self::query();

        if (!empty($queryWord)) {
            $query->where('name', 'like', $queryWord . '%');
        }

        return $query->count();
    }

    // 修改权限组
    public static function editGroup(string $permissionsGroupId, string $permissionsGroupName, string $classList): array
    {
        // 参数验证
        if (empty($permissionsGroupName) || empty($classList)) {
            return [
                'success' => false,
                'message' => '权限组名称和目标列表不能为空'
            ];
        }

        try {
            $affected = self::where('group_id', $permissionsGroupId)
                ->update([
                    'name' => $permissionsGroupName,
                    'target' => $classList
                ]);

            if ($affected > 0) {
                return [
                    'success' => true,
                    'message' => '权限组修改成功',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => '权限组不存在'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '修改权限组失败: ' . $e->getMessage()
            ];
        }
    }

}