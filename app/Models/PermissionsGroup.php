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

    // 设置目标
    public function setTargetAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['target'] = implode('|', $value);
        } else {
            $this->attributes['target'] = $value;
        }
    }

    // 检查是否包含某个目标
    public function hasTarget($target)
    {
        $targets = $this->getPermissionTarget();
        return in_array($target, $targets);
    }

}