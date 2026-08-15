import React, { useEffect, useState } from 'react';
import { Button, Descriptions, DescriptionsProps, Flex, Form, Input, Modal } from 'antd';
import { EyeInvisibleOutlined, EyeTwoTone } from '@ant-design/icons';
import { formItemLayout } from '../dashboard.tsx';
import messageService from '../../../Module/messageService.ts';
import { userCenterApiService } from '../../../Api/userCenter.ts';
import { deleteCookie } from '../../../Module/LocalStorage.ts';
import { clearCurrentUserDataCache } from '../../../Module/DataCache.ts';


const UserCenter: React.FC = () => {

    const [userInfo, setUserInfo] = useState<null | DescriptionsProps>(null);
    const [open1, setOpen1] = useState(false);
    const [account, setAccount] = useState("");
    const [confirmLoading1, setConfirmLoading1] = useState(false);
    const [form1] = Form.useForm();

    async function exitLogin() {
        try {
            await userCenterApiService.exitLogin();

            clearCurrentUserDataCache();
            deleteCookie("token");
            deleteCookie("token_type");
            
            window.location.href = window.location.origin + "/login.html";
        } catch (error) { }
    }

    async function editPassword(values: any) {
        setConfirmLoading1(true);
        try {
            await userCenterApiService.editPassword(account,values.password);
            messageService.info('密码修改成功!');
            setConfirmLoading1(false);
            setOpen1(false);
            setTimeout(function () {
                exitLogin();
            }, 1500);
        } catch (error) {
            messageService.error('密码修改失败: ' + error);
            setConfirmLoading1(false);
            setOpen1(false);
        }
    }


    useEffect(() => {
        const fetchUserInfo = async () => {
            try {
                const data = await userCenterApiService.getUserInfo();
                const info = data.data;
                const classNames: string[] = info.class_names ?? (info.class_name ? [info.class_name] : []);
                const primaryClassName: string | undefined = info.primary_class_name ?? classNames[0];
                const orderedClassNames = primaryClassName
                    ? [primaryClassName, ...classNames.filter((name: string) => name !== primaryClassName)]
                    : classNames;
                const classDisplay = (
                    <>
                        {orderedClassNames.map((name: string, index: number) => (
                            <span
                                key={`${info.account}-${name}-${index}`}
                                style={name === primaryClassName ? { textDecoration: 'underline' } : undefined}
                            >
                                {index > 0 ? ' / ' : ''}
                                {name}
                            </span>
                        ))}
                    </>
                );

                setAccount(info.account);

                setUserInfo({
                    title: '个人信息',
                    items: [
                        {
                            key: '1',
                            label: '姓名:',
                            children: info.name,
                            span: 3,
                        },
                        {
                            key: '2',
                            label: '学号',
                            children: info.account,
                            span: 3,
                        },
                        {
                            key: '3',
                            label: '班级',
                            span: 3,
                            children: classDisplay,
                        },
                        {
                            key: '4',
                            label: '权限',
                            span: 3,
                            children: info.level,
                        }
                    ]
                });
            } catch (_) { }
        };
        
        fetchUserInfo();
    }, []);


    return (
        <>
            <Flex title='学生信息' id="UserCenterFlex" vertical gap="small">

                {/*姓名 学号 班级 权限等级*/<>

                    <Descriptions title="个人信息" bordered items={userInfo?.items} />

                </>}

                <Button type="primary" block onClick={() => { setOpen1(true) }}>
                    修改密码
                </Button>
                <Button type="primary" block onClick={exitLogin}>
                    退出登录
                </Button>
            </Flex>

            <Modal
                title="修改密码"
                open={open1}
                onOk={() => { form1.submit() }}
                confirmLoading={confirmLoading1}
                onCancel={() => { setOpen1(false) }}
            >
                <Form {...formItemLayout} form={form1} onFinish={editPassword} style={{ maxWidth: 600 }} >
                    <Form.Item label="新密码" name="password" rules={[{ required: true, message: '请输入新密码' }]}>
                        <Input.Password maxLength={40} iconRender={(visible) => (visible ? <EyeTwoTone /> : <EyeInvisibleOutlined />)} />
                    </Form.Item>
                    <Form.Item label="重复输入密码" name="password_verify" rules={[
                        { required: true, message: '请输入相同的密码' },
                        ({ getFieldValue }) => ({
                            validator(_, value) {
                                if (!value || getFieldValue('password') === value) {
                                    return Promise.resolve()
                                }
                                return Promise.reject("两次密码输入不一致")
                            }
                        })
                    ]}
                        validateTrigger="onBlur"
                    >
                        <Input.Password maxLength={40} iconRender={(visible) => (visible ? <EyeTwoTone /> : <EyeInvisibleOutlined />)} />
                    </Form.Item>
                </Form>
            </Modal>
        </>
    )
};

export default UserCenter;
