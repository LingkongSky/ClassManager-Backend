import React, { useEffect, useState } from 'react';
import {
    Button,
    Form,
    Space,
    Input,
    DatePicker,
    Result,
    message,
    TreeSelect,
    Checkbox,
    Select
} from 'antd';
const { RangePicker } = DatePicker;
const formItemLayout = {
    labelCol: { span: 6 },
    wrapperCol: { span: 14 },
};
import { deceteMobile } from '../../../global.ts';
import dayjs from 'dayjs';

import { DatePicker as DatePickerMobile, Button as ButtonMobile } from 'antd-mobile'
import Markdown from 'react-markdown';
import remarkGfm from 'remark-gfm';
import { Base64 } from 'js-base64';
import { mailApiService } from '../../../Api/mail.ts';

interface SelectList {
    value: string,
    label: string
}

//var parsedData: SelectList[];

interface MailType {
    title: string,
    content: string,
    target: string,
    start_time: string,
    stop_time: string
};

const TypeDict: { [key: string]: number } = {
    all: 0,
    admin: 1,
    class: 2
}

const MailEdit: React.FC = () => {

    const [mailList, setMailList] = useState<null | SelectList[]>(null);
    const [MailEditForm] = Form.useForm();
    const [markdownContet, setMarkdownContent] = useState("");
    const [targetType, setTargetType] = useState(3);

    const [formStatus, setFormStatus] = useState(true);
    const [resultOK, setResultOK] = useState(false);
    const [resultError, setResultError] = useState(false);
    const [subtitle, setSubtitle] = useState("");


    //const [defaultClassList, setDefaultClassList] = useState();



    const [mobileDatepicker1, setMobileDatepicker1] = useState(false);
    const [mobileDatepicker2, setMobileDatepicker2] = useState(false);
    const [mobileButtonText1, setMobileButtonText1] = useState("");
    const [mobileButtonText2, setMobileButtonText2] = useState("");
    const [mobileDateValue1, setMobileDateValue1] = useState("");
    const [mobileDateValue2, setMobileDateValue2] = useState("");

    const [isMobile, _setIsMobile] = useState(deceteMobile());



    // 获取活动信息并填写表单
    async function mailSelect(value: any) {
        try {
            const response = await mailApiService.getMailEditInfo(value);
            const info: MailType = response.data;

            // 对info.target和content进行解码，获取类型和值
            const target = JSON.parse(Base64.decode(info.target));
            const type: number = TypeDict[target.target];
            setTargetType(type);

            let target_class = "";
            if (type === 2) {
                target_class = target.class;
            }

            const content = Base64.decode(info.content);
            setMarkdownContent(content);

            if (!isMobile) {
                MailEditForm.setFieldsValue({
                    title: info.title,
                    content: content,
                    target_class: target_class,
                    time: [dayjs(info.start_time), dayjs(info.stop_time)]
                });
            } else {
                MailEditForm.setFieldsValue({
                    title: info.title,
                    content: content,
                    target_class: target_class,
                });
                setMobileButtonText1(dayjs(info.start_time).format("YYYY-MM-DD HH:mm"));
                setMobileButtonText2(dayjs(info.stop_time).format("YYYY-MM-DD HH:mm"));
                setMobileDateValue1(dayjs(info.start_time).format("YYYY-MM-DD HH:mm"));
                setMobileDateValue2(dayjs(info.stop_time).format("YYYY-MM-DD HH:mm"));
            }

        } catch (error) {
            console.error('mailSelect error:', error);
            message.error(error instanceof Error ? error.message : '获取邮件信息失败');
        }
    }

    // 移动端日历组件适配
    function dataPickerMobileSelect1(values: any) {
        setMobileButtonText1(dayjs(values).format("YYYY-MM-DD HH:mm"));
        setMobileDateValue1(dayjs(values).format("YYYY-MM-DD HH:mm"));
        setMobileDatepicker1(false);
    }

    // 移动端日历组件适配
    function dataPickerMobileSelect2(values: any) {
        setMobileButtonText2(dayjs(values).format("YYYY-MM-DD HH:mm"));
        setMobileDateValue2(dayjs(values).format("YYYY-MM-DD HH:mm"));
        setMobileDatepicker2(false);
    }
    // 获取活动列表
    async function get_mail_list() {
        try {
            const response = await mailApiService.getMailEditList();
            const parsedData = response.data;
            setMailList(parsedData);

            // 清空表单
            MailEditForm.setFieldsValue({
                title: "",
                content: ""
            });

        } catch (error) {
            console.error('get_mail_list error:', error);
            message.error(error instanceof Error ? error.message : '获取邮件列表失败');
        }
    }

    async function formSubmit(values: any) {
        try {
            const formData = new FormData();

            formData.append("mode", "2");
            formData.append("mail_id", values.mail_id);
            formData.append("content", values.content);

            if (!isMobile) {
                formData.append("start_time", dayjs(values.time[0]).format('YYYY-MM-DD HH:mm:ss'));
                formData.append("stop_time", dayjs(values.time[1]).format('YYYY-MM-DD HH:mm:ss'));
            } else {
                formData.append("start_time", mobileDateValue1);
                formData.append("stop_time", mobileDateValue2);
            }

            const response = await mailApiService.submitMailEdit(formData);

            if (response.code === 2) {
                setFormStatus(false);
                setSubtitle("请检查您的权限.");
                setResultError(true);
            } else {
                setFormStatus(false);
                setSubtitle(response.data);
                setResultOK(true);
            }

        } catch (error) {
            console.error('formSubmit error:', error);
            setFormStatus(false);
            setSubtitle(`请在重新提交之前确认提交内容是否有误: ${error instanceof Error ? error.message : '未知错误'}`);
            setResultError(true);
        }
    }


    // 请求所有活动
    useEffect(() => {
        setTimeout(async () => {
            await get_mail_list();
        });

        if (isMobile) {
            var date1: string = dayjs().format("YYYY-MM-DD HH:mm");
            var date2: string = dayjs().add(1, "week").format("YYYY-MM-DD HH:mm");
            setMobileButtonText1(date1);
            setMobileButtonText2(date2);
            setMobileDateValue1(date1);
            setMobileDateValue2(date2);
        }
    }, []);


    return (
        <>
            {formStatus && (
                <Form
                    id="MailEdit"
                    name="MailEdit"
                    form={MailEditForm}
                    {...formItemLayout}
                    onFinish={formSubmit}
                    style={{ maxWidth: 600 }}
                    autoComplete="on"
                >

                    <Form.Item
                        label="选择活动"
                        name="mail_id"
                        rules={[{ required: true, message: '请选择活动' }]}
                    >
                        <Form.Item
                            label="选择活动"
                            name="mail_id"
                            rules={[{ required: true, message: '请选择活动' }]}
                        >
                            <TreeSelect
                                placeholder="请选择活动"
                                treeData={mailList!}
                                showSearch={{
                                    filterTreeNode: true,
                                    treeNodeFilterProp: 'title'
                                }}
                                style={{ width: '100%' }}
                                styles={{
                                    popup: {
                                        root: {
                                            maxHeight: 400,
                                            overflow: 'auto'
                                        }
                                    }
                                }}
                                allowClear
                                onSelect={(value) => { mailSelect(value) }}
                            />
                        </Form.Item>
                    </Form.Item>

                    <Form.Item
                        label="活动名"
                        name="title"
                    >
                        <Input disabled />
                    </Form.Item>

                    <Form.Item
                        label="发布对象"
                    >
                        {(targetType == 0) && <Checkbox disabled checked>所有人</Checkbox>}
                        {(targetType == 1) && <Checkbox disabled checked>仅管理员</Checkbox>}
                        {(targetType == 2) && <Checkbox disabled checked>仅班级</Checkbox>}
                    </Form.Item>
                    {(targetType == 2) &&
                        <Form.Item
                            label="发布班级"
                        >
                            <Select
                                mode="tags"
                                disabled
                                style={{ width: '100%' }}
                                tokenSeparators={['|', ","]}
                            />
                        </Form.Item>
                    }

                    <Form.Item
                        name="content"
                        label="正文"
                        rules={[{ required: true, message: '请填入公告内容' }]}
                    >
                        <Input.TextArea maxLength={675} autoSize allowClear onChange={(value) => setMarkdownContent(value.target.value)} />
                    </Form.Item>


                    <Form.Item
                        name="view"
                        label="预览"
                    >
                        <Markdown remarkPlugins={[remarkGfm]}>{markdownContet}</Markdown>
                    </Form.Item>



                    {!isMobile &&
                        <Form.Item
                            name="time"
                            label="提交时间"
                            rules={[{ required: true }]}
                        >
                            <RangePicker
                                showNow
                                allowClear
                                showTime
                                format="YYYY-MM-DD HH:mm"
                                inputReadOnly
                                size='small'
                            />
                        </Form.Item>
                    }



                    {isMobile &&
                        <>
                            <Form.Item
                                name="start_time"
                                label="开始时间"
                            >
                                <ButtonMobile onClick={() => { setMobileDatepicker1(true) }}>
                                    {mobileButtonText1}
                                </ButtonMobile>

                                <DatePickerMobile
                                    visible={mobileDatepicker1}
                                    value={new Date(dayjs(mobileDateValue1).valueOf())}
                                    precision='minute'
                                    onCancel={() => { setMobileDatepicker1(false) }}
                                    onConfirm={(values: any) => { dataPickerMobileSelect1(values) }}
                                />
                            </Form.Item>


                            <Form.Item
                                name="stop_time"
                                label="结束时间"
                            >

                                <ButtonMobile onClick={() => { setMobileDatepicker2(true) }}>
                                    {mobileButtonText2}
                                </ButtonMobile>

                                <DatePickerMobile
                                    visible={mobileDatepicker2}
                                    value={new Date(dayjs(mobileDateValue2).valueOf())}
                                    precision='minute'
                                    onCancel={() => { setMobileDatepicker2(false) }}
                                    onConfirm={(values: any) => { dataPickerMobileSelect2(values) }}
                                />
                            </Form.Item>
                        </>
                    }



                    <Form.Item wrapperCol={{ span: 12, offset: 6 }}>
                        <Space>
                            <Button type="primary" htmlType="submit">
                                提交
                            </Button>
                            <Button htmlType="reset">重置</Button>
                        </Space>
                    </Form.Item>
                </Form>)}

            {resultOK && (<Result
                status="success"
                title="提交成功!"
                subTitle={subtitle}
                extra={[
                    <Button type="primary" key="console" onClick={() => { window.location.reload() }}>
                        返回首页
                    </Button>
                ]}
            />)}

            {resultError && (<Result
                status="error"
                title="提交失败"
                subTitle={subtitle}
                extra={[
                    <Button type="primary" key="console" onClick={() => { window.location.reload() }}>
                        返回首页
                    </Button>
                ]}
            >
            </Result>)}
        </>
    )
};

export default MailEdit;