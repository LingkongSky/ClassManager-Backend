<?php
// app/Models/UserMailbox.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMailbox extends Model
{
    use HasFactory;

    protected $table = 'user_mailbox';
    public $timestamps = false;

    protected $fillable = [
        'account',
        'unread_mails',
        'read_mails',
        'all_mails'
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    // 访问器：获取未读邮件数组
    public function getUnreadMailsArrayAttribute()
    {
        return $this->unread_mails ? explode(',', $this->unread_mails) : [];
    }

    // 访问器：获取已读邮件数组
    public function getReadMailsArrayAttribute()
    {
        return $this->read_mails ? explode(',', $this->read_mails) : [];
    }

    // 访问器：获取所有邮件数组
    public function getAllMailsArrayAttribute()
    {
        return $this->all_mails ? explode(',', $this->all_mails) : [];
    }

    // 访问器：未读邮件数量
    public function getUnreadCountAttribute()
    {
        return count($this->getUnreadMailsArrayAttribute());
    }

    // 添加邮件
    public function addMail($mailId, $markAsUnread = true)
    {
        $allMails = $this->getAllMailsArrayAttribute();
        if (!in_array($mailId, $allMails)) {
            $allMails[] = $mailId;
            $this->all_mails = implode(',', $allMails);

            if ($markAsUnread) {
                $unreadMails = $this->getUnreadMailsArrayAttribute();
                $unreadMails[] = $mailId;
                $this->unread_mails = implode(',', $unreadMails);
            } else {
                $readMails = $this->getReadMailsArrayAttribute();
                $readMails[] = $mailId;
                $this->read_mails = implode(',', $readMails);
            }

            $this->save();
        }
    }

    // 标记为已读
    public function markAsRead($mailId)
    {
        $unreadMails = $this->getUnreadMailsArrayAttribute();
        $readMails = $this->getReadMailsArrayAttribute();

        $key = array_search($mailId, $unreadMails);
        if ($key !== false) {
            unset($unreadMails[$key]);
            $readMails[] = $mailId;

            $this->unread_mails = implode(',', array_values($unreadMails));
            $this->read_mails = implode(',', $readMails);
            $this->save();
        }
    }

    // 关系：邮箱属于一个学生
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'account', 'account');
    }
}
