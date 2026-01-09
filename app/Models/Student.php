<?php
// app/Models/Student.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasApiTokens, HasFactory;

    // 指定表名
    protected $table = 'student';

    // 主键字段名
    protected $primaryKey = 'id';

    // 主键类型
    protected $keyType = 'int';

    // 是否自动递增
    public $incrementing = true;

    // 是否维护时间戳
    public $timestamps = false;

    // 可批量赋值的字段
    protected $fillable = [
        'name',
        'password',
        'account',
        'date',
        'level',
        'class_id',
        'permissions_group_id',
    ];

    // 隐藏的字段（如密码）
    protected $hidden = [
        'password',
    ];

    // 字段类型转换
    protected $casts = [
        'level' => 'integer',
    ];

    // 缓存属性
    protected $permissionsGroupCache = null;
    protected static $userClassCache = [];

    //获取权限组
    protected function getPermissionsGroup()
    {
        if ($this->level >= 10) {
            return null;
        }

        if ($this->permissionsGroupCache === null && $this->permissions_group_id) {
            $this->permissionsGroupCache = PermissionsGroup::find($this->permissions_group_id);
        }

        return $this->permissionsGroupCache;
    }


    // 检查班级权限
    protected function checkClassPermission($classId = null, $account = null)
    {
        if ($this->level >= 10) {
            return true;
        }

        $permissionsGroup = $this->getPermissionsGroup();
        if (!$permissionsGroup) {
            return false;
        }

        if ($account) {
            $targetClassId = self::getUserClass($account);
            return $targetClassId && $permissionsGroup->hasTarget($targetClassId);
        }

        return $classId && $permissionsGroup->hasTarget($classId);
    }

    // 生成token
    public function createTokenWithExpiry(string $name, bool $remember = false, array $abilities = ['*'])
    {
        $expiresAt = $remember
            ? now()->addWeeks(4)
            : now()->addHours(2);

        return $this->createToken($name, $abilities, $expiresAt);
    }

    // 密码Hash
    public static function hashPassword(string $password)
    {
        return hash_hmac("sha256", $password, config('app.password_sign'));
    }

    // 创建学生
    public function createStudent(
        string $account,
        string $name,
        string $password,
        ?string $classId = null,
        ?int $level = 0,
        ?string $permissionsGroupId = null,
    ) {
        // 权限检查
        if ($this->level < 10) {
            $permissionsGroup = $this->getPermissionsGroup();
            if (!$permissionsGroup) {
                return null;
            }
            if ($classId && !$permissionsGroup->hasTarget($classId)) {
                return null;
            }
        }

        try {
            if ($classId) {
                if (!ClassModel::where('class_id', $classId)->exists()) {
                    return [
                        'success' => false,
                        'message' => '班级不存在'
                    ];
                }
            }

            if ($permissionsGroupId) {
                if (!PermissionsGroup::where('group_id', $permissionsGroupId)->exists()) {
                    return [
                        'success' => false,
                        'message' => '权限组不存在'
                    ];
                }
            }

            // 检查账号是否重复
            if (self::where('account', $account)->exists()) {
                return [
                    'success' => false,
                    'message' => '账号已存在'
                ];
            }

            $student = new self();
            $student->account = $account;
            $student->name = $name;
            $student->password = self::hashPassword($password);

            if ($level !== null) {
                $student->level = $level;
            }

            if ($classId !== null) {
                $student->class_id = $classId;
            }

            if ($permissionsGroupId !== null) {
                $student->permissions_group_id = $permissionsGroupId;
            }

            // 保存到数据库
            $student->save();

            if ($classId !== null) {
                ClassModel::updateTargetClass($classId);
            }

            return [
                'success' => true,
                'message' => '账号创建成功',
                'data' => null
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '创建账号失败: ' . $e->getMessage()
            ];
        }
    }

    // 删除对象
    public function deleteByAccount(string $account)
    {
        if (!$this->checkClassPermission(null, $account)) {
            return null;
        }

        try {
            $student = self::where('account', $account)->first();

            if (!$student) {
                return [
                    'success' => false,
                    'message' => '账号不存在'
                ];
            }

            $classId = $student->class_id;

            // 删除用户
            $student->delete();

            if ($classId) {
                ClassModel::updateTargetClass($classId);
            }

            // 清除缓存
            unset(self::$userClassCache[$account]);

            return [
                'success' => true,
                'message' => '账号删除成功'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '删除账号失败: ' . $e->getMessage()
            ];
        }
    }

    // 判断传入的class_id是否在用户的权限组里
    public function isClassInPermission($classId): bool
    {
        return $this->checkClassPermission($classId);
    }

    // 获取管理员的所属班级
    public function getStudentPermissionClass()
    {
        if ($this->level == 10) {
            return ClassModel::getAllClasses();
        }

        $permissionsGroup = $this->getPermissionsGroup();
        if (!$permissionsGroup) {
            return [];
        }

        $classList = $permissionsGroup->getPermissionTarget();

        return ClassModel::query()
            ->whereIn('class_id', $classList)
            ->get()
            ->toArray();
    }

    // 根据班级查询学生
    public function getStudentByClass($classId, $queryWord, $queryCount, $limit = 20)
    {
        if (!$this->checkClassPermission($classId)) {
            return null;
        }

        $query = $this->query()->where('class_id', $classId);

        if (!empty($queryWord)) {
            $query->where('name', 'like', $queryWord . '%');
        }

        return $query->offset($queryCount)
            ->limit($limit)
            ->get();
    }

    // 查询所有管理员
    public static function getAdminList($queryCount = 0, $queryWord = '', $limit = 20)
    {
        $query = self::where('level', 5);

        if (!empty($queryWord)) {
            $query->where('name', 'like', $queryWord . '%');
        }

        return $query->offset($queryCount)
            ->limit($limit)
            ->get();
    }

    // 获取管理员数量
    public static function getAdminCount(?string $queryWord = ''): int
    {
        $query = self::where('level', 5);

        if (!empty($queryWord)) {
            $query->where('name', 'like', $queryWord . '%');
        }

        return $query->count();
    }

    // 获取学生班级
    public static function getUserClass($account)
    {
        if (!isset(self::$userClassCache[$account])) {
            self::$userClassCache[$account] = self::where('account', $account)
                ->value('class_id');
        }

        return self::$userClassCache[$account];
    }

    // 获取学生数量
    public static function getStudentCount(): int
    {
        return self::count();
    }

    // 获取学生名字
    public static function getName($account)
    {
        return self::where('account', $account)->value('name');
    }

    // 根据班级获取学生数量
    public function getCountByClass(string $classId, ?string $queryWord = '')
    {
        if (!$this->checkClassPermission($classId)) {
            return null;
        }

        $query = self::where('class_id', $classId);

        if (!empty($queryWord)) {
            $query->where('name', 'like', $queryWord . '%');
        }

        return $query->count();
    }

    // 修改密码
    public static function editAdminPassword(string $account, string $password)
    {
        $affected = self::where('account', $account)
            ->where('level', 5)
            ->update(['password' => self::hashPassword($password)]);

        if ($affected === 0) {
            return [
                'success' => false,
                'message' => '管理员账号不存在'
            ];
        }

        return [
            'success' => true,
            'message' => '密码修改成功'
        ];
    }

    // 修改学生密码
    public function editPassword(string $account, string $password)
    {
        if (!$this->checkClassPermission(null, $account) && $this->account != $account) {
            return null;
        }

        $affected = self::where('account', $account)
            ->update(['password' => self::hashPassword($password)]);

        if ($affected === 0) {
            return [
                'success' => false,
                'message' => '账号不存在'
            ];
        }

        return [
            'success' => true,
            'message' => '密码修改成功'
        ];
    }

    // 修改管理员
    public static function editAdmin(string $account, string $name, ?string $classId, string $permissionGroupId)
    {
        try {
            // 先验证权限组是否存在
            if (!PermissionsGroup::where('group_id', $permissionGroupId)->exists()) {
                return [
                    'success' => false,
                    'message' => '权限组不存在'
                ];
            }

            // 验证班级是否存在（如果class_id不为空）
            if ($classId && !ClassModel::where('class_id', $classId)->exists()) {
                return [
                    'success' => false,
                    'message' => '班级不存在'
                ];
            }

            // 更新管理员信息
            $affected = self::where('account', $account)
                ->where('level', 5)
                ->update([
                    'name' => $name,
                    'class_id' => $classId,
                    'permissions_group_id' => $permissionGroupId
                ]);

            if ($affected === 0) {
                return [
                    'success' => false,
                    'message' => '管理员账号不存在'
                ];
            }

            return [
                'success' => true,
                'message' => '管理员信息修改成功',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '修改管理员信息失败: ' . $e->getMessage()
            ];
        }
    }

    // 修改用户
    public function edit(string $account, string $name, string $classId)
    {
        // 检查源账号和目标班级权限
        if (!$this->checkClassPermission(null, $account) || !$this->checkClassPermission($classId)) {
            return null;
        }

        try {
            // 验证班级是否存在
            if (!ClassModel::where('class_id', $classId)->exists()) {
                return [
                    'success' => false,
                    'message' => '班级不存在'
                ];
            }

            // 更新用户信息
            $affected = self::where('account', $account)
                ->update([
                    'class_id' => $classId,
                    'name' => $name
                ]);

            if ($affected === 0) {
                return [
                    'success' => false,
                    'message' => '账号不存在'
                ];
            }

            // 清除缓存
            unset(self::$userClassCache[$account]);

            return [
                'success' => true,
                'message' => '信息修改成功',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '修改信息失败: ' . $e->getMessage()
            ];
        }
    }

    // 设置用户的权限组
    public static function setPermissionGroup($account, $permissionGroupId)
    {
        try {
            // 先检查权限组是否存在
            if (!PermissionsGroup::where('group_id', $permissionGroupId)->exists()) {
                return [
                    'success' => false,
                    'message' => '权限组不存在'
                ];
            }

            // 更新权限组
            $affected = self::where('account', $account)
                ->update(['permissions_group_id' => $permissionGroupId,
                'level' => 5,
            ]);

            if ($affected === 0) {
                return [
                    'success' => false,
                    'message' => '用户不存在'
                ];
            }

            return [
                'success' => true,
                'message' => '权限组设置成功',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '设置权限组错误: ' . $e->getMessage()
            ];
        }
    }

    // 获取待办
    public function getTodos()
    {
        return Activity::select(['activity_id', 'title', 'status', 'start_time', 'stop_time'])
            ->where('class_id', $this->class_id)
            ->where('status', 1)
            ->orderBy('start_time', 'asc')
            ->limit(50)
            ->get();
    }

    // 获取已开始
    public function getStarted()
    {
        return Activity::select(['activity_id', 'title', 'status', 'start_time', 'stop_time'])
            ->where('class_id', $this->class_id)
            ->whereIn('status', [1, 2])
            ->orderBy('start_time', 'asc')
            ->limit(50)
            ->get();
    }
}
