import type { ActionType, ProColumns } from '@ant-design/pro-components';
import { ProTable } from '@ant-design/pro-components';
import {
    DatePicker,
    Form,
    GetProp,
    Input,
    Modal,
    TableProps,
} from 'antd';
import { useEffect, useState } from 'react';
import { DatePicker as DatePickerMobile, Button as ButtonMobile } from 'antd-mobile'
import dayjs from 'dayjs';
const { RangePicker } = DatePicker;
import { deceteMobile } from '../../../global.ts';
import Markdown from 'react-markdown';
import remarkGfm from 'remark-gfm';
import { Base64 } from 'js-base64';
import { useCachedState } from '../../../Module/DataCache.ts';
import messageService from '../../../Module/messageService.ts';
import { mailApiService } from '../../../Api/mail.ts';
import { FilterValue, SorterResult } from 'antd/lib/table/interface.js';

type TableListItem = {
    key: number;
    mail_id: string;
    title: string;
    status: string;
    target: string;
    owner: string;
    start_time: string;
    stop_time: string;
    read_situation: string;
};


const TypeDict: { [key: string]: number } = {
    all: 0,
    admin: 1,
    class: 2
}

var searchDataSource: TableListItem[] = [];

type TablePaginationConfig = Exclude<GetProp<TableProps, 'pagination'>, boolean>;
interface TableParams {
    pagination?: TablePaginationConfig;
    sortField?: string;
    sortOrder?: string;
    filters?: Parameters<GetProp<TableProps, 'onChange'>>[1];
}

const formItemLayout = {
    labelCol: { span: 5 },
    wrapperCol: { span: 16 },
};


/*
初始请求最大数量
根据最大数量填充空数组
请求第一页并填充数据到第一页
切换页时先检测该页是否已加载，再进行请求
进行搜索时带上搜索词重复以上内容

对各个修改内容的函数进行重新请求

*/

// 单页20条 每次加载40条数据
let queryWord: string = "";

const MailContent: React.FC = () => {

    const [dataSource, setDataSource] = useCachedState<TableListItem[]>('table:mails', []);

    //const [searchDataSource, setSearchDataSource] = useState<TableListItem[]>([]);

    const [, setLoading] = useState(true);
    const [error, setError] = useState(false);
    const [showDeleteMail, setShowDeleteMail] = useState(false);
    const [deleteMailName, setDeleteMailName] = useState("");
    const [deleteMailId, setDeleteMailId] = useState("");
    const [openEditTime, setOpenEditTime] = useState(false);
    const [showConfirmDeleteMail, setShowConfirmDeleteMail] = useState(false);

    const [showMailContent, setShowMailContent] = useState(false);
    const [form1] = Form.useForm();
    const [markdownContet, setMarkdownContent] = useState("");


    const [tableParams, setTableParams] = useState<TableParams>({
        pagination: {
            current: 1,
            pageSize: 20,
            total: 20
        },
    });

    const [mobileDatepicker1, setMobileDatepicker1] = useState(false);
    const [mobileDatepicker2, setMobileDatepicker2] = useState(false);
    const [mobileButtonText1, setMobileButtonText1] = useState("");
    const [mobileButtonText2, setMobileButtonText2] = useState("");
    const [mobileDateValue1, setMobileDateValue1] = useState("");
    const [mobileDateValue2, setMobileDateValue2] = useState("");
    const [isMobile, _setIsMobile] = useState(deceteMobile());

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

    // 列表结构
    const columns: ProColumns<TableListItem>[] = [
        {
            title: '名称',
            dataIndex: 'title',
            key: 'title',
            render: (_) => <a>{_}</a>,
            //sorter: (a, b) => a.name.localeCompare(b.name)
        },
        {
            title: 'ID',
            dataIndex: 'mail_id',
            key: 'mail_id',
            //sorter: (a, b) => a.mail_id.localeCompare(b.mail_id),
            hidden: true
        },
        {
            title: '发布对象',
            dataIndex: 'target',
            key: 'target',
            //sorter: (a, b) => a.mail_id.localeCompare(b.mail_id),
        },
        {
            title: '所有者',
            dataIndex: 'owner',
            key: 'owner',
            //sorter: (a, b) => a.mail_id.localeCompare(b.mail_id),
        },
        {
            title: '进度',
            dataIndex: 'read_situation',
            key: 'read_situation',
            //sorter: (a, b) => a.mail_id.localeCompare(b.mail_id),
        },
        {
            title: '状态',
            dataIndex: 'status',
            key: 'status',
            render: (text) => (
                <span style={{ color: getStatusColor(text as string) }}>{text}</span>
            ),
            //sorter: (a, b) => a.status.localeCompare(b.status)
        },
        {
            title: '开始日期',
            dataIndex: 'start_time',
            key: 'start_time',
            //sorter: (a, b) => a.start_time.localeCompare(b.start_time),
            hidden: isMobile
        },
        {
            title: '截止日期',
            dataIndex: 'stop_time',
            key: 'stop_time',
            //sorter: (a, b) => a.stop_time.localeCompare(b.stop_time),
            hidden: isMobile
        },
        {
            title: '操作',
            key: 'option',
            valueType: 'option',
            render: (_, entity) => [
                <a onClick={() => mail_edit_time(entity.mail_id, entity.title, entity.start_time, entity.stop_time)} key="edit_time">
                    编辑时间
                </a>,
                <a onClick={() => mail_show(entity.mail_id)} key="link">
                    查看
                </a>,
                <a onClick={() => mail_delete(entity.mail_id, entity.title)} key="warn">
                    删除
                </a>,
            ],
        },
    ];

    const statusMap: { [key: number]: string } = {
        0: '未开始',
        1: '进行中',
        2: '已结束',
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case '未开始':
                return 'red';
            case '进行中':
                return 'lightgreen';
            case '已结束':
                return 'gray';
            default:
                return 'inherit';
        }
    };

    // 渲染公告表格
    async function get_mail_content(query_count: number) {
        try {
            const response = await mailApiService.getMailList(query_count, queryWord);

            const parsedData = response.data;

            const tableData: TableListItem[] = parsedData.map((item: any) => ({
                key: item.mail_id,
                mail_id: item.mail_id,
                title: item.title,
                target: parsedTarget(item.target),
                owner: item.owner,
                read_situation: item.read_situation,
                start_time: item.start_time,
                stop_time: item.stop_time,
                status: statusMap[item.status],
            }));

            // 覆盖原数组
            const dataLength = tableData.length;
            let tempData: TableListItem[] = searchDataSource.slice();

            for (let i = 0; i < dataLength; i++) {
                tempData[i + query_count] = tableData[i];
            }

            setDataSource(tempData.slice());
            searchDataSource = tempData.slice();

        } catch (error) {
            console.error('get_mail_content error:', error);
            setError(true);
        }
    }
    function parsedTarget(data: any) {
        //对info.target和content进行解码，获取类型和值
        //var target = JSON.parse(Base64.decode(data.data));

        var target = JSON.parse(Base64.decode(data));
        var type: number = TypeDict[target.target];
        if (type == 0) {
            return "所有用户";
        } else if (type == 1) {
            return "仅管理员";
        } else if (type == 2) {

            // 根据class_id获取班级名称
            return "班级";
        }
    }


    // 获取公告总数并进行表格数组填充初始化
    const getMaxMail = async () => {
     /*   try {

            const mailCount = await generalApiService.getQueryCount("/Server/admin/mailManager.php", "6", queryWord);
            var count = Number(mailCount.data);
            if (isNaN(count)) return;
            setTableParams({
                ...tableParams,
                pagination: {
                    ...tableParams.pagination,
                    total: count, // 设置总页数
                    current: 1,
                    showSizeChanger: false,
                    showQuickJumper: true
                },
            });

            // 进行数据初始化
            const tableData: TableListItem[] = Array.from({ length: count }, () => ({
                key: Math.random(),
                mail_id: "",
                title: "",
                target: "",
                owner: "",
                read_situation: "",
                start_time: "",
                stop_time: "",
                status: ""
            }));

            setDataSource(tableData.slice());
            //setSearchDataSource(tableData);
            searchDataSource = tableData.slice();

        } catch (error: any) {
            messageService.error(error);
            setError(true);
        }*/
    }

    // 处理缓存部分 防止重复加载
    const handleTableChange: TableProps<TableListItem>['onChange'] = (
        pagination: TablePaginationConfig,
        filters: Record<string, FilterValue | null>,
        sorter: SorterResult<TableListItem> | SorterResult<TableListItem>[],) => {
        setTableParams({
            pagination,
            filters,
            ...sorter,
        });

        if (!(Number(dataSource[(pagination.current! - 1) * 20]?.mail_id) >= 1)) {      // 通过取值判断当前页是否有数据，没有则请求
            get_mail_content((pagination.current! - 1) * 20);
        }
    }


    function refreshForm1() {
        getMaxMail();
        get_mail_content(0);
        messageService.success("刷新成功");
    }


    // 删除公告
    const mail_delete = (mail_id: any, mail_name: any) => {
        setDeleteMailId(mail_id);
        setDeleteMailName(mail_name);
        setShowDeleteMail(true);
    }

    // 编辑公告时间
    const mail_edit_time = (mail_id: any, mail_name: any, start_time: any, stop_time: any) => {

        setOpenEditTime(true);
        form1.setFieldsValue({
            mail_id: mail_id,
            name: mail_name,
        });


        if (!isMobile) {
            form1.setFieldsValue({
                time: [dayjs(start_time), dayjs(stop_time)]
            });
        } else {
            setMobileButtonText1(dayjs(start_time).format("YYYY-MM-DD HH:mm"));
            setMobileButtonText2(dayjs(stop_time).format("YYYY-MM-DD HH:mm"));
            setMobileDateValue1(dayjs(start_time).format("YYYY-MM-DD HH:mm"));
            setMobileDateValue2(dayjs(stop_time).format("YYYY-MM-DD HH:mm"));
        }
    }

    // 提交公告删除
    const confirmDelete = async () => {
        try {
            await mailApiService.deleteMail(deleteMailId);

            setShowDeleteMail(false);
            setShowConfirmDeleteMail(false);

            messageService.info('删除成功');
            setLoading(true);

            await Promise.all([
                getMaxMail(),
                get_mail_content(0)
            ]);

            setLoading(false);

        } catch (error) {
            console.error('confirmDelete error:', error);
            setShowDeleteMail(false);
            setShowConfirmDeleteMail(false);
            messageService.error(`删除失败: ${error instanceof Error ? error.message : '未知错误'}`);
        }
    };

    // 渲染公告正文
    async function mail_show(mail_id: string) {
        try {
            setShowMailContent(true);
            const response = await mailApiService.getMailContent(mail_id);
            const decodedContent = Base64.decode(response.data);
            setMarkdownContent(decodedContent);

        } catch (error) {
            console.error('mail_show error:', error);
            messageService.error(`加载邮件失败: ${error instanceof Error ? error.message : '未知错误'}`);
            setMarkdownContent('邮件内容加载失败');
        }
    }



    // 编辑公告时间提交
    async function time_edit_submit(values: any) {
        let start_time: string;
        let stop_time: string;

        if (!isMobile) {
            start_time = dayjs(values.time[0]).format('YYYY-MM-DD HH:mm:ss');
            stop_time = dayjs(values.time[1]).format('YYYY-MM-DD HH:mm:ss');
        } else {
            start_time = mobileDateValue1;
            stop_time = mobileDateValue2;
        }

        try {
            await mailApiService.editMailTime(
                values.mail_id,
                start_time,
                stop_time
            );
            messageService.info("修改成功");
            setLoading(true);
            await get_mail_content(0);
            setLoading(false);
        } catch (error) {
            console.error('time_edit_submit error:', error);
            messageService.error(`修改失败: ${error instanceof Error ? error.message : '未知错误'}`);
        } 

        setOpenEditTime(false);
        
    }

    useEffect(() => {
        setTimeout(() => {
            queryWord = "";
            getMaxMail();
            get_mail_content(0);
        }, 0);
        setLoading(false);
    }, []);


    return (
        <>

            {!error && <ProTable<TableListItem>
                columns={columns}
                dataSource={dataSource}
                pagination={tableParams.pagination}
                onChange={handleTableChange}
                options={{
                    reload: ((_e: React.MouseEvent<HTMLSpanElement>, _action?: ActionType) => refreshForm1())
                }}
                toolbar={{
                    search: {
                        onSearch: (value: string) => {
                            queryWord = value;
                            getMaxMail();
                            get_mail_content(0);
                        },
                        placeholder: "请输入名称"
                    },
                }}
                rowKey="key"
                search={false}
            />}

            <Modal title="删除公告" open={showDeleteMail} onOk={() => { setShowConfirmDeleteMail(true) }} onCancel={() => { setShowDeleteMail(false) }} destroyOnHidden okType="danger" okText="确定">
                <p>您确定要删除<b>{deleteMailName}</b>吗？这将永久删除该条目及所有相关数据及文件，并且不可恢复！</p>
            </Modal>

            <Modal title="删除公告" open={showConfirmDeleteMail} onOk={confirmDelete} onCancel={() => { setShowConfirmDeleteMail(false) }} destroyOnHidden okType="danger" okText="再次确定">
                <p>您真的确认要删除<b>{deleteMailName}</b>吗？</p>
            </Modal>



            <Modal
                title="编辑时间"
                open={openEditTime}
                onOk={() => { form1.submit() }}
                onCancel={() => { setOpenEditTime(false) }}
            >

                <Form {...formItemLayout} form={form1} onFinish={time_edit_submit} style={{ maxWidth: 600 }} >
                    {<Form.Item label="公告ID" name="mail_id" >
                        <Input disabled />
                    </Form.Item>}
                    <Form.Item label="公告名称" name="name">
                        <Input disabled />
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

                </Form>
            </Modal>




            <Modal title="预览正文" open={showMailContent} footer={null} onOk={() => { setShowMailContent(true) }} onCancel={() => { setShowMailContent(false) }} destroyOnHidden okText="确定">
                <Markdown remarkPlugins={[remarkGfm]}>{markdownContet}</Markdown>
            </Modal>




        </>
    );
};

export default MailContent;
