import type { ActionType, ProColumns } from '@ant-design/pro-components';
import { ProTable } from '@ant-design/pro-components';
import {
    Button,
    DatePicker,
    Form,
    GetProp,
    Input,
    Modal,
    Select,
    TableProps,
    Typography,
    Upload,
} from 'antd';
import { useEffect, useState } from 'react';
import { DatePicker as DatePickerMobile, Button as ButtonMobile } from 'antd-mobile'
import dayjs from 'dayjs';
const { RangePicker } = DatePicker;
import { deceteMobile, FileMaxUplaodSize } from '../../../global.ts';
import { InboxOutlined } from '@ant-design/icons';
import messageService from '../../../Module/messageService.ts';
import { ClassSelectList } from '../../../Interface/class.ts';
import { memberApiService } from '../../../Api/memberList.ts';
import { SorterResult, FilterValue } from 'antd/es/table/interface.js';
import { activityApiService } from '../../../Api/activityContent.ts';
import { todosApiService } from '../../../Api/todos.ts';
import { normFile } from '../dashboard.tsx';
import { Base64 } from 'js-base64';
import { useCachedState } from '../../../Module/DataCache.ts';

type TableListItem = {
    key: number;
    name: string;
    activity_id: string;
    status: string;
    //class_name: string;
    progress: string;
    start_time: string;
    stop_time: string;
};


type Sorts = SorterResult<any>

type HomeworkTableListItem = {
    key: number;
    name: string;
    account: string;
    status: string;
    activity_id: string;
    studentID: string;
    submit_date: string;
    class_name: string;
    class_names?: string[];
    primary_class_name?: string;
};

interface HomeWorks {
    activity_id: string,
    name: string,
    requestment: string,
    files: string[],
    accept: string
}


var searchDataSource: TableListItem[] = [];
var oldOrder: String = "";


var searchDataSource2: HomeworkTableListItem[] = [];
var homeworkActivityID: string = "";
var homeworkActivityName: string = "";



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
let queryWord2: string = "";

const ActivityContent: React.FC = () => {
    const renderClassNames = (record: HomeworkTableListItem) => {
        const classNames = record.class_names ?? (record.class_name ? [record.class_name] : []);
        const primaryClassName = record.primary_class_name ?? classNames[0];
        const orderedClassNames = primaryClassName
            ? [primaryClassName, ...classNames.filter((name) => name !== primaryClassName)]
            : classNames;

        return (
            <>
                {orderedClassNames.map((name, index) => (
                    <span
                        key={`${record.account}-${name}-${index}`}
                        style={name === primaryClassName ? { textDecoration: 'underline' } : undefined}
                    >
                        {index > 0 ? ' / ' : ''}
                        {name}
                    </span>
                ))}
            </>
        );
    };

    const [dataSource, setDataSource] = useCachedState<TableListItem[]>('table:activities', []);

    //const [searchDataSource, setSearchDataSource] = useState<TableListItem[]>([]);

    const [homeworkDataSource, setHomeworkDataSource] = useState<HomeworkTableListItem[]>([]);
    //const [searchHomeworkDataSource, setSearchHomeworkDataSource] = useState<HomeworkTableListItem[]>([]);
    const [activityShowName, setActivityShowName] = useState("");
    const [, setLoading] = useState(true);
    const [error, setError] = useState(false);
    const [showHomework, setShowHomework] = useState(false);
    const [showDeleteActivity, setShowDeleteActivity] = useState(false);
    const [showConfirmExport, setShowConfirmExport] = useState(false);
    const [showDeleteFile, setShowDeleteFile] = useState(false);


    const [showFileUpload, setShowFileUpload] = useState(false);
    const [fileUploadAccount, setFileUploadAccount] = useState("");
    const [buttonLoading, setButtonLoading] = useState(false);
    const [homeworks, setHomeworks] = useState<null | HomeWorks>(null);
    const [rows, _setRows] = useState(2);
    const [expanded, setExpanded] = useState(false);



    const [exportActivityName, setExportActivityName] = useState("");
    const [exportActivityId, setExportActivityId] = useState("");
    const [deleteActivityName, setDeleteActivityName] = useState("");
    const [deleteActivityId, setDeleteActivityId] = useState("");
    const [deleteFileAccount, setDeleteFileAccount] = useState("");
    const [deleteFileAccountName, setDeleteFileAccountName] = useState("");
    const [openEditTime, setOpenEditTime] = useState(false);
    const [showConfirmDeleteActivity, setShowConfirmDeleteActivity] = useState(false);


    const [classList, setClassList] = useCachedState<null | ClassSelectList[]>('options:classes', null);
    const [selectedClassId, setSelectedClassId] = useCachedState<string | null>('selection:activities-class', null);
    const [defaultClassList, setDefaultClassList] = useState<string | undefined>(selectedClassId ?? undefined);

    const [form1] = Form.useForm();
    const [form2] = Form.useForm();
    const [ClassListForm] = Form.useForm();


    const [tableParams, setTableParams] = useState<TableParams>({
        pagination: {
            current: 1,
            pageSize: 20,
            total: 20
        },
    });

    const [tableParams2, setTableParams2] = useState<TableParams>({
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
            dataIndex: 'name',
            key: 'name',
            render: (_) => <a>{_}</a>,
            //sorter: (a, b) => a.name.localeCompare(b.name)
        },
        {
            title: 'ID',
            dataIndex: 'activity_id',
            key: 'activity_id',
            //sorter: (a, b) => a.activity_id.localeCompare(b.activity_id),
            hidden: isMobile
        },
        {
            title: '进度',
            dataIndex: 'progress',
            key: 'progress',
            //sorter: (a, b) => a.progress.localeCompare(b.progress)
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
            title: '状态',
            dataIndex: 'status',
            key: 'status',
            render: (text) => (
                <span style={{ color: getStatusColor(text as string) }}>{text}</span>
            ),
            //sorter: (a, b) => a.status.localeCompare(b.status)
        },
        {
            title: '操作',
            key: 'option',
            valueType: 'option',
            render: (_, entity) => [
                <a onClick={() => activity_edit_time(entity.key, entity.name, entity.start_time, entity.stop_time)} key="edit_time">
                    编辑时间
                </a>,
                <a onClick={() => activity_output(entity.key, entity.name, "first")} key="output">
                    导出
                </a>,
                <a onClick={() => activity_show(entity.key, entity.name)} key="link">
                    查看
                </a>,
                <a onClick={() => activity_delete(entity.key, entity.name)} key="warn">
                    删除
                </a>,
            ],
        },
    ];


    const homeworkColumns: ProColumns<HomeworkTableListItem>[] = [
        {
            title: '班级',
            dataIndex: 'class_name',
            key: 'class_name',
            render: (_, record) => renderClassNames(record),
            //sorter: (a, b) => a.class_name.localeCompare(b.class_name),
        },
        {
            title: '姓名',
            dataIndex: 'name',
            key: 'students.name',
            sorter: true,
            render: (_) => <a>{_}</a>,
            //sorter: (a, b) => a.name.localeCompare(b.name)
        },
        {
            title: '学号',
            dataIndex: 'account',
            key: 'students.account',
            sorter: true,
            //sorter: (a, b) => a.account.localeCompare(b.account), 
        },
        {
            title: '状态',
            dataIndex: 'status',
            key: 'status',
            render: (text) => (
                <span style={{ color: getStatusColor2(text as string) }}>{text}</span>
            ),
            // sorter: () => {return homeworkSort();},
        },
        {
            title: '提交日期',
            dataIndex: 'submit_date',
            key: 'homework.update_time',
            sorter: true,
            //sorter: (a, b) => a.submit_date.localeCompare(b.submit_date)
        },
        {
            title: '操作',
            key: 'option',
            valueType: 'option',
            render: (_, entity) => {
                if (entity.status === '已提交') {
                    return [
                        <a onClick={() => file_output(entity.activity_id, entity.account)} key="file_download">
                            下载文件
                        </a>,
                        <a onClick={() => fileUpload(entity.account)} key="file_reupload">
                            重新提交
                        </a>,
                        <a style={{ color: "red" }} onClick={() => { setShowDeleteFile(true); setDeleteFileAccount(entity.account); setDeleteFileAccountName(entity.name); }} key="file_delete">
                            删除提交
                        </a>
                    ];
                }
                return [
                    <a onClick={() => fileUpload(entity.account)} key="file_upload">
                        手动提交
                    </a>
                ]
            },
        },
    ];


    const statusMap: { [key: number]: string } = {
        0: '未开始',
        1: '进行中',
        2: '已结束',
    };


    const statusMap2: { [key: number]: string } = {
        0: '未提交',
        1: '已提交',
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

    const getStatusColor2 = (status: string) => {
        switch (status) {
            case '未提交':
                return 'red';
            case '已提交':
                return 'lightgreen';
            default:
                return 'inherit';
        }
    };


    async function getSortData(query_count: number, sortType: Sorts) {
        try {
            var sortMode: string = "asc";
            if (sortType.order == "ascend") {
                sortMode = "asc";
            } else if (sortType.order == "descend") {
                sortMode = "desc";
            } else {
                return;
            }

            const response = await activityApiService.getSortActivityTable(
                homeworkActivityID,
                query_count,
                queryWord2,
                sortMode,
                sortType.columnKey
            );

            if (response.code != 0) {
                //setError(true);
                return;
            }

            const parsedData = response.data;
            const tableData: HomeworkTableListItem[] = parsedData.map((item: any) => ({
                key: item.account,
                activity_id: homeworkActivityID,
                name: item.name,
                account: item.account,
                class_name: item.class_name,
                class_names: item.class_names,
                primary_class_name: item.primary_class_name,
                status: statusMap2[item.status],
                submit_date: item.submit_date,
            }));

            var dataLength = tableData.length;
            var tempData: HomeworkTableListItem[] = searchDataSource2.slice();
            for (var i = 0; i < dataLength; i++) {
                tempData[i + query_count] = tableData[i];
            }

            setHomeworkDataSource(tempData.slice());
            searchDataSource2 = tempData.slice();
        } catch (error: any) {
            console.error('getSortData error:', error);
            setError(true);
        }
    }

    async function get_class_list() {
        var parsedData;
        try {
            const data = await memberApiService.getClassList();
            parsedData = data.data;
            const transformedData = parsedData.map((item: any) => ({
                value: item.class_id,
                label: item.class_name
            }));

            setClassList(transformedData);
            if (transformedData.length === 0) return;

            const preferredClassId = transformedData.some((item: ClassSelectList) => item.value === selectedClassId)
                ? selectedClassId as string
                : transformedData[0]['value'];
            if (selectedClassId && selectedClassId !== preferredClassId) {
                setDataSource([]);
                searchDataSource = [];
            }
            setSelectedClassId(preferredClassId);
            setDefaultClassList(preferredClassId);
            ClassListForm.setFieldsValue({
                class_id: preferredClassId
            });
        } catch (error: any) {
            messageService.error('获取班级列表失败: ' + error.message);
        }
    }


    // 渲染活动表格

    async function get_activity_content(query_count: number) {
        try {
            var class_id = ClassListForm.getFieldValue('class_id');

            const response = await activityApiService.getActivityTable(
                class_id,
                query_count,
                queryWord
            );

            const parsedData = response.data;
            const tableData: TableListItem[] = parsedData.map((item: any) => ({
                key: item.activity_id,
                activity_id: item.activity_id,
                name: item.name,
                progress: item.progress,
                start_time: item.start_time,
                stop_time: item.stop_time,
                status: statusMap[item.status],
            }));

            var dataLength = tableData.length;
            var tempData: TableListItem[] = searchDataSource.slice();
            for (var i = 0; i < dataLength; i++) {
                tempData[i + query_count] = tableData[i];
            }

            setDataSource(tempData.slice());
            searchDataSource = tempData.slice();
        } catch (error: any) {
            console.info(error);
        }
    }


    // 获取活动总数并进行表格数组填充初始化
    async function getMaxActivity() {
        try {
            const response = await activityApiService.getActivityCount(
                ClassListForm.getFieldValue('class_id'),
                queryWord
            );

            var count = Number(response.data);
            if (isNaN(count)) return;

            setTableParams({
                ...tableParams,
                pagination: {
                    ...tableParams.pagination,
                    total: count,
                    current: 1,
                    showSizeChanger: false,
                    showQuickJumper: true
                },
            });

            setDataSource((current) => {
                const tableData: TableListItem[] = Array.from({ length: count }, (_, index) =>
                    current[index]?.activity_id ? current[index] : {
                        key: Math.random(),
                        activity_id: "",
                        name: "",
                        progress: "",
                        start_time: "",
                        stop_time: "",
                        status: ""
                    });
                searchDataSource = tableData.slice();
                return tableData;
            });
        } catch (error: any) {
            console.error('getMaxActivity error:', error);
            setError(true);
        }
    }


    async function getMaxStudent() {
        try {
            const class_id = ClassListForm.getFieldValue('class_id');

            // 调用 API 服务获取学生数量
            const data = await memberApiService.getStudentCount(class_id, queryWord2);

            const count = Number(data.data);
            if (isNaN(count)) return;

            // 更新分页参数
            setTableParams2({
                ...tableParams2,
                pagination: {
                    ...tableParams2.pagination,
                    total: count,
                    current: 1,
                    showSizeChanger: false,
                    showQuickJumper: true
                },
            });

            // 初始化表格数据
            const tableData: HomeworkTableListItem[] = Array.from({ length: count }, () => ({
                key: Math.random(),
                name: "",
                account: "",
                status: "",
                activity_id: "",
                studentID: "",
                submit_date: "",
                class_name: ""
            }));

            setHomeworkDataSource(tableData.slice());
            searchDataSource2 = tableData.slice();

        } catch (error: any) {
            messageService.error(error?.message || '获取学生数据失败');
        }
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

        if (!(Number(dataSource[(pagination.current! - 1) * 20]?.activity_id) >= 1)) {      // 通过取值判断当前页是否有数据，没有则请求
            get_activity_content((pagination.current! - 1) * 20);
        }
    }


    // 处理缓存部分 防止重复加载
    const handleTableChange2: TableProps<HomeworkTableListItem>['onChange'] = async (
        pagination: TablePaginationConfig,
        filters: Record<string, FilterValue | null>,
        sorter: SorterResult<HomeworkTableListItem> | SorterResult<HomeworkTableListItem>[],) => {
        setTableParams2({
            pagination,
            filters,
            ...sorter,
        });

        var sort: Sorts = sorter as Sorts;

        // 确保在修改排序时重置表单
        if (sort.order) {

            if (sort.order != oldOrder) {
                oldOrder = sort.order;
                await getMaxStudent();
                await getSortData(0, sort);
            } else {
                if (!(Number(homeworkDataSource[(pagination.current! - 1) * 20].account) >= 1))// 使用缓存
                    getSortData((pagination.current! - 1) * 20, sort);
            }
            return;
        }

        // 在切换排序时重置数据
        if (oldOrder != "") {
            oldOrder = "";
            await getMaxStudent();
            await get_homework_list(0);
            return;
        }

        if (!(Number(homeworkDataSource[(pagination.current! - 1) * 20].account) >= 1)) {      // 通过取值判断当前页是否有数据，没有则请求
            await get_homework_list((pagination.current! - 1) * 20);
        }
    }


    async function TableClassSet(classId: string) {
        setSelectedClassId(classId);
        setLoading(true);
        await getMaxActivity();
        await get_activity_content(0);
        setLoading(false);
    }

    // 开始请求作业提交情况表
    const activity_show = async (activity_id: any, name: any) => {
        homeworkActivityID = activity_id;
        homeworkActivityName = name;
        await getMaxStudent()
        await get_homework_list(0);
        setActivityShowName(homeworkActivityName);
        setShowHomework(true);
    }



    // 请求作业列表
    async function get_homework_list(query_count: number) {
        try {
            const response = await activityApiService.getHomeworkTable(
                homeworkActivityID,
                query_count,
                queryWord2
            );

            if (response.code != 0) {
                setError(true);
                return;
            }

            const parsedData = response.data;
            const tableData: HomeworkTableListItem[] = parsedData.map((item: any) => ({
                key: item.account,
                activity_id: homeworkActivityID,
                name: item.name,
                account: item.account,
                class_name: item.class_name,
                class_names: item.class_names,
                primary_class_name: item.primary_class_name,
                status: statusMap2[item.status],
                submit_date: item.submit_date,
            }));

            var dataLength = tableData.length;
            var tempData: HomeworkTableListItem[] = searchDataSource2.slice();
            for (var i = 0; i < dataLength; i++) {
                tempData[i + query_count] = tableData[i];
            }

            setHomeworkDataSource(tempData.slice());
            searchDataSource2 = tempData.slice();
        } catch (error: any) {
            console.error('get_homework_list error:', error);
            setError(true);
        }
    }

    async function refreshForm1() {
        await getMaxActivity();
        await get_activity_content(0);
        messageService.success("刷新成功");
    }

    async function refreshForm2() {
        await getMaxStudent();
        await get_homework_list(0);
        messageService.success("刷新成功");
    }

    // 删除活动
    const activity_delete = (activity_id: any, activity_name: any) => {
        setDeleteActivityId(activity_id);
        setDeleteActivityName(activity_name);
        setShowDeleteActivity(true);
    }

    // 编辑活动时间
    const activity_edit_time = (activity_id: any, activity_name: any, start_time: any, stop_time: any) => {

        setOpenEditTime(true);
        form1.setFieldsValue({
            activity_id: activity_id,
            name: activity_name,
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
    // 提交活动删除
    async function confirmDelete() {
        try {
            await activityApiService.deleteActivity(deleteActivityId);

            setShowDeleteActivity(false);
            setShowConfirmDeleteActivity(false);
            messageService.info('删除成功');
            //setLoading(true);
            await getMaxActivity();
            await get_activity_content(0);
            //setLoading(false);

        } catch (error: any) {
            console.error('confirmDelete error:', error);
            messageService.error("删除失败：" + error.message);
        }
    }

    // 导出 打包zip
    async function activity_output(activity_id: any, activity_export_name: any, times: any) {
        try {
            if (times != "second") {
                messageService.info("导出中，文件较大可能花费较多时间，请勿关闭此页面耐心等待");
            }

            const response = await activityApiService.exportActivity(activity_id, times);
            if (response.code == 1) {
                messageService.error("找不到活动");
                return;
            } else if (response.code == 4) {
                messageService.error("没有可导出的内容");
            } else if (response.code == 3) {
                setExportActivityName(activity_export_name);
                setExportActivityId(activity_id);
                setShowConfirmExport(true);
            }

        } catch (error: any) {
            console.error('activity_output error:', error);
            messageService.error(error.message || '导出失败');
        }
    }



    // 下载学生作业
    const file_output = async (activity_id: string, account: string) => {
        try {
            const response = await todosApiService.downloadHomeworkAdmin(activity_id, account);

            switch (response.code) {
                case 1:
                    messageService.error("找不到活动");
                    break;
                case 2:
                    messageService.error("请检查您的权限");
                    break;
                case 3:
                    messageService.error("导出失败");
                    break;
                case 4:
                    messageService.error("没有可导出的内容");
                    break;
                case 0:
                    break;
                default:
                    messageService.error(`未知错误: ${response.code}`);
            }
        } catch (error: any) {
            messageService.error(error);
        };
    }

    async function file_delete(activity_id: any, account: any) {
        try {
            const data = await todosApiService.deleteHomeworkAdmin(activity_id, account);

            if (data.code == 1) {
                messageService.error("找不到活动");
                return;
            } else if (data.code == 2) {
                messageService.error("请检查您的权限");
                return;
            } else if (data.code == 3) {
                messageService.error("文件不存在");
                return;
            } else if (data.code == 0) {
                // 删除成功
                setShowDeleteFile(false);
                await getMaxStudent();
                await get_homework_list(0);
                messageService.success("删除成功");
            }
        } catch (error: any) {
            messageService.error(error.message || "删除文件时发生错误");
        }
    }



    // 编辑活动时间提交
    async function time_edit_submit(values: any) {
        try {
            let start_time: string;
            let stop_time: string;

            if (!isMobile) {
                start_time = dayjs(values.time[0]).format('YYYY-MM-DD HH:mm:ss');
                stop_time = dayjs(values.time[1]).format('YYYY-MM-DD HH:mm:ss');
            } else {
                start_time = mobileDateValue1;
                stop_time = mobileDateValue2;
            }

            // 调用 API 服务
            const response = await activityApiService.editActivityTime(
                values.activity_id,
                start_time,
                stop_time
            );

            // 处理响应
            if (response.code === 2) {
                messageService.error("请检查您的权限");
            } else if (response.code === 0) {
                messageService.info("修改成功");
                setLoading(true);
                await get_activity_content(0);
                setLoading(false);
            }

            setOpenEditTime(false);

        } catch (error: any) {
            messageService.error(error?.message || "修改失败");
            setOpenEditTime(false);
        }
    }

    // 打开作业提交
    async function fileUpload(account: string) {
        setFileUploadAccount(account);

        try {
            const response = await todosApiService.openHomework(homeworkActivityID);

            if (response.code == 1 || response.code == 3) {
                messageService.error("提交失败: 活动不存在");
                return;
            } else if (response.code == 2) {
                messageService.error("提交失败: 您没有权限");
                return;
            }

            const parsedData = response.data;

            form1.setFieldsValue({
                activity_id: parsedData.activity_id,
            });

            const homework_arr: HomeWorks = {
                activity_id: parsedData.activity_id,
                name: parsedData.name,
                requestment: Base64.decode(parsedData.requestment),
                files: parsedData.files,
                accept: parsedData.accept
            };

            setHomeworks(homework_arr);
            setShowFileUpload(true);
        } catch (error: any) {
            messageService.error(error.message || "打开作业失败");
        }
    }

    async function homeworkSubmit(values: any) {
        setButtonLoading(true);

        try {
            const formData = new FormData();

            // 检查文件
            if (!values.files || !values.files[0]) {
                messageService.info("请上传文件");
                setButtonLoading(false);
                return;
            }

            const file = values.files[0];
            const fileExtension = file.name.substring(file.name.lastIndexOf(".")).toLowerCase();
            const extensions: string[] = homeworks!.accept.split(",");

            // 检查文件格式
            if (!extensions.includes(fileExtension)) {
                messageService.error("文件格式错误");
                setButtonLoading(false);
                return;
            }


            // 检查文件大小
            if (file.size > FileMaxUplaodSize) {
                messageService.error(`文件体积过大 最大支持${FileMaxUplaodSize / 1024 / 1024}MB`);
                setButtonLoading(false);
                return;
            }

            const convertedFile = new File([file.originFileObj], file.name);
            formData.append("file", convertedFile, convertedFile.name);

            formData.append("activity_id", homeworkActivityID);
            formData.append("target_account", fileUploadAccount);

            const response = await todosApiService.submitHomeworkAdmin(formData);

            if (response.code == 0) {
                setButtonLoading(false);
                setShowFileUpload(false);
                await getMaxStudent();
                await get_homework_list(0);
                messageService.success("提交成功");
            } else if (response.code == 1) {
                messageService.error("提交失败: 活动不存在");
            } else if (response.code == 4) {
                messageService.error(`提交失败: 文件过大,最大支持${FileMaxUplaodSize / 1024 / 1024}MB`);
            }
        } catch (error: any) {
            messageService.error("提交失败: " + (error.message || "未知错误"));
            setButtonLoading(false);
            setShowFileUpload(false);
        }
        form2.resetFields();
    }

    async function downloadPublish(activity_id: string, file_name: string) {

        try {
            const data = await todosApiService.downloadPublish(activity_id, file_name);

            if (data.code == 2) {
                messageService.error("下载失败: 文件不存在");
            }

        } catch (error: any) {
            messageService.error(error.message);
        }
    }

    useEffect(() => {
        setTimeout(async () => {
            queryWord = "";
            queryWord2 = "";
            await get_class_list();
            await getMaxActivity();
            await get_activity_content(0);
        }, 0);
        setLoading(false);
    }, []);


    return (
        <>
            {!error && showHomework &&
                <>
                    <Button type="primary" onClick={() => {
                        getMaxActivity();
                        setTableParams({
                            ...tableParams,
                            pagination: {
                                ...tableParams.pagination,
                                current: tableParams.pagination!.current,
                            }
                        });
                        get_activity_content((tableParams.pagination!.current! - 1) * tableParams.pagination!.pageSize!);

                        setShowHomework(false);
                    }}>
                        返回
                    </Button>
                    <ProTable<HomeworkTableListItem>
                        columns={homeworkColumns}
                        dataSource={homeworkDataSource}
                        headerTitle={<b>{activityShowName}</b>}
                        pagination={tableParams2.pagination}
                        onChange={handleTableChange2}
                        options={{
                            reload: ((_e: React.MouseEvent<HTMLSpanElement>, __action?: ActionType) => refreshForm2())
                        }}
                        toolbar={{
                            search: {
                                onSearch: async (value: string) => {
                                    queryWord2 = value;
                                    await getMaxStudent();
                                    await get_homework_list(0);
                                },
                                placeholder: "请输入学生名称"
                            },
                            actions: [],
                        }}
                        rowKey="key"
                        search={false}
                    />
                </>
            }

            {!error && !showHomework && (
                <ProTable<TableListItem>
                    columns={columns}
                    dataSource={dataSource}
                    pagination={tableParams.pagination}
                    onChange={handleTableChange}
                    options={{
                        reload: ((_e: React.MouseEvent<HTMLSpanElement>, _action?: ActionType) => refreshForm1())
                    }}
                    toolbar={{
                        search: {
                            onSearch: async (value: string) => {
                                queryWord = value;
                                await getMaxActivity();
                                await get_activity_content(0);
                            },
                            placeholder: "请输入名称"
                        },
                        title: [
                            <div key="class-select-form">
                                <Form
                                    id="ClassListForm"
                                    form={ClassListForm}
                                    size={"middle"}
                                    style={{ height: "32px" }}
                                >
                                    <Form.Item
                                        name="class_id"
                                        initialValue={defaultClassList}
                                    >
                                        <Select
                                            placeholder="请选择班级"
                                            options={classList!}
                                            onChange={TableClassSet}
                                            showSearch
                                            filterOption={(input, option) => (option?.label ?? '').includes(input)}
                                            filterSort={(optionA, optionB) =>
                                                (optionA?.label ?? '').toLowerCase().localeCompare((optionB?.label ?? '').toLowerCase())
                                            }
                                        />
                                    </Form.Item>
                                </Form>
                            </div>
                        ],
                        actions: [],
                    }}
                    rowKey="key"
                    search={false}
                />
            )}

            <Modal title="删除活动" open={showDeleteActivity} onOk={() => { setShowConfirmDeleteActivity(true) }} onCancel={() => { setShowDeleteActivity(false) }} destroyOnHidden okType="danger" okText="确定">
                <p>您确定要删除<b>{deleteActivityName}</b>吗？这将永久删除该条目及所有相关数据及文件，并且不可恢复！</p>
            </Modal>

            <Modal title="删除活动" open={showConfirmDeleteActivity} onOk={confirmDelete} onCancel={() => { setShowConfirmDeleteActivity(false) }} destroyOnHidden okType="danger" okText="再次确定">
                <p>您真的确认要删除<b>{deleteActivityName}</b>吗？</p>
            </Modal>

            <Modal title="删除提交" open={showDeleteFile} onOk={() => { file_delete(homeworkActivityID, deleteFileAccount) }} onCancel={() => { setShowDeleteFile(false) }} destroyOnHidden okType="danger" okText="确定">
                <p>您确定要删除<b>{deleteFileAccountName}</b>提交的<b>{activityShowName}</b>吗？</p>
            </Modal>


            <Modal
                title="编辑时间"
                open={openEditTime}
                onOk={() => { form1.submit() }}
                onCancel={() => { setOpenEditTime(false) }}
            >

                <Form {...formItemLayout} form={form1} onFinish={time_edit_submit} style={{ maxWidth: 600 }} >
                    {<Form.Item label="活动ID" name="activity_id" >
                        <Input disabled />
                    </Form.Item>}
                    <Form.Item label="活动名称" name="name">
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

            <Modal
                open={showConfirmExport}
                title="导出活动"
                onOk={() => { }}
                onCancel={() => { setShowConfirmExport(false); }}
                footer={(_, { }) => (
                    <>
                        <Button key="re-export-btn" onClick={() => { activity_output(exportActivityId, exportActivityName, "third"); setShowConfirmExport(false); }}>重新导出</Button>
                        <Button key="direct-download-btn" type='primary' onClick={() => { activity_output(exportActivityId, exportActivityName, "second"); setShowConfirmExport(false); }}>直接下载</Button>
                    </>
                )}
            >
                <p>{exportActivityName}存在副本，是否直接下载？</p>
            </Modal>




            <Modal
                title="提交作业"
                open={showFileUpload}
                onOk={() => { form2.submit(); }}
                confirmLoading={buttonLoading}
                onCancel={() => { setShowFileUpload(false); form2.resetFields(); }}

            >
                {homeworks && <div>
                    <h3> {homeworks.name}</h3>
                    {/*<h4>课程: {homeworks.course}</h4>*/}
                    <Typography.Paragraph ellipsis={{ rows, expandable: 'collapsible', expanded, onExpand: (_, info) => setExpanded(info.expanded), }} style={{ whiteSpace: 'pre-wrap' }} >
                        要求: {homeworks.requestment}
                    </Typography.Paragraph>

                    {homeworks.files.map((file, index) => {
                        const fileName = file.substring(file.lastIndexOf('/') + 1);
                        return (
                            <div key={index}>
                                <a key={index} onClick={() => downloadPublish(homeworks.activity_id, fileName)} target="_blank" rel="noopener noreferrer">
                                    {fileName}
                                </a>
                                <br />
                            </div>
                        );
                    })}

                    <br />
                    <div key={Math.random()}>

                        <Form
                            id="form2"
                            form={form2}
                            onFinish={homeworkSubmit}
                            style={{ maxWidth: 600 }}
                            autoComplete="on"
                        >

                            <Form.Item name="files"
                                valuePropName="fileList"
                                getValueFromEvent={normFile}  >
                                <Upload.Dragger
                                    accept={homeworks.accept}
                                    onDrop={(event) => {
                                        event.preventDefault();
                                        event.stopPropagation();
                                    }}
                                    beforeUpload={() => false}
                                    maxCount={1}
                                >
                                    <p className="ant-upload-drag-icon">
                                        <InboxOutlined />
                                    </p>
                                    <p className="ant-upload-text">点击或拖拽作业文件到此处</p>
                                    <p className="ant-upload-hint">
                                        该作业接收 {homeworks.accept} 等文件类型
                                    </p>
                                </Upload.Dragger>
                            </Form.Item>
                        </Form>

                    </div>
                </div>
                }
            </Modal>



        </>
    );
};

export default ActivityContent;
