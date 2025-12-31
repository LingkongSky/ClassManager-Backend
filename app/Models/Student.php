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
    protected $table = 'students';

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
        'id' => 'integer',
        'level' => 'integer',
    ];

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

        if ($this->level < 10) {
            $permissionsGroup = PermissionsGroup::find($this->permissions_group_id);
            if (!$permissionsGroup) {
                return null;
            }
            if ($classId) {
                if (!$permissionsGroup->hasTarget($classId)) {
                    return null;
                }
            }
        }

        try {

            $existingStudent = self::where('account', $account)->first();
            if ($existingStudent) {
                return [
                    'success' => false,
                    'message' => '账号已存在'
                ];
            }

            if ($classId) {
                $class = ClassModel::find($classId);
                if (!$class) {
                    return [
                        'success' => false,
                        'message' => '班级不存在'
                    ];
                }
            }

            if ($permissionsGroupId) {
                $permissionGroup = PermissionsGroup::find($permissionsGroupId);
                if (!$permissionGroup) {
                    return [
                        'success' => false,
                        'message' => '权限组不存在'
                    ];
                }
            }

            $student = new self();
            $student->account = $account;
            $student->name = $name;
            $student->password = Student::hashPassword($password);

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

        if ($this->level < 10) {
            $permissionsGroup = PermissionsGroup::find($this->permissions_group_id);
            if (!$permissionsGroup) {
                return null;
            }
            if (!$permissionsGroup->hasTarget(self::getUserClass($account))) {
                return null;
            }
        }

        try {
            $student = self::where('account', $account)->first();

            if (!$student) {
                return [
                    'success' => false,
                    'message' => '账号不存在'
                ];
            }
            // 删除用户
            $student->delete();

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
    public function isClassInPermisssion($classId): bool
    {
        if ($this->level == 10) {
            return true;
        }

        $permissionsGroup = PermissionsGroup::find($this->permissions_group_id);

        if (!$permissionsGroup) {
            return false;
        }

        return $permissionsGroup->hasTarget($classId);
    }

    // 获取管理员的所属班级
    public function getStudentPermissionClass()
    {
        $allClass = [];
        if ($this->level == 10) {
            $allClass = ClassModel::getAllClasses();
        } else {
            $permissionsGroup = PermissionsGroup::find($this->permissions_group_id);
            if (!$permissionsGroup) {
                return [];
            }

            $classList = $permissionsGroup->getPermissionTarget();
            foreach ($classList as $classId) {
                array_push($allClass, ClassModel::query()->where('class_id', $classId)->first());
            }
        }
        return $allClass;
    }

    // 根据班级查询学生
    public function getStudentByClass($classId, $queryWord, $queryCount, $limit = 20)
    {
        if ($this->level < 10) {
            $permissionsGroup = PermissionsGroup::find($this->permissions_group_id);
            if (!$permissionsGroup) {
                return null;
            }
            if (!$permissionsGroup->hasTarget($classId)) {
                return null;
            }
        }

        return $this->query()->where('class_id', $classId)
            ->where('name', 'like', '%' . $queryWord . '%')
            ->offset($queryCount)
            ->limit($limit)
            ->get();
    }

    // 查询所有管理员
    public static function getAdminList($queryCount = 0, $queryWord = '', $limit = 20)
    {
        $query = self::query();

        if (!empty($queryWord)) {
            $query->where('name', 'like', '%' . $queryWord . '%');
        }

        return $query->where('level', 5)
            ->offset($queryCount)
            ->limit($limit)
            ->get();
    }

    // 获取管理员数量
    public static function getAdminCount(?string $queryWord = ''): int
    {
        $query = self::query();

        if (!empty($queryWord)) {
            $query->where('name', 'like', '%' . $queryWord . '%')
                ->where('level', 5);
        }

        return $query->where('level', 5)->count();
    }

    // 获取学生班级
    public static function getUserClass($account)
    {
        return self::where('account', $account)->value('class_id');
    }

    // 获取学生数量
    public static function getStudentCount(): int
    {
        return self::count();
    }

    // 获取学生名字
    public static function getName($account)
    {
        return self::query()->where('account', $account)->first()->name;
    }

    // 根据班级获取学生数量
    public function getCountByClass(string $classId, ?string $queryWord = '')
    {
        if ($this->level < 10) {
            $permissionsGroup = PermissionsGroup::find($this->permissions_group_id);
            if (!$permissionsGroup) {
                return null;
            }
            if (!$permissionsGroup->hasTarget($classId)) {
                return null;
            }
        }

        $query = self::query()->where('class_id', $classId);

        if (!empty($queryWord)) {
            $query->where('name', 'like', '%' . $queryWord . '%');
        }

        return $query->count();
    }

    // 修改密码
    public static function editAdminPassword(string $account, string $password)
    {
        $admin = self::where('account', $account)
            ->where('level', 5)
            ->first();

        if (!$admin) {
            return [
                'success' => false,
                'message' => '管理员账号不存在'
            ];
        }
        $admin->password = Student::hashPassword($password);
        $admin->save();
    }

    // 管理员修改学生密码
    public function editPassword(string $account, string $password)
    {

        if ($this->level < 10 && $account != $this->account) {
            $permissionsGroup = PermissionsGroup::find($this->permissions_group_id);
            if (!$permissionsGroup) {
                return null;
            }
            if (!$permissionsGroup->hasTarget(self::getUserClass($account))) {
                return null;
            }
        }

        $user = self::where('account', $account)
            ->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => '账号不存在'
            ];
        }
        $user->password = Student::hashPassword($password);
        $user->save();
    }


    // 修改管理员
    public static function editAdmin(string $account, string $name, ?string $classId, string $permissionGroupId)
    {
        try {
            // 查找对应的管理员账号
            $admin = self::where('account', $account)
                ->where('level', 5)
                ->first();

            if (!$admin) {
                return [
                    'success' => false,
                    'message' => '管理员账号不存在'
                ];
            }

            // 验证权限组是否存在
            $permissionGroup = PermissionsGroup::find($permissionGroupId);
            if (!$permissionGroup) {
                return [
                    'success' => false,
                    'message' => '权限组不存在'
                ];
            }

            // 验证班级是否存在（如果class_id不为空）
            if ($classId) {
                $class = ClassModel::find($classId);
                if (!$class) {
                    return [
                        'success' => false,
                        'message' => '班级不存在'
                    ];
                }
            }

            // 更新管理员信息
            $admin->name = $name;
            $admin->class_id = $classId;
            $admin->permissions_group_id = $permissionGroupId;
            $admin->save();

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
        if ($this->level < 10) {
            $permissionsGroup = PermissionsGroup::find($this->permissions_group_id);
            if (!$permissionsGroup) {
                return null;
            }
            if (!$permissionsGroup->hasTarget(self::getUserClass($account))) {
                return null;
            }
            if (!$permissionsGroup->hasTarget($classId)) {
                return null;
            }
        }

        try {
            // 查找对应账号
            $user = self::where('account', $account)
                ->first();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => '账号不存在'
                ];
            }

            $class = ClassModel::find($classId);
            if (!$class) {
                return [
                    'success' => false,
                    'message' => '班级不存在'
                ];
            }

            $user->class_id = $classId;
            $user->name = $name;
            $user->save();

            return [
                'success' => true,
                'message' => '息修改成功',
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
            // 查找学生
            $student = self::where('account', $account)->first();

            if (!$student) {
                return [
                    'success' => false,
                    'message' => '用户不存在'
                ];
            }

            // 查找权限组
            $permissionGroup = PermissionsGroup::find($permissionGroupId)->first();

            if (!$permissionGroup) {
                return [
                    'success' => false,
                    'message' => '权限组不存在'
                ];
            }

            $student->permissions_group_id = $permissionGroupId;
            $student->save();

            return [
                'success' => true,
                'message' => '管理员设置成功',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '设置管理员错误: ' . $e->getMessage()
            ];
        }
    }


    // 获取待办
    public function getTodos()
    {
        return Activity::query()
            ->where('class_id', $this->class_id)
            ->where('status', 1)
            ->limit(50)
            ->get();
    }

    // 获取已开始
    public function getStarted()
    {
        return Activity::query()
            ->where('class_id', $this->class_id)
            ->whereIn('status', [1, 2])
            ->limit(50)
            ->get();
    }
}
