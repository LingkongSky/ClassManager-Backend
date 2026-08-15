import { handleDownload, handleError, handleResponse, serializeData } from './utils.ts';
import { generalApi } from './common.ts';

export const todosApiService = {
  // 获取活动
  getActivity: () => {
    return new Promise<any>((resolve, reject) => {
      generalApi.get('todos/getTodos')
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },

  // 获取全部活动
  getActivityList: (query_count: number) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.get('todos/getStarted', {
        params: {
          query_count
        }
      })
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },

  // 打开作业
  openHomework: (activity_id: string) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.get('todos/open', {
        params: { activity_id }
      })
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },

  // 提交作业
  submitHomework: (formData: FormData) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.post('todos/submit', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
        .then(response => handleResponse(response, resolve, reject, [0, 1, 2, 4]))
        .catch(error => handleError(error, reject));
    });
  },
  // 管理员提交作业
  submitHomeworkAdmin: (formData: FormData) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.post('todos/submitAdmin', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
        .then(response => handleResponse(response, resolve, reject, [0, 1, 2, 4]))
        .catch(error => handleError(error, reject));
    });
  },
  // 获取作业
  downloadHomework: (activity_id: string) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.get('todos/download', {
        params: { activity_id }, responseType: 'blob'
      })
        .then(response => {
          if (response.data) {
            const hasContentDisposition = response.headers &&
              response.headers['content-disposition'];
            if (hasContentDisposition) {
              handleDownload(response, resolve, reject);
            } else {
              handleResponse(response, resolve, reject, [0, 1, 2]);
            }
          } else {
            reject(new Error(response.data?.message || 'Export failed'));
          }
        }).catch(error => handleError(error, reject));
    });
  },

  // 管理员下载作业
  downloadHomeworkAdmin: (activity_id: string, target_account: string) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.get('todos/downloadAdmin', { params: { activity_id, target_account }, responseType: 'blob' })
        .then(response => {
          if (response.data) {
            const hasContentDisposition = response.headers &&
              response.headers['content-disposition'];
            if (hasContentDisposition) {
              handleDownload(response, resolve, reject);
            } else {
              handleResponse(response, resolve, reject, [0, 1, 2, 3, 4]);
            }
          } else {
            reject(new Error(response.data?.message || 'Export failed'));
          }
        }).catch(error => handleError(error, reject));
    });
  },

  // 下载活动的发布文件
  downloadPublish: (activity_id: string, file_name: string) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.get('todos/downloadPublish', { params: { activity_id, file_name }, responseType: 'blob' })
        .then(response => {
          if (response.data) {
            const hasContentDisposition = response.headers &&
              response.headers['content-disposition'];
            if (hasContentDisposition) {
              handleDownload(response, resolve, reject);
            } else {
              handleResponse(response, resolve, reject, [0, 2]);
            }
          } else {
            reject(new Error(response.data?.message || 'Export failed'));
          }
        }).catch(error => handleError(error, reject));
    });
  },

  // 获取待办事项计数
  getTodosCount: () => {
    return new Promise<any>((resolve, reject) => {
      generalApi.get('todos/getTodosCount')
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },

  // 获取全部活动总数
  getActivityCount: () => {
    return new Promise<any>((resolve, reject) => {
      generalApi.get('todos/getStartedCount')
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },

  // 删除作业
  deleteHomework: (activity_id: string) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.post('todos/delete', serializeData({ activity_id }))
        .then(response => handleResponse(response, resolve, reject, [0, 1, 2, 3]))
        .catch(error => handleError(error, reject));
    });
  },
  // 管理员删除作业
  deleteHomeworkAdmin: (activity_id: string, target_account: string) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.post('todos/deleteAdmin', serializeData({ activity_id, target_account }))
        .then(response => handleResponse(response, resolve, reject, [0, 1, 2, 3]))
        .catch(error => handleError(error, reject));
    });
  }
};