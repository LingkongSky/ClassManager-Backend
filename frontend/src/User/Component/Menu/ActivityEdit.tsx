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
    message,
    Switch,
    TreeSelect
} from 'antd';
const { RangePicker } = DatePicker;
const formItemLayout = {
    labelCol: { span: 6 },
    wrapperCol: { span: 14 },
};
const { Dragger } = Upload;
import type { UploadProps, SelectProps } from 'antd';
import { deceteMobile, FileMaxUplaodSize } from '../../../global.ts';
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

const props: UploadProps = {
    //action: ClassManagerSite + '/Server/admin/memberList.php',
    method: 'POST',
    multiple: true
};


interface ActivityType {
    name: string,
    title: string,
    requestment: string,
    class_id: string,
    // course_id: string,
    rename_format: string;
    start_time: string,
    stop_time: string
    accept: string,
    files: string[],
};


const ActivityEdit: React.FC = () => {
    //const [open, setOpen] = useState(false);
    const [classList, setClassList] = useCachedState<null | SelectList[]>('options:classes', null);
    // const [courseList, setCourseList] = useState<null | SelectList[]>(null);
    const [activityList, setActivityList] = useState<null | SelectList[]>(null);
    const [showFileUpload, setShowFileUpload] = useState(false);
    const [ActivityEditForm] = Form.useForm();
    const [fileList, setFileList] = useState<null | string[]>(null);
    const [renameFormatValue, setRenameFormatValue] = useState<JSX.Element[]>([]);

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

    function switchChange(checked: boolean) {
        setShowFileUpload(checked);
    }


    // 获取活动信息并填写表单
    async function activitySelect(value: any) {
        try {
            const response = await activityApiService.getContentActivity(value);

            const info: ActivityType = response.data;

            if (!isMobile) {
                ActivityEditForm.setFieldsValue({
                    name: info.name,
                    title: info.title,
                    requestment: Base64.decode(info.requestment),
                    rename_format: info.rename_format,
                    accept: info.accept.split(","),
                    time: [dayjs(info.start_time), dayjs(info.stop_time)]
                });
            } else {
                ActivityEditForm.setFieldsValue({
                    name: info.name,
                    title: info.title,
                    requestment: Base64.decode(info.requestment),
                    rename_format: info.rename_format,
                    accept: info.accept.split(","),
                });
                setMobileButtonText1(dayjs(info.start_time).format("YYYY-MM-DD HH:mm"));
                setMobileButtonText2(dayjs(info.stop_time).format("YYYY-MM-DD HH:mm"));
                setMobileDateValue1(dayjs(info.start_time).format("YYYY-MM-DD HH:mm:ss"));
                setMobileDateValue2(dayjs(info.stop_time).format("YYYY-MM-DD HH:mm:ss"));
            }

            if (info.files.length != 0) {
                setFileList(info.files);
            }
            parseRenameFormat(info.rename_format);

        } catch (error: any) {
            console.error("获取活动信息失败:", error);
            message.error(error.message || "获取活动信息失败");
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

    // 获取班级列表
    async function get_class_list() {
        try {
            const response = await memberApiService.getClassList();

            const parsedData = response.data;
            const transformedData = parsedData.map((item: any) => ({
                value: item.class_id,
                label: item.class_name
            }));

            setClassList(transformedData);

            let result = null;
            if (transformedData && transformedData.length > 0) {
                result = transformedData[0]['value'];
                ActivityEditForm.setFieldsValue({
                    class_id: result
                });
            } else {
                console.warn('班级列表为空');
            }

        } catch (error: any) {
            console.error("获取班级列表失败:", error);
        }
    }

    // 获取活动列表
    async function get_activity_list() {
        try {
            const response = await activityApiService.listActivity(ActivityEditForm.getFieldValue('class_id'));

            const parsedData = response.data;
            setActivityList(parsedData);

            ActivityEditForm.setFieldsValue({
                name: "",
                activity_id: "",
                title: "",
                requestment: "",
                rename_format: "",
                accept: [".doc", ".docx"],
            });
            setRenameFormatValue([]);

        } catch (error) {
            console.error('获取活动列表失败:', error);
        }
    }

    async function formSubmit(values: any) {
        try {
            var formData = new FormData();

            formData.append("activity_id", values.activity_id);
            formData.append("name", values.name);
            formData.append("title", values.title);
            formData.append("rename_format", values.rename_format);
            formData.append("requestment", Base64.encode(values.requestment));
            formData.append("accept", values.accept);
            formData.append("file_upload", showFileUpload.toString());
            
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

            const data = await activityApiService.editActivity(formData);

            if (data.code == 2) {
                setFormStatus(false);
                setSubtitle("请检查您的权限.");
                setResultError(true);
            } else if (data.code == 0) {
                setFormStatus(false);
                setSubtitle(data.data);
                setResultOK(true);
            }

        } catch (error) {
            setFormStatus(false);
            setResultError(true);
        }
    }


    // 请求所有活动
    useEffect(() => {
        setTimeout(async () => {
            await get_class_list();
            await get_activity_list();
        });


        if (isMobile) {
            var date1: string = dayjs().format("YYYY-MM-DD HH:mm");
            var date2: string = dayjs().add(1, "week").format("YYYY-MM-DD HH:mm");
            setMobileButtonText1(date1);
            setMobileButtonText2(date2);
            setMobileDateValue1(dayjs(date1).format("YYYY-MM-DD HH:mm:ss"));
            setMobileDateValue2(dayjs(date2).format("YYYY-MM-DD HH:mm:ss"));
        }
    }, []);


    return (
        <>
            {formStatus && (
                <Form
                    id="ActivityEdit"
                    name="ActivityEdit"
                    form={ActivityEditForm}
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
                            onChange={() => get_activity_list()}
                        />
                    </Form.Item>

                    <Form.Item
                        label="选择活动"
                        name="activity_id"
                        rules={[{ required: true, message: '请选择活动' }]}
                    >

                        <TreeSelect
                            treeData={activityList!}
                            showSearch={{
                                filterTreeNode: true,
                                treeNodeFilterProp: 'title'
                            }} style={{ width: '100%' }}
                            styles={{
                                popup: {
                                    root: {
                                        maxHeight: 400,
                                        overflow: 'auto'
                                    }
                                }
                            }} allowClear
                            onSelect={(value) => { activitySelect(value) }}
                        />

                    </Form.Item>


                    <Form.Item
                        label="活动名"
                        name="name"
                        rules={[{ required: true, message: '请填入名称' },
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




                    {fileList && <Form.Item label="文件列表">
                        {fileList?.map((file, index) => {
                            const fileName = file.substring(file.lastIndexOf('/') + 1);
                            return (
                                <>
                                    <a key={index} href={file} style={{ height: "32px", display: "inline-flex", alignItems: "center" }} target="_blank" rel="noopener noreferrer">
                                        {fileName}
                                    </a>
                                    <br />
                                </>
                            );
                        })}

                    </Form.Item>}


                    <Form.Item
                        name="fileReupload"
                        label="文件上传"
                    >
                        <Switch checkedChildren="启用" unCheckedChildren="关闭" onChange={switchChange} />
                    </Form.Item>


                    {showFileUpload && <Form.Item
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
                            <p className="ant-upload-hint">
                                注：在开启文件上传后，无论是否上传文件都将覆盖原活动文件
                            </p>
                        </Dragger>
                    </Form.Item>}




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

export default ActivityEdit;
