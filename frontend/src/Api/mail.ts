import axios from 'axios';
import { ClassManagerSite } from '../global.ts';
import { handleError, handleResponse, serializeData } from './utils.ts';

const userMailApi = axios.create({
  baseURL: `${ClassManagerSite}/Server/user/`
});

const adminMailApi = axios.create({
  baseURL: `${ClassManagerSite}/Server/admin/`
});

export const mailApiService = {
  // 获取未读邮件数量
  getUnreadMailCount: () => {
    return new Promise<number>((resolve, reject) => {
      userMailApi.post('mail.php', serializeData({ mode: "1" }))
        .then(response => {
          if (response.data && response.data.code == 0) {
            resolve(response.data.data);
          } else {
            reject(new Error(response.data?.message || '获取未读邮件失败'));
          }
        })
        .catch(error => handleError(error, reject));
    });
  },

  // 获取邮件列表（未读+已读）
  getUserMailList: () => {
    return new Promise<{ unread: any[], read: any[] }>((resolve, reject) => {
      userMailApi.post('mail.php', serializeData({ mode: "2" }))
        .then(response => {
          if (response.data && response.data.code == 0) {
            resolve(response.data.data);
          } else {
            reject(new Error(response.data?.message || '获取邮件列表失败'));
          }
        })
        .catch(error => handleError(error, reject));
    });
  },

  // 编辑公告时间
  editMailTime: (mail_id: string, start_time: string, stop_time: string) => {
    return new Promise((resolve, reject) => {
      adminMailApi.post('mailManager.php', serializeData({ mode: "2.5", mail_id, start_time, stop_time }))
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },


  // 获取邮件详情
  getUserMailContent: (mail_id: string, type: string) => {
    return new Promise<{ title: string, content: string, unread_count: number }>((resolve, reject) => {
      userMailApi.post('mail.php', serializeData({ mode: "3", mail_id, type }))
        .then(response => {
          if (response.data && response.data.code == 0) {
            resolve(response.data.data);
          } else {
            reject(new Error(response.data?.message || '获取邮件详情失败'));
          }
        })
        .catch(error => handleError(error, reject));
    });
  },


  // 删除邮件
  deleteMail: (deleteMailId: string) => {
    return new Promise((resolve, reject) => {
      adminMailApi.post('mailManager.php', serializeData({ mode: "3", mail_id: deleteMailId }))
        .then(response => {
          if (response.data && response.data.code == 0) {
            resolve(response.data.data);
          } else {
            reject(new Error(response.data?.message || '删除邮件失败'));
          }
        })
        .catch(error => handleError(error, reject));
    });
  },

  // 获取邮件编辑列表
  getMailEditList: () => {
    return new Promise<any>((resolve, reject) => {
      adminMailApi.post('mailManager.php', serializeData({ mode: "4" }))
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },

  // 获取信息用于编辑公告
  getMailEditInfo: (mail_id: string) => {
    return new Promise<any>((resolve, reject) => {
      adminMailApi.post('mailManager.php', serializeData({ mode: "5", mail_id }))
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },


  // 获取邮件总数（用于分页）
  getMaxMail: () => {
    return new Promise<number>((resolve, reject) => {
      adminMailApi.post('mailManager.php', serializeData({ mode: "6" }))
        .then(response => {
          if (response.data && response.data.code == 0) {
            resolve(response.data.data);
          } else {
            reject(new Error(response.data?.message || '获取邮件总数失败'));
          }
        })
        .catch(error => handleError(error, reject));
    });
  },
  
  // 获取邮件列表
  getMailList: (query_count: number, query_word: string) => {
    return new Promise<{ data: any }>((resolve, reject) => {
      adminMailApi.post('mailManager.php', serializeData({ mode: "7", query_count, query_word }))
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },

  // 获取邮件正文
  getMailContent: (mail_id: string) => {
    return new Promise<{ data: any }>((resolve, reject) => {
      adminMailApi.post('mailManager.php', serializeData({ mode: "8", mail_id }))
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },


  // 创建公告
  submitMail: (formData: FormData) => {
    return new Promise<any>((resolve, reject) => {
      adminMailApi.post('mailManager.php', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
        .then(response => handleResponse(response, resolve, reject, [0, 2]))
        .catch(error => handleError(error, reject));
    });
  },

  // 提交邮件编辑
  submitMailEdit: (formData: FormData) => {
    return new Promise<any>((resolve, reject) => {
      adminMailApi.post('mailManager.php', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
        .then(response => handleResponse(response, resolve, reject, [0, 2]))
        .catch(error => handleError(error, reject));
    });
  },
};