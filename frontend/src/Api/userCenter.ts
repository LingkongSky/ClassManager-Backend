import { requestWithHandler, serializeData } from './utils.ts';
import { PasswordHash } from '../Module/PasswordCrypto.ts';
import { generalApi } from './common.ts';


export const userCenterApiService = {

    // 获取用户个人信息
    getUserInfo: () => {
        return requestWithHandler<any>(generalApi.get('user/me'));
    },

    // 用户修改密码
    editPassword: (account: string, passwd: string) => {
        return requestWithHandler<any>(
            generalApi.post('user/editPassword', serializeData({
                account,
                password: PasswordHash(passwd),
            }))
        );
    },
    // 退出登录
    exitLogin: () => {
        return requestWithHandler<any>(generalApi.post('user/logout'));
    },

};
