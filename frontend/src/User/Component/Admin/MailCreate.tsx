import { useEffect, useState } from 'react';
import {
    Button,
    Form,
    Select,
    Space,
    Input,
    DatePicker,
    Result,
    Checkbox,
    CheckboxChangeEvent,
} from 'antd';
const { RangePicker } = DatePicker;
const formItemLayout = {
    labelCol: { span: 6 },
    wrapperCol: { span: 14 },
};
import { deceteMobile } from '../../../global.ts';


import Markdown from 'react-markdown';
import remarkGfm from 'remark-gfm';


import dayjs from 'dayjs';

import { DatePicker as DatePickerMobile, Button as ButtonMobile } from 'antd-mobile'
import { memberApiService } from '../../../Api/memberList.ts';
import { mailApiService } from '../../../Api/mail.ts';
import { useCachedState } from '../../../Module/DataCache.ts';

interface SelectList {
    value: string,
    label: string
}

//var parsedData: SelectList[];


function MailCreate(props: any) {

    const [classList, setClassList] = useCachedState<null | SelectList[]>('options:classes', null);

    const [formStatus, setFormStatus] = useState(true);
    const [resultOK, setResultOK] = useState(false);
    const [resultError, setResultError] = useState(false);
    const [subtitle, setSubtitle] = useState("");

    const [markdownContet, setMarkdownContent] = useState("");

    const [isMobile, _setIsMobile] = useState(deceteMobile());
    const [mobileDatepicker1, setMobileDatepicker1] = useState(false);
    const [mobileDatepicker2, setMobileDatepicker2] = useState(false);
    const [mobileButtonText1, setMobileButtonText1] = useState("");
    const [mobileButtonText2, setMobileButtonText2] = useState("");
    const [mobileDateValue1, setMobileDateValue1] = useState("");
    const [mobileDateValue2, setMobileDateValue2] = useState("");

    const [selectAll, setSelectAll] = useState(true);
    const [selectAdmin, setSelectAdmin] = useState(false);
    const [selectClass, setSelectClass] = useState(false);


    const [defaultRange, _setDefaultRange] = useState<[dayjs.Dayjs, dayjs.Dayjs]>(() => {
        var now = dayjs(); // 获取当前时间
        var weekAgo = now.add(7, 'day'); // 计算一周前的时间
        return [now, weekAgo]; // 返回起始时间和结束时间的数组
    });


    // 切换选择目标
    function target_change_all(value: CheckboxChangeEvent) {
        //alert(JSON.stringify(value.target.checked));
        var isChecked = value.target.checked;
        setSelectAll(isChecked);
        if (isChecked) {
            setSelectAdmin(false);
            setSelectClass(false);
        }
    }

    function target_change_admin(value: CheckboxChangeEvent) {
        var isChecked = value.target.checked;
        setSelectAdmin(isChecked);
        if (isChecked) {
            setSelectAll(false);
            setSelectClass(false);
        }
    }


    async function target_change_class(value: CheckboxChangeEvent) {
        var isChecked = value.target.checked;
        setSelectClass(isChecked);
        if (isChecked) {
            setSelectAll(false);
            setSelectAdmin(false);
            await get_class_list();
        }
    }


    async function formSubmit(values: any) {

        var formData = new FormData();
        formData.append("mode", "1");
        formData.append("title", values.title);
        formData.append("content", values.content);


        if (selectAll) {
            formData.append("target_type", "all");
        } else if (selectAdmin) {
            formData.append("target_type", "admin");
        } else if (selectClass) {
            formData.append("target_type", "class");
            formData.append("target_class", values.class_list);
        }

        if (!isMobile) {
            formData.append("start_time", dayjs(values.time[0]).format('YYYY-MM-DD HH:mm:ss'));
            formData.append("stop_time", dayjs(values.time[1]).format('YYYY-MM-DD HH:mm:ss'));
        } else {
            formData.append("start_time", mobileDateValue1);
            formData.append("stop_time", mobileDateValue2);
        }
        try {
            const data = await mailApiService.submitMail(formData);
            if (data.code == 2) {
                setFormStatus(false);
                setSubtitle("请检查您的权限.");
                setResultError(true);
            } else if (data.code == 0) {
                setResultOK(true);
            }

        } catch (error) {
            setFormStatus(false);
            setSubtitle("请在重新提交之前确认提交内容是否有误:" + error);
            setResultError(true);

        }
    }

    async function get_class_list() {
        try {
            const respon = await memberApiService.getClassList();
            const apiData = respon.data;

            const transformedData = apiData.map((item: any) => ({
                value: item.class_id,
                label: item.class_name
            }));

            setClassList(transformedData);
        } catch (error: any) {
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

    useEffect(() => {
        setTimeout(async () => {
            // 根据用户权限进行区别渲染
            if (props.type == null) {
                props.type = 2;
            } else if (props.type == 1) {
                setSelectClass(true);
            }

            // 设置日期初始值
            if (isMobile) {
                var date1: string = dayjs().format("YYYY-MM-DD HH:mm");
                var date2: string = dayjs().add(1, "week").format("YYYY-MM-DD HH:mm");
                setMobileButtonText1(date1);
                setMobileButtonText2(date2);
                setMobileDateValue1(date1);
                setMobileDateValue2(date2);
            }
            await get_class_list()

        });
    }, []);


    return (
        <>
            {formStatus && (
                <Form
                    id="form"
                    name="MailCreate"
                    {...formItemLayout}
                    onFinish={formSubmit}
                    style={{ maxWidth: 600 }}
                    autoComplete="on"
                >

                    <Form.Item
                        label="发布对象"
                        rules={[{ required: true, message: '请选择发布对象' }]}
                    >
                        {(props.type == 2) && <><Checkbox checked={selectAll} onChange={(value) => target_change_all(value)} defaultChecked>所有人</Checkbox>
                            <Checkbox checked={selectAdmin} onChange={(value) => target_change_admin(value)}>仅管理员</Checkbox></>}
                        <Checkbox checked={selectClass} onChange={(value) => target_change_class(value)}>仅班级</Checkbox>
                    </Form.Item>

                    {selectClass && <Form.Item
                        name="class_list"
                        label="发布班级"
                        rules={[{ required: true, message: '请选择班级' }]}
                    >

                        <Select
                            mode="tags"
                            placeholder="请选择班级"
                            style={{ width: '100%' }}
                            showSearch
                            onChange={() => { }}
                            tokenSeparators={['|', ","]}
                            options={classList!}
                            filterOption={(input, option) => (option?.label ?? '').includes(input)}
                            filterSort={(optionA, optionB) =>
                                (optionA?.label ?? '').toLowerCase().localeCompare((optionB?.label ?? '').toLowerCase())
                            }
                        />
                    </Form.Item>}



                    <Form.Item
                        label="公告名"
                        name="title"
                        rules={[{ required: true, message: '请填入公告名' },
                        {
                            message: '请勿输入特殊字符',
                            pattern: /^((?!\+|\\|\/|:|\*|\?|<|>|\|| |'|%|@|#|&|\$|\^|&|\*).){1,20}$/
                        }]}
                    >
                        <Input maxLength={20} />
                    </Form.Item>


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
                            initialValue={defaultRange}
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
                                    precision='minute'
                                    value={new Date(dayjs(mobileDateValue1).valueOf())}
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
                title="Submission Failed"
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

export default MailCreate;
