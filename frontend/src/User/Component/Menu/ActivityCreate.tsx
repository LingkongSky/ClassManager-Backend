import { InboxOutlined } from '@ant-design/icons';
import React, { JSX, useEffect, useState } from 'react';
import {
    Button,
    Form,
    Select,
    Space,
    Upload,
    Input,
    DatePicker,
    Result,
} from 'antd';
const { RangePicker } = DatePicker;
const formItemLayout = {
    labelCol: { span: 6 },
    wrapperCol: { span: 14 },
};
const { Dragger } = Upload;
import type { UploadProps } from 'antd';
import { deceteMobile, FileMaxUplaodSize } from '../../../global.ts';
import type { SelectProps } from 'antd';
import dayjs from 'dayjs';

import { DatePicker as DatePickerMobile, Button as ButtonMobile } from 'antd-mobile'
import { normFile } from '../dashboard.tsx';
import messageService from '../../../Module/messageService.ts';
import { activityApiService } from '../../../Api/activityContent.ts';
import { memberApiService } from '../../../Api/memberList.ts';
import { Base64 } from 'js-base64';
import { useCachedState } from '../../../Module/DataCache.ts';

interface SelectList {
    value: string,
    label: string
}

//var parsedData: SelectList[];

const initialRenameFormat = "[活动名] [班级] [姓名] [学号]";

const props: UploadProps = {
    //action: ClassManagerSite + '/Server/admin/memberList.php',
    method: 'POST',
    multiple: true
};

/*
初始值
返回值格式化
自动选择一周
*/

const ActivityCreate: React.FC = () => {
    //const [open, setOpen] = useState(false);
    const [classList, setClassList] = useCachedState<null | SelectList[]>('options:classes', null);
    // const [courseList, setCourseList] = useState<null | SelectList[]>(null);
    const [formStatus, setFormStatus] = useState(true);
    const [resultOK, setResultOK] = useState(false);
    const [resultError, setResultError] = useState(false);
    const [subtitle, setSubtitle] = useState("");
    const [renameFormatValue, setRenameFormatValue] = useState<JSX.Element[]>([]);

    const [isMobile, _setIsMobile] = useState(deceteMobile());
    const [mobileDatepicker1, setMobileDatepicker1] = useState(false);
    const [mobileDatepicker2, setMobileDatepicker2] = useState(false);
    const [mobileButtonText1, setMobileButtonText1] = useState("");
    const [mobileButtonText2, setMobileButtonText2] = useState("");
    const [mobileDateValue1, setMobileDateValue1] = useState("");
    const [mobileDateValue2, setMobileDateValue2] = useState("");

    const options: SelectProps['options'] = [
        { label: '.xlsx', value: '.xlsx' },
        { label: '.xls', value: '.xls' },
        { label: '.doc', value: '.doc' },
        { label: '.docx', value: '.docx' },
        { label: '.zip', value: '.zip' },
        { label: '.rar', value: '.rar' },
        { label: '.ppt', value: '.ppt' },
        { label: '.pptx', value: '.pptx' },
    ];

    const [defaultRange, _setDefaultRange] = useState<[dayjs.Dayjs, dayjs.Dayjs]>(() => {
        var now = dayjs(); // 获取当前时间
        var weekAgo = now.add(7, 'day'); // 计算一周前的时间
        return [now, weekAgo]; // 返回起始时间和结束时间的数组
    });


    const parseRenameFormat = (value: any) => {
        const regex = /\[(.*?)\]/g;
        let lastIndex = Math.random();
        const elements = [];

        let match;
        while ((match = regex.exec(value)) !== null) {
            const plainText = value.substring(lastIndex, match.index);
            const coloredText = match[0];

            elements.push(
                <span key={lastIndex}>{plainText}</span>,
                <span key={match.index} style={{ color: "lightgreen" }}>
                    {coloredText}
                </span>
            );

            lastIndex = match.index + match[0].length;
        }

        const remainingText = value.substring(lastIndex);
        elements.push(<span key={lastIndex}>{remainingText}</span>);
        setRenameFormatValue(elements);
        //return elements;
    };


    async function formSubmit(values: any) {

        try {
            var formData = new FormData();

            formData.append("title", values.title);
            formData.append("name", values.name);
            formData.append("class_id", values.class_id);
            formData.append("rename_format", values.rename_format);
            formData.append("requestment", Base64.encode(values.requestment));
            formData.append("accept", values.accept);

            if (!isMobile) {
                formData.append("start_time", dayjs(values.time[0]).format('YYYY-MM-DD HH:mm:ss'));
                formData.append("stop_time", dayjs(values.time[1]).format('YYYY-MM-DD HH:mm:ss'));
            } else {
                formData.append("start_time", mobileDateValue1);
                formData.append("stop_time", mobileDateValue2);
            }

            if (values.files && values.files.length > 0) {
                for (var file of values.files) {
                    if (file.size > FileMaxUplaodSize) {
                        messageService.error("文件体积过大 最大支持" + FileMaxUplaodSize / 1024 / 1024 + "MB");
                        return;
                    }
                    
                    var convertedFile = new File([file.originFileObj], file.name);
                    formData.append("files[]", convertedFile);
                }
            }

            const data = await activityApiService.createActivity(formData);

            if (data.code == 2) {
                setFormStatus(false);
                setSubtitle("请检查您的权限.");
                setResultError(true);
            } else if (data.code == 0) {
                setFormStatus(false);
                setSubtitle(data.data);
                setResultOK(true);
            }

        } catch (error: any) {
            setFormStatus(false);
            setSubtitle(error);
            setResultError(true);
        }
    }



    // 移动端日历组件适配
    function dataPickerMobileSelect1(values: any) {
        setMobileButtonText1(dayjs(values).format("YYYY-MM-DD HH:mm"));
        setMobileDateValue1(dayjs(values).format("YYYY-MM-DD HH:mm:ss"));
        setMobileDatepicker1(false);
    }

    // 移动端日历组件适配
    function dataPickerMobileSelect2(values: any) {
        setMobileButtonText2(dayjs(values).format("YYYY-MM-DD HH:mm"));
        setMobileDateValue2(dayjs(values).format("YYYY-MM-DD HH:mm:ss"));
        setMobileDatepicker2(false);
    }



    useEffect(() => {
        setTimeout(async () => {
            try {
                const response = await memberApiService.getClassList();
                const parsedData = response.data;

                const transformedData = parsedData.map((item: any) => ({
                    value: item.class_id,
                    label: item.class_name
                }));

                setClassList(transformedData);

                // 设置日期初始值
                if (isMobile) {
                    const date1: string = dayjs().format("YYYY-MM-DD HH:mm");
                    const date2: string = dayjs().add(1, "week").format("YYYY-MM-DD HH:mm");
                    setMobileButtonText1(date1);
                    setMobileButtonText2(date2);
                    setMobileDateValue1(dayjs(date1).format("YYYY-MM-DD HH:mm:ss"));
                    setMobileDateValue2(dayjs(date2).format("YYYY-MM-DD HH:mm:ss"));
                }
                parseRenameFormat(initialRenameFormat);

            } catch (error) {
                console.error('数据获取失败:', error);
            }
        });
    }, []);


    return (
        <>
            {formStatus && (
                <Form
                    id="form"
                    name="ActivityCreate"
                    {...formItemLayout}
                    onFinish={formSubmit}
                    style={{ maxWidth: 600 }}
                    autoComplete="on"
                >
                    <Form.Item
                        name="class_id"
                        label="发布班级"
                        rules={[{ required: true, message: '请选择班级' }]}
                    >
                        <Select placeholder="请选择班级" options={classList!}
                            showSearch
                            filterOption={(input, option) => (option?.label ?? '').includes(input)}
                            filterSort={(optionA, optionB) =>
                                (optionA?.label ?? '').toLowerCase().localeCompare((optionB?.label ?? '').toLowerCase())
                            }
                        />
                    </Form.Item>
                    <Form.Item
                        label="活动名"
                        name="name"
                        rules={[{ required: true, message: '请填入活动名' },
                        {
                            message: '请勿输入特殊字符',
                            pattern: /^((?!\+|\.|\\|\/|:|\*|\?|<|>|\|| |'|%|@|#|&|\$|\^|&|\*).){1,20}$/
                        }]}
                    >
                        <Input maxLength={20} />
                    </Form.Item>

                    <Form.Item
                        label="作业标题"
                        name="title"
                        rules={[{ required: true, message: '请填入作业标题' },
                        {
                            message: '请勿输入特殊字符',
                            pattern: /^((?!\+|\.|\\|\/|:|\*|\?|<|>|\|| |'|%|@|#|&|\$|\^|&|\*).){1,20}$/
                        }]}
                    >
                        <Input maxLength={20} />
                    </Form.Item>


                    <Form.Item
                        name="requestment"
                        label="作业要求"
                        rules={[{ required: true, message: '请选择作业要求' }]}
                    >
                        <Input.TextArea maxLength={675} autoSize allowClear />
                    </Form.Item>



                    <Form.Item
                        name="accept"
                        label="作业格式"
                        rules={[{ required: true }]}
                        initialValue={[".doc", ".docx"]}
                    >
                        <Select
                            mode="tags"
                            style={{ width: '100%' }}
                            onChange={() => { }}
                            tokenSeparators={['|', ","]}
                            options={options}
                        />
                    </Form.Item>


                    <Form.Item
                        name="rename_format"
                        label="作业命名格式"
                        rules={[{ required: true },
                        {
                            message: '请勿输入特殊字符',
                            pattern: /^((?!\+|\.|\\|\/|:|\*|\?|<|>|\||'|%|@|#|&|\$|\^|&|\*).){1,99}$/
                        }]}
                        initialValue={initialRenameFormat}
                    >
                        <Input
                            onChange={(value) => parseRenameFormat(value.target.value)} maxLength={99}
                        />
                    </Form.Item>


                    <Form.Item label="作业命名格式">
                        {renameFormatValue!}
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


                    <Form.Item
                        name="files"
                        label="文件上传"
                        valuePropName="fileList"
                        getValueFromEvent={normFile}
                    >
                        <Dragger  {...props}
                            customRequest={formSubmit}
                            onDrop={(event: any) => {
                                event.preventDefault();
                                event.stopPropagation();
                            }}
                            beforeUpload={() => false}

                        >
                            <p className="ant-upload-drag-icon">
                                <InboxOutlined />
                            </p>
                            <p className="ant-upload-text">请拖拽或点击此处上传文件</p>
                        </Dragger>
                    </Form.Item>


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

export default ActivityCreate;
