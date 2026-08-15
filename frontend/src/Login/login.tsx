import React, { useEffect, useState } from 'react';
import { Button, Checkbox, Form, type FormProps, Input, message, Divider, CheckboxChangeEvent, Layout, Typography, Row, Col } from 'antd';
import { Card } from "antd";
import { FootText, WebsiteTitle } from '../global.ts';
import Filing from '../Module/Filing.tsx';
import './login.css';
import { loginApiService } from '../Api/login.ts';
import CountUp from 'react-countup';
import { setCookie } from '../Module/LocalStorage.ts';


type FieldType = {
    account?: string;
    password?: string;
    remember?: string;
};


const Login: React.FC = () => {

    const [messageApi, contextHolder] = message.useMessage();
    const [isRemember, setIsRemember] = useState(true);
    const [buttonLoading, setButtonLoading] = useState(false);

    const [userCount, setUserCount] = useState(0);

    const [errorCount, setErrorCount] = useState(0);

    const CountUpComponent = CountUp as any;

    const onFinish: FormProps<FieldType>["onFinish"] = (values) => {
        FormSumbit(values);
        console.log('Success:', values);
    };


    async function FormSumbit(values: any) {

        if (errorCount >= 6) {
            messageApi.error("密码错误次数已达上限，请一分钟后再试");
            return;
        }

        setButtonLoading(true);
        var account: string = values.account;
        var passwd: string = values.password;
        try {
            const data = await loginApiService.login(account, passwd, isRemember);
            const tokenLifetime = isRemember
                ? 4 * 7 * 24 * 60 * 60 * 1000
                : 2 * 60 * 60 * 1000;
            setCookie("token", data.data.token, tokenLifetime);
            setCookie("token_type", data.data.token_type, tokenLifetime);
            window.location.href = "/";
        } catch (error) {
            messageApi.error("登录失败");
            const nextErrorCount = errorCount + 1;
            setErrorCount(nextErrorCount);
            setButtonLoading(false);
            if (nextErrorCount >= 6) {
                disableLogin();
            }
        }
    }

    function disableLogin() {
        messageApi.error("密码错误次数已达上限，请一分钟后再试");
        setTimeout(function () {
            setErrorCount(0);
        }, 60000);
    }

    useEffect(() => {
        const loadUserCount = async () => {
            try {
                const data = await loginApiService.getUserCount();
                if (!isNaN(Number(data.data)))
                    setUserCount(data.data);
            } catch (error) {
                console.log(error);
                setUserCount(2333);
            }
        };
        loadUserCount();
    }, []);

    return (
        <div id="LoginArea">
            {contextHolder}
            {
                <Card id="LoginCard" title={WebsiteTitle} size="default" hoverable>
                    <Form
                        name="basic"
                        labelCol={{ xs: { span: 24 }, sm: { span: 5 } }}
                        wrapperCol={{ xs: { span: 24 }, sm: { span: 16 } }}
                        onFinish={onFinish}
                        autoComplete="off"
                    >
                        <Form.Item<FieldType>
                            label="学号"
                            name="account"
                            rules={[{ required: true, message: '请输入学号!' },
                            {
                                message: '请勿输入特殊字符',
                                pattern: /^((?!\+|\.|\\|\/|:|\*|\?|<|>|\|| |'|%|@|#|&|\$|\^|&|\*).){1,20}$/
                            }
                            ]}
                        >
                            <Input maxLength={20} />
                        </Form.Item>

                        <Form.Item<FieldType>
                            label="密码"
                            name="password"
                            rules={[{ required: true, message: '请输入密码!' }]}
                        >
                            <Input.Password visibilityToggle />
                        </Form.Item>

                        <Form.Item<FieldType>
                            wrapperCol={{ xs: { span: 24 }, sm: { span: 16, offset: 5 } }}
                        >
                            <Row justify="space-between" align="middle">
                                <Col>
                                    <label>
                                        <Checkbox 
                                            defaultChecked 
                                            onChange={(value: CheckboxChangeEvent) => { setIsRemember(value.target.checked) }}
                                        >
                                            记住用户
                                        </Checkbox>
                                    </label>
                                </Col>
                                <Col>
                                    <a href={'/Docs/'} style={{ whiteSpace: 'nowrap' }}>
                                        平台指南
                                    </a>
                                </Col>
                            </Row>
                        </Form.Item>

                        <Form.Item wrapperCol={{ xs: { span: 24 }, sm: { span: 16, offset: 5 } }}>
                            <Button block type="primary" htmlType="submit" loading={buttonLoading}>
                                登录
                            </Button>
                        </Form.Item>
                    </Form>
                </Card>
            }

            <Divider style={{ textAlign: 'center' }}>
                <Layout.Footer style={{ textAlign: 'center', backgroundColor: 'transparent' }}>
                    用户人数: <CountUpComponent end={userCount} />
                </Layout.Footer>
                <Typography.Paragraph>{FootText}</Typography.Paragraph>
                {/*<br />如果出现无法登录的情况，请尝试更换浏览器<br />*/}
                <br />用户交流群:<a href='https://qm.qq.com/q/ctAiS9gwsE'>960483252</a><br />
                <Filing />
            </Divider>
        </div>
    )
};

export default Login;
