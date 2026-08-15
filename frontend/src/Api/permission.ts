import { requestWithHandler, serializeData } from './utils.ts';
import { generalApi } from './common.ts';


export const permissionEditApi = {
  // 获取管理员列表
  getAdminList: (query_count: number, query_word: string) => {
    return requestWithHandler<any>(
      generalApi.get('permissionEdit/getAdminList', {
        params: {
          query_count,
          query_word
        }
      })
    );
  },

  // 获取管理员数量
  getAdminCount: (query_word: string) => {
    return requestWithHandler<any>(
      generalApi.get('permissionEdit/getCount', {
        params: {
          query_word
        }
      })
    );
  },

  // 获取权限组列表
  getPermissionsGroupList: () => {
    return requestWithHandler<any>(generalApi.get('permissionGroup/getList'));
  },

  // 编辑管理员信息
  editAdmin: (account: string, name: string, class_ids: string[], primary_class_id: string, permissions_group_id: string) => {
    return requestWithHandler<any>(
      generalApi.post('permissionEdit/editAdmin', serializeData({
        account,
        name,
        class_ids: JSON.stringify(class_ids),
        primary_class_id,
        permissions_group_id
      }))
    );
  },

  // 删除管理员
  deleteAdmin: (admin_list: string) => {
    return requestWithHandler<any>(generalApi.post('permissionEdit/deleteAdmin', serializeData({admin_list })));
  },

  // 设置管理员
  setAdmin: (admin_list: string) => {
    return requestWithHandler<any>(generalApi.post('permissionEdit/setAdmin', serializeData({ admin_list })));
  },

  // 新增管理员用户
  addAdminUser: (data: string) => {
    return requestWithHandler<any>(generalApi.post('permissionEdit/addAdmin', serializeData({ data })));
  },

  // 编辑管理员密码
  editAdminPassword: (account: string, password: string) => {
    return requestWithHandler<any>(generalApi.post('permissionEdit/editAdminPassword', serializeData({ account, password })));
  }
};

// 权限组相关接口
export const permissionGroupApi = {

  // 获取权限组
  getPermissionsGroupList: (query_count: number, query_word: string) => {
    return requestWithHandler<any>(
      generalApi.get('permissionGroup/getGroupList', {
        params: {
          query_count,
          query_word
        }
      })
    );
  },

  // 获取权限组数量
  getPermissionsGroupCount: (query_word: string) => {
    return requestWithHandler<any>(
      generalApi.get('permissionGroup/getCount', {
        params: {
          query_word
        }
      })
    );
  },


  // 增加权限组
  addPermissionsGroup: (permissions_group_list: string) => {
    return requestWithHandler<any>(
      generalApi.post('permissionGroup/addGroup', serializeData({
        permissions_group_list
      }))
    );
  },

  // 编辑权限组
  editPermissionsGroup: (permissions_group_id: string, permissions_group_name: string, class_list: string) => {
    return requestWithHandler<any>(
      generalApi.post('permissionGroup/editGroup', serializeData({
        permissions_group_id,
        permissions_group_name,
        class_list
      }))
    );
  },

  // 删除权限组
  deletePermissionsGroup: (permissions_group_list: string) => {
    return requestWithHandler<any>(
      generalApi.post('permissionGroup/deleteGroup', serializeData({
        permissions_group_list
      }))
    );
  },

  // xlxs提交管理员列表
  addPermissionsGroupList: (permissions_group_list: string) => {
    return requestWithHandler<any>(
      generalApi.post('permissionGroup/addGroup', serializeData({
        permissions_group_list
      }))
    );
  }
};
