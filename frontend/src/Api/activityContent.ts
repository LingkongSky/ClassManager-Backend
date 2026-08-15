import { handleDownload, handleError, handleResponse, serializeData } from './utils.ts';
import { generalApi } from './common.ts';

export const activityApiService = {
  // 编辑活动时间提交
  editActivityTime: (activity_id: string, start_time: string, stop_time: string) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.post('activity/editTime', serializeData({
        activity_id,
        start_time,
        stop_time
      }))
        .then(response => handleResponse(response, resolve, reject, [0, 2]))
        .catch(error => handleError(error, reject));
    });
  },

  // 导出活动
  exportActivity: (activity_id: string, times: any) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.get('activity/export', {
        params:{
        activity_id,
        times
    }, responseType: 'blob'})
        .then(response => {
          if (response.data) {
            const hasContentDisposition = response.headers &&
              response.headers['content-disposition'];
            if (hasContentDisposition) {
              handleDownload(response, resolve, reject);
            } else {
              handleResponse(response, resolve, reject, [0, 1, 3, 4]);
            }
          } else {
            reject(new Error(response.data?.message || 'Export failed'));
          }
        })
        .catch(error => handleError(error, reject));
    });
  },

  // 获取活动列表
  listActivity: (class_id: string) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.get('activity/getList', {
        params: {
          class_id
        }
      })
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },

  // 删除活动
  deleteActivity: (activity_id: string) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.post('activity/delete', serializeData({
        activity_id
      }))
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },

  // 获取活动信息用于自动补充表单
  getContentActivity: (activity_id: string) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.get('activity/getInfo', {
        params: {
          activity_id
        }
      })
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },

  // 获取活动表格
  getActivityTable: (class_id: string, query_count: number, query_word: string) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.get('activity/getTable', {
        params: {
          class_id,
          query_count,
          query_word
        }
      })
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },
  // 获取排序作业表格
  getSortActivityTable: (activity_id: string, query_count: number, query_word: string, sort_mode: string, sort_field: any) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.get('activity/getHomeworkTable', {
        params: {
          activity_id,
          query_count,
          query_word,
          sort_mode,
          sort_field
        }
      })
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },

  // 获取活动表格
  getHomeworkTable: (activity_id: string, query_count: number, query_word: string) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.get('activity/getHomeworkTable', {
        params: {
          activity_id,
          query_count,
          query_word
        }
      })
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },

  // 获取活动表格数量
  getActivityCount: (class_id: string, query_word: string) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.get('activity/getCount', {
        params: {
          class_id,
          query_word
        }
      })
        .then(response => handleResponse(response, resolve, reject))
        .catch(error => handleError(error, reject));
    });
  },

  // 创建新活动
  createActivity: (formData: FormData) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.post('activity/create', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
        .then(response => handleResponse(response, resolve, reject, [0, 2]))
        .catch(error => handleError(error, reject));
    });
  },

  // 编辑活动
  editActivity: (formData: FormData) => {
    return new Promise<any>((resolve, reject) => {
      generalApi.post('activity/edit', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
        .then(response => handleResponse(response, resolve, reject, [0, 2]))
        .catch(error => handleError(error, reject));
    });
  },

};