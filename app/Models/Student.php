<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'student';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'name',
        'password',
        'account',
        'date',
        'level',
        'permissions_group_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'level' => 'integer',
    ];

    protected $permissionsGroupCache = null;
    protected static $userClassCache = [];

    protected static function booted(): void
    {
        // 无外键约束时，应用层兜底清理班级映射，避免孤儿数据
        static::deleting(function (self $student) {
            DB::table('student_class_map')->where('student_id', $student->id)->delete();
        });
    }

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
            $targetClassIds = self::getUserClasses($account);
            foreach ($targetClassIds as $targetClassId) {
                if ($permissionsGroup->hasTarget($targetClassId)) {
                    return true;
                }
            }
            return false;
        }

        return $classId && $permissionsGroup->hasTarget($classId);
    }

    public function createTokenWithExpiry(string $name, bool $remember = false, array $abilities = ['*'])
    {
        $expiresAt = $remember
            ? now()->addWeeks(4)
            : now()->addHours(2);

        return $this->createToken($name, $abilities, $expiresAt);
    }

    public static function hashPassword(string $password)
    {
        return hash_hmac('sha256', $password, config('app.password_sign'));
    }

    /**
     * 更新学生班级映射。
     */
    protected static function syncStudentClasses(int $studentId, array $classIds, ?string $primaryClassId = null): void
    {
        $classIds = array_values(array_unique(array_filter($classIds, fn($item) => !empty($item))));

        if (empty($classIds)) {
            DB::table('student_class_map')->where('student_id', $studentId)->delete();
            return;
        }

        if ($primaryClassId === null || !in_array($primaryClassId, $classIds, true)) {
            $primaryClassId = $classIds[0];
        }

        DB::transaction(function () use ($studentId, $classIds, $primaryClassId) {
            DB::table('student_class_map')->where('student_id', $studentId)->delete();

            $rows = [];
            $now = now();
            foreach ($classIds as $classId) {
                $rows[] = [
                    'student_id' => $studentId,
                    'class_id' => $classId,
                    'is_primary' => $classId === $primaryClassId ? 1 : 0,
                    'created_at' => $now,
                ];
            }

            DB::table('student_class_map')->insert($rows);
        });
    }

    protected static function getStudentIdByAccount(string $account): ?int
    {
        return self::where('account', $account)->value('id');
    }

    public static function getUserClasses(string $account): array
    {
        $studentId = self::getStudentIdByAccount($account);
        if (!$studentId) {
            return [];
        }

        return DB::table('student_class_map')
            ->where('student_id', $studentId)
            ->orderByDesc('is_primary')
            ->pluck('class_id')
            ->toArray();
    }

    public static function hasClass(string $account, string $classId): bool
    {
        $studentId = self::getStudentIdByAccount($account);
        if (!$studentId) {
            return false;
        }

        return DB::table('student_class_map')
            ->where('student_id', $studentId)
            ->where('class_id', $classId)
            ->exists();
    }

    /**
     * 仅主班级在权限组内时，才允许管理目标学生信息。
     */
    public function canManageStudentByPrimaryClass(string $targetAccount): bool
    {
        if ($this->level >= 10) {
            return true;
        }

        $permissionsGroup = $this->getPermissionsGroup();
        if (!$permissionsGroup) {
            return false;
        }

        $targetPrimaryClassId = self::getUserClass($targetAccount);
        if (!$targetPrimaryClassId) {
            return false;
        }

        return $permissionsGroup->hasTarget($targetPrimaryClassId);
    }

    public function createStudent(
        string $account,
        string $name,
        string $password,
        array $classIds,
        ?int $level = 0,
        ?string $permissionsGroupId = null,
        ?string $primaryClassId = null,
    ) {
        if ($this->level < 10) {
            $permissionsGroup = $this->getPermissionsGroup();
            if (!$permissionsGroup) {
                return null;
            }
            foreach ($classIds as $classId) {
                if (!$permissionsGroup->hasTarget($classId)) {
                    return null;
                }
            }
        }

        try {
            if (!empty($classIds)) {
                $classCount = ClassModel::whereIn('class_id', $classIds)->count();
                if ($classCount !== count(array_unique($classIds))) {
                    return [
                        'success' => false,
                        'message' => '班级不存在',
                    ];
                }
            }

            if ($primaryClassId !== null && !in_array($primaryClassId, $classIds, true)) {
                return [
                    'success' => false,
                    'message' => '主班级必须包含在班级列表中',
                ];
            }

            if ($permissionsGroupId) {
                if (!PermissionsGroup::where('group_id', $permissionsGroupId)->exists()) {
                    return [
                        'success' => false,
                        'message' => '权限组不存在',
                    ];
                }
            }

            if (self::where('account', $account)->exists()) {
                return [
                    'success' => false,
                    'message' => '账号已存在',
                ];
            }

            DB::transaction(function () use ($account, $name, $password, $classIds, $level, $permissionsGroupId, $primaryClassId) {
                $student = new self();
                $student->account = $account;
                $student->name = $name;
                $student->password = self::hashPassword($password);

                if ($level !== null) {
                    $student->level = $level;
                }

                if ($permissionsGroupId !== null) {
                    $student->permissions_group_id = $permissionsGroupId;
                }

                $student->save();
                self::syncStudentClasses($student->id, $classIds, $primaryClassId);
            });
            ClassModel::updateClassStatus();

            return [
                'success' => true,
                'message' => '账号创建成功',
                'data' => null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '创建账号失败: ' . $e->getMessage(),
            ];
        }
    }

    public function deleteByAccount(string $account)
    {
        if (!$this->canManageStudentByPrimaryClass($account)) {
            return null;
        }

        try {
            $student = self::where('account', $account)->first();

            if (!$student) {
                return [
                    'success' => false,
                    'message' => '账号不存在',
                ];
            }

            $student->delete();
            ClassModel::updateClassStatus();
            unset(self::$userClassCache[$account]);

            return [
                'success' => true,
                'message' => '账号删除成功',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '删除账号失败: ' . $e->getMessage(),
            ];
        }
    }

    public function isClassInPermission($classId): bool
    {
        return $this->checkClassPermission($classId);
    }

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

    public function getStudentByClass($classId, $queryWord, $queryCount, $limit = 20)
    {
        if (!$this->checkClassPermission($classId)) {
            return null;
        }

        $query = self::query()
            ->join('student_class_map as scm', 'scm.student_id', '=', 'student.id')
            ->where('scm.class_id', $classId)
            ->select('student.*', 'scm.class_id');

        if (!empty($queryWord)) {
            $query->where('student.name', 'like', $queryWord . '%');
        }

        return $query->offset($queryCount)
            ->limit($limit)
            ->get();
    }

    public static function getAdminList($queryCount = 0, $queryWord = '', $limit = 20)
    {
        $query = self::query()
            ->where('student.level', 5)
            ->leftJoin('student_class_map as scm', function ($join) {
                $join->on('scm.student_id', '=', 'student.id')->where('scm.is_primary', 1);
            })
            ->select('student.*', 'scm.class_id');

        if (!empty($queryWord)) {
            $query->where('student.name', 'like', $queryWord . '%');
        }

        return $query->offset($queryCount)
            ->limit($limit)
            ->get();
    }

    public static function getAdminCount(?string $queryWord = ''): int
    {
        $query = self::where('level', 5);

        if (!empty($queryWord)) {
            $query->where('name', 'like', $queryWord . '%');
        }

        return $query->count();
    }

    public static function getUserClass($account)
    {
        if (!isset(self::$userClassCache[$account])) {
            $studentId = self::getStudentIdByAccount($account);
            if (!$studentId) {
                return null;
            }

            self::$userClassCache[$account] = DB::table('student_class_map')
                ->where('student_id', $studentId)
                ->orderByDesc('is_primary')
                ->value('class_id');
        }

        return self::$userClassCache[$account];
    }

    public static function getStudentCount(): int
    {
        return self::count();
    }

    public static function getName($account)
    {
        return self::where('account', $account)->value('name');
    }

    public function getCountByClass(string $classId, ?string $queryWord = '')
    {
        if (!$this->checkClassPermission($classId)) {
            return null;
        }

        $query = self::query()
            ->join('student_class_map as scm', 'scm.student_id', '=', 'student.id')
            ->where('scm.class_id', $classId);

        if (!empty($queryWord)) {
            $query->where('student.name', 'like', $queryWord . '%');
        }

        return $query->count();
    }

    public static function editAdminPassword(string $account, string $password)
    {
        $student = self::where('account', $account)
            ->where('level', 5)
            ->first();
        if (!$student) {
            return [
                'success' => false,
                'message' => '管理员账号不存在',
            ];
        }
        $student->update(['password' => self::hashPassword($password)]);

        return [
            'success' => true,
            'message' => '密码修改成功',
        ];
    }

    public function editPassword(string $account, string $password)
    {
        if (!$this->canManageStudentByPrimaryClass($account) && $this->account != $account) {
            return null;
        }

        $student = self::where('account', $account)->first();
        if (!$student) {
            return [
                'success' => false,
                'message' => '账号不存在',
            ];
        }
        $student->update(['password' => self::hashPassword($password)]);

        return [
            'success' => true,
            'message' => '密码修改成功',
        ];
    }

    public static function editAdmin(string $account, string $name, ?array $classIds, string $permissionGroupId, ?string $primaryClassId = null)
    {
        try {
            if (!PermissionsGroup::where('group_id', $permissionGroupId)->exists()) {
                return [
                    'success' => false,
                    'message' => '权限组不存在',
                ];
            }

            if (!empty($classIds)) {
                $classCount = ClassModel::whereIn('class_id', $classIds)->count();
                if ($classCount !== count(array_unique($classIds))) {
                    return [
                        'success' => false,
                        'message' => '班级不存在',
                    ];
                }
            }

            $student = self::where('account', $account)
                ->where('level', 5)
                ->first();

            if (!$student) {
                return [
                    'success' => false,
                    'message' => '管理员账号不存在',
                ];
            }

            $student->update([
                'name' => $name,
                'permissions_group_id' => $permissionGroupId,
            ]);

            self::syncStudentClasses($student->id, $classIds ?? [], $primaryClassId);
            ClassModel::updateClassStatus();

            return [
                'success' => true,
                'message' => '管理员信息修改成功',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '修改管理员信息失败: ' . $e->getMessage(),
            ];
        }
    }

    public function edit(string $account, string $name, array $classIds, ?string $primaryClassId = null)
    {
        foreach ($classIds as $classId) {
            if (!$this->checkClassPermission($classId)) {
                return null;
            }
        }

        if (!$this->canManageStudentByPrimaryClass($account)) {
            return null;
        }

        try {
            $classCount = ClassModel::whereIn('class_id', $classIds)->count();
            if ($classCount !== count(array_unique($classIds))) {
                return [
                    'success' => false,
                    'message' => '班级不存在',
                ];
            }

            $student = self::where('account', $account)->first();
            if (!$student) {
                return [
                    'success' => false,
                    'message' => '账号不存在',
                ];
            }

            $student->update([
                'name' => $name,
            ]);

            self::syncStudentClasses($student->id, $classIds, $primaryClassId);
            ClassModel::updateClassStatus();
            unset(self::$userClassCache[$account]);

            return [
                'success' => true,
                'message' => '信息修改成功',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '修改信息失败: ' . $e->getMessage(),
            ];
        }
    }

    public static function setPermissionGroup($account, $permissionGroupId)
    {
        try {
            if (!PermissionsGroup::where('group_id', $permissionGroupId)->exists()) {
                return [
                    'success' => false,
                    'message' => '权限组不存在',
                ];
            }

            $student = self::where('account', $account)->first();
            if (!$student) {
                return [
                    'success' => false,
                    'message' => '用户不存在',
                ];
            }
            $student->update([
                'permissions_group_id' => $permissionGroupId,
                'level' => 5,
            ]);

            return [
                'success' => true,
                'message' => '权限组设置成功',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '设置权限组错误: ' . $e->getMessage(),
            ];
        }
    }

    public function getTodos()
    {
        $classIds = self::getUserClasses($this->account);

        return Activity::select(['activity_id', 'title', 'status', 'start_time', 'stop_time', 'name', 'type'])
            ->whereIn('class_id', $classIds)
            ->where('status', 1)
            ->orderBy('start_time', 'asc')
            ->limit(50)
            ->get();
    }

    public function getStarted()
    {
        $classIds = self::getUserClasses($this->account);

        return Activity::select(['activity_id', 'title', 'status', 'start_time', 'stop_time', 'name', 'type'])
            ->whereIn('class_id', $classIds)
            ->whereIn('status', [1, 2])
            ->orderBy('start_time', 'asc')
            ->limit(50)
            ->get();
    }
}
