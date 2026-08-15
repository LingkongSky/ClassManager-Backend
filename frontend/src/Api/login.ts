import axios from 'axios';
import { ClassManagerSite } from '../global.ts';
import { requestWithHandler, serializeData } from './utils.ts';
import { PasswordHash } from '../Module/PasswordCrypto.ts';

const loginApi = axios.create({
  baseURL: `${ClassManagerSite}/`,
});

export const loginApiService = {

  // 用户登录
  login: (account: string, passwd: string, remember: Boolean) => {
    return requestWithHandler<any>(
      loginApi.post('auth/login', serializeData({
        account,
        password: PasswordHash(passwd),
        remember: remember ? 1 : 0
      }))
    );
  },
  // 获取用户数量
  getUserCount: () => {
    return requestWithHandler<any>(loginApi.get('auth/getUserCount'));
  }
  
};
