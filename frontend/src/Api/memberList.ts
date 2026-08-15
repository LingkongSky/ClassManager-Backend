import { requestWithHandler, serializeData } from './utils.ts';
import { generalApi } from './common.ts';


export const memberApiService = {
    // 获取学生列表
    getStudentList: (class_id: string, query_count: number, query_word: string) => {
        return requestWithHandler<any>(
            generalApi.get('user/getList', {
                params: {
                    class_id,
                    query_count,
                    query_word
                }
            })
            , [0, 1]
        );
    },

    // 获取班级列表
    getClassList: () => {
        return requestWithHandler<any>(generalApi.get('class/getList'));
    },

    getClassIdByName: (class_name: string) => {
        return requestWithHandler<any>(
            generalApi.post('class/getId', serializeData({
                class_name
            }))
        );
    },

    // 批量添加学生（Excel导入）
    batchAddStudents: (data: any[]) => {
        return requestWithHandler<any>(
            generalApi.post('user/add', serializeData({
                data: JSON.stringify(data)
            }))
            , [0, 2]
        );
    },

    // 添加单个学生
    addStudent: (data: Array<{
        name: string;
        account: string;
        password: string;
        class_ids: string[];
        primary_class_id: string;
    }>) => {
        return requestWithHandler<any>(
            generalApi.post('user/add', serializeData({
                data: JSON.stringify(data)
            }))
        );
    },

    // 删除学生（单个或批量）
    deleteStudent: (student_list: string[]) => {
        return requestWithHandler<any>(
            generalApi.post('user/delete', serializeData({
                student_list: JSON.stringify(student_list)
            }))
        );
    },

    // 编辑学生信息
    editStudent: (account: string, name: string, class_ids: string[], primary_class_id: string) => {
        return requestWithHandler<any>(
            generalApi.post('user/edit', serializeData({
                account,
                name,
                class_ids: JSON.stringify(class_ids),
                primary_class_id
            }))
        );
    },

    // 修改密码
    changePassword: (account: string, password: string) => {
        return requestWithHandler<any>(
            generalApi.post('user/editPassword', serializeData({
                account,
                password
            }))
        );
    },

    // 获取用户数量
    getStudentCount: (class_id: string, query_word: string) => {
        return requestWithHandler<any>(
            generalApi.get('user/getCount', {
                params: {
                    class_id,
                    query_word
                }
            })
        );
    }
};
