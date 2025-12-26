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
    public function passwordHash(string $password)
    {
        return hash_hmac("sha256", $password, config('app.password_sign'));
    }


    // 判断传入的class_id是否在用户的权限组里
    public function isClassInPermisssion($classId)
    {
        $permissionsGroup = PermissionsGroup::find($this->permissions_group_id);

        if (!$permissionsGroup) {
            return false;
        }

        return $permissionsGroup->hasTarget($classId);
    }

    public function getStudentPermissionClass(){

           $permissionsGroup = PermissionsGroup::find($this->permissions_group_id);

        if (!$permissionsGroup) {
            return null;
        }

        return $permissionsGroup->getPermissionTarget();
    }

    // 获取学生数量
    public static function getStudentCount(): int
    {
        return self::count();
    }
}
