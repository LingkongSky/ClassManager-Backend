import type { ActionType, ProColumns } from '@ant-design/pro-components';
import { ProTable } from '@ant-design/pro-components';
import {
    Button,
    Divider,
    Form,
    GetProp,
    Input,
    Modal,
    Tag,
    Select,
    Space,
    Table,
    TableProps,
    Upload,
    UploadProps,
    message,
} from 'antd';
import { useEffect, useState } from 'react';
import { useCachedState } from '../../../Module/DataCache.ts';
type TableListItem = {
    key: number;
    class_name: string;
    class_id: string;
    student_count: string;
};
import * as ExcelJS from 'exceljs';
import { deceteMobile } from '../../../global.ts';
import { InboxOutlined } from '@ant-design/icons';
import { formItemLayout } from '../dashboard.tsx';
import messageService from '../../../Module/messageService.ts';
import { classApiService } from '../../../Api/classList.ts';
import { FilterValue, SorterResult } from 'antd/lib/table/interface.js';


// 列表结构
var searchDataSource: TableListItem[] = [];
var oldOrder: String = "";


type TablePaginationConfig = Exclude<GetProp<TableProps, 'pagination'>, boolean>;
interface TableParams {
    pagination?: TablePaginationConfig;
    sortField?: string;
    sortOrder?: string;
    filters?: Parameters<GetProp<TableProps, 'onChange'>>[1];
}

const props: UploadProps = {
    progress: {
        showInfo: false,
        trailColor: "#108ee9",
        strokeWidth: 3,
        format: () => `${parseFloat('100')}%`,
    },
};


type DataType = {
    class_name: string;
};

type ClassOption = {
    value: string;
    label: string;
};

type QuickStudentItem = {
    key: string;
    account: string;
    name: string;
    class_names?: string[];
    primary_class_name?: string;
};
type Sorts = SorterResult<any>
let queryWord: string = "";


const ClassList: React.FC = () => {

    const [dataSource, setDataSource] = useCachedState<TableListItem[]>('table:classes', []);
    // const [searchDataSource, setSearchDataSource] = useState<TableListItem[]>([]);

    const [, setLoading] = useState(true);
    const [error, setError] = useState(false);
    const [open1, setOpen1] = useState(false);
    const [open2, setOpen2] = useState(false);
    const [open3, setOpen3] = useState(false);
    const [openQuickBuild, setOpenQuickBuild] = useState(false);
    const [confirmLoading1, setConfirmLoading1] = useState(false);
    const [quickLoading, setQuickLoading] = useState(false);
    const [quickStudentsLoading, setQuickStudentsLoading] = useState(false);

    const [editClassId, setEditClassId] = useState("");
    const [deleteClassId, setDeleteClassId] = useState("");
    const [deleteClassName, setDeleteClassName] = useState("");
    const [showDeleteClass, setShowDeleteClass] = useState(false);
    const [showDeleteClassList, setShowDeleteClassList] = useState(false);
    const [selectedRowKeys, setSelectedRowKeys] = useState<React.Key[]>([]);

    const [isMobile, _setIsMobile] = useState(deceteMobile());

    const [uploadKey, setUploadKey] = useState(0);
    const [xlxsAnalysisData, setXlxsAnalysisData] = useState<null | DataType[]>(null);
    const [allClasses, setAllClasses] = useState<ClassOption[]>([]);
    const [quickStudents, setQuickStudents] = useState<QuickStudentItem[]>([]);
    const [quickSelectedAccounts, setQuickSelectedAccounts] = useState<React.Key[]>([]);

    const [form1] = Form.useForm();
    const [form3] = Form.useForm();
    const [quickForm] = Form.useForm();

    const [tableParams, setTableParams] = useState<TableParams>({
        pagination: {
            current: 1,
            pageSize: 20,
            total: 20
        },
    });


    // 编辑班级信息
    const class_edit = (class_id: any, class_name: any) => {

        setEditClassId(class_id);

        form3.setFieldsValue({
            class_id: class_id,
            class_name: class_name,
        });

        setOpen3(true);
    }

    // 删除班级
    const class_delete = (class_id: any, class_name: any) => {
        setDeleteClassId(class_id);
        setDeleteClassName(class_name);
        setShowDeleteClass(true);
    }

    // 导出班级学生信息
    const class_output = (_class_id: any, _class_name: any) => {
        alert("开发中...");
    }


    const columns: ProColumns<TableListItem>[] = [
        {
            title: '班级',
            dataIndex: 'class_name',
            key: 'class_name',
            render: (_) => <a>{_}</a>,
            sorter: true
        },
        {
            title: 'ID',
            dataIndex: 'class_id',
            key: 'class_id',
            hidden: isMobile,
            sorter: true
        },
        {
            title: '人数',
            dataIndex: 'student_count',
            key: 'student_count',
            sorter: true
        },
        {
            title: '操作',
            key: 'option',
            valueType: 'option',
            render: (_, entity) => [
                <a style={{ whiteSpace: "nowrap" }} onClick={() => class_output(entity.class_id, entity.class_name)} key="export">导出</a>,
                <a style={{ whiteSpace: "nowrap" }} onClick={() => class_edit(entity.class_id, entity.class_name)} key="edit">编辑</a>,
                <a style={{ whiteSpace: "nowrap" }} onClick={() => class_delete(entity.class_id, entity.class_name)} key="delete">删除</a>,
            ],
        },
    ];

    async function getSortData(query_count: number, sortType: Sorts) {

        var sortMode: string = "asc";
        //console.log(sortType);

        if (sortType.order == "ascend") {
            sortMode = "asc";
        } else if (sortType.order == "descend") {
            sortMode = "desc";
        } else {
            return;
        }

        try {
            const data = await classApiService.getSortClassContent(query_count, queryWord, sortMode, sortType.field?.toString());

            const parsedData = data.data;
            const tableData: TableListItem[] = parsedData.map((item: any) => ({
                key: item.class_id,
                class_name: item.class_name,
                class_id: item.class_id,
                student_count: item.student_count
            }));

            var dataLength = tableData.length;
            var tempData: TableListItem[] = searchDataSource.slice();
            for (var i = 0; i < dataLength; i++) {
                tempData[i + query_count] = tableData[i];
            }

            setDataSource(tempData.slice());
            searchDataSource = tempData.slice();

        } catch (error) {
            setError(true);
        }
    }


    // 提交添加班级
    async function formSubmit1(values: any) {
        setConfirmLoading1(true);

        try {
            const param = [{
                class_name: values.class_name,
            }];

            await classApiService.addClass(param);

            messageService.info('提交成功!');

            setConfirmLoading1(false);
            setOpen1(false);
            setLoading(true);
            await getMaxClass();
            await get_class_list(0);
            setLoading(false);
        } catch (error: any) {
            messageService.error(error);
            setConfirmLoading1(false);
            setOpen1(false);
        }
    }


    // 提交班级信息编辑
    async function class_edit_submit(values: any) {
        try {
            await classApiService.editClass(editClassId, values.class_name);
            messageService.info('编辑成功!');
            setLoading(true);
            await get_class_list(0);
            setLoading(false);
        } catch (error: any) {
            messageService.error(error);
        }

        setOpen3(false);
    }

    async function refreshForm1() {
        await getMaxClass();
        await get_class_list(0);
        messageService.success('刷新成功');
    }

    // 提交班级删除
    async function class_delete_submit() {
        const arr: Array<string> = [deleteClassId];

        try {
            await classApiService.deleteClass(arr);

            messageService.info('删除成功!');
            setLoading(true);
            await getMaxClass();
            await get_class_list(0);
            setLoading(false);
        } catch (error: any) {
            messageService.error(error);
        }

        setShowDeleteClass(false);
    }


    // 处理缓存部分 防止重复加载
    const handleTableChange: TableProps<TableListItem>['onChange'] = async(
        pagination: TablePaginationConfig,
        filters: Record<string, FilterValue | null>,
        sorter: SorterResult<TableListItem> | SorterResult<TableListItem>[],) => {
        setTableParams({
            pagination,
            filters,
            ...sorter,
        });

        var sort: Sorts = sorter as Sorts;

        // 确保在修改排序时重置表单
        if (sort.order) {

            if (sort.order != oldOrder) {
                oldOrder = sort.order;
                await getMaxClass();
                await getSortData(0, sort);
            } else {
                if (!(Number(dataSource[(pagination.current! - 1) * 20]?.class_id) >= 1)) // 使用缓存
                    await getSortData((pagination.current! - 1) * 20, sort);
            }

            return;
        }

        // 在切换排序时重置数据
        if (oldOrder != "") {
            oldOrder = "";
            await getMaxClass();
            await get_class_list(0);
            return;
        }


        if (!(Number(dataSource[(pagination.current! - 1) * 20]?.class_id) >= 1)) {      // 通过取值判断当前页是否有数据，没有则请求
            await get_class_list((pagination.current! - 1) * 20);
        }

    }

    // 提交班级批量删除
    async function class_list_delete_submit() {
        try {
            await classApiService.deleteClass(selectedRowKeys as string[]);
            messageService.info('删除成功!');
            setLoading(true);
            await getMaxClass();
            await get_class_list(0);
            setLoading(false);

        } catch (error: any) {
            messageService.error(error);
        }

        setShowDeleteClassList(false);
        setSelectedRowKeys([]);
    }



    // 获取活动总数并进行表格数组填充初始化
    const getMaxClass = async () => {
        try {
            const data = await classApiService.getClassCount(queryWord);
            const count = Number(data.data);

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

            // 保留已缓存的真实行，网络列表返回后再覆盖；只补齐尚未加载的占位行。
            setDataSource((current) => {
                const tableData: TableListItem[] = Array.from({ length: count }, (_, index) =>
                    current[index]?.class_id ? current[index] : {
                        key: Math.random(),
                        class_name: "",
                        class_id: "",
                        student_count: ""
                    });
                searchDataSource = tableData.slice();
                return tableData;
            });
        } catch (error: any) {
            messageService.error(error.message);
            setError(true);
        }
    }


    // 提交班级批量添加
    function xlxsAnalysis(file: any) {
        if (file.type !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            message.error('只能上传.xlsx文件');
            return;
        }

        const workbook = new ExcelJS.Workbook();

        const reader = new FileReader();
        reader.onload = () => {
            const arrayBuffer = reader.result as ArrayBuffer;
            workbook.xlsx.load(arrayBuffer)
                .then(() => {
                    const worksheet = workbook.getWorksheet('Sheet1');
                    var columnsData: Array<DataType> = [];
                    const columnNames = ['班级'];

                    if (!worksheet) {
                        messageService.info('提交失败!');
                        throw new DOMException();
                    }
                    // 按行遍历 读取指定名称列的内容，并存入数组中
                    worksheet.eachRow((row, rowNumber) => {
                        if (rowNumber === 1) {
                            // 检查第一行的列名称是否匹配
                            var i = 0;
                            row.eachCell((cell) => {
                                if (columnNames.includes(cell.text)) {
                                    i++;
                                }
                            });

                            if (i < 1) {
                                messageService.info('列名不匹配！');
                                throw new DOMException();
                            }
                        } else {
                            // 从第二行开始读取数据
                            var rowData: DataType = {
                                class_name: '',
                            };
                            row.eachCell((cell, colNumber) => {
                                const columnName = worksheet.getCell(1, colNumber).text;
                                if (!columnNames.includes(columnName)) return;

                                var texts: string = cell.text;
                                texts = texts.split('\\').join('');
                                texts = texts.split('\n').join('');
                                texts = texts.split('\r').join('');
                                texts = texts.split('\t').join('');

                                //alert(columnName + " " + cell.text);
                                if (columnName == columnNames[0]) rowData['class_name'] = texts;
                            });
                            columnsData.push(rowData);
                        }
                    });
                    setXlxsAnalysisData(columnsData);
                    messageService.info('解析成功! ');
                })
                .catch((error) => {
                    messageService.info('解析失败! ' + error);
                    setXlxsAnalysisData(null);
                });
        };
        reader.readAsArrayBuffer(file);
    }



    // 提交表单解析数据 - 批量添加班级
    async function addListSumbit() {
        if (xlxsAnalysisData == null || xlxsAnalysisData.length < 1) {
            messageService.info('暂无可提交的内容');
            return;
        }

        try {
            await classApiService.addClass(xlxsAnalysisData);
            messageService.info('提交成功!');
            setLoading(true);
            await getMaxClass();
            await get_class_list(0);
            setLoading(false);
        } catch (error) {
            messageService.error('提交失败!');
            setError(true);
        }

        setOpen2(false);
        setXlxsAnalysisData(null);
    }

    async function get_class_list(query_count: number) {
        try {
            const data = await classApiService.getClassContent(query_count, queryWord);

            const parsedData = data.data;
            const tableData: TableListItem[] = parsedData.map((item: any) => ({
                key: item.class_id,
                class_name: item.class_name,
                class_id: item.class_id,
                student_count: item.student_count
            }));

            const dataLength = tableData.length;
            const tempData: TableListItem[] = searchDataSource.slice();
            for (let i = 0; i < dataLength; i++) {
                tempData[i + query_count] = tableData[i];
            }

            setDataSource(tempData.slice());
            searchDataSource = tempData.slice();
        } catch (error: any) {
            messageService.error(error);
        }
    }

    const renderClassNames = (record: QuickStudentItem) => {
        const classNames = record.class_names ?? [];
        const primaryClassName = record.primary_class_name ?? classNames[0];
        const ordered = primaryClassName
            ? [primaryClassName, ...classNames.filter((name) => name !== primaryClassName)]
            : classNames;
        return ordered.map((name, idx) => (
            <span key={`${record.account}-${name}-${idx}`} style={name === primaryClassName ? { textDecoration: 'underline' } : undefined}>
                {idx > 0 ? ' / ' : ''}
                {name}
            </span>
        ));
    };

    async function openQuickBuildModal() {
        try {
            const data = await classApiService.getAllClasses();
            const options: ClassOption[] = (data.data ?? []).map((item: any) => ({
                value: item.class_id,
                label: item.class_name
            }));
            setAllClasses(options);
            setQuickStudents([]);
            setQuickSelectedAccounts([]);
            quickForm.resetFields();
            setOpenQuickBuild(true);
        } catch (error: any) {
            messageService.error(error?.message || '获取班级列表失败');
        }
    }

    async function loadQuickStudents() {
        const sourceClassIds = quickForm.getFieldValue('source_class_ids') ?? [];
        const queryWordQuick = quickForm.getFieldValue('query_word') ?? '';
        if (!Array.isArray(sourceClassIds) || sourceClassIds.length === 0) {
            setQuickStudents([]);
            setQuickSelectedAccounts([]);
            return;
        }

        setQuickStudentsLoading(true);
        try {
            const data = await classApiService.getStudentsByClasses(sourceClassIds, queryWordQuick);
            const rows: QuickStudentItem[] = (data.data ?? []).map((item: any) => ({
                key: item.account,
                account: item.account,
                name: item.name,
                class_names: item.class_names,
                primary_class_name: item.primary_class_name,
            }));
            setQuickStudents(rows);
            setQuickSelectedAccounts([]);
        } catch (error: any) {
            messageService.error(error?.message || '加载学生失败');
        } finally {
            setQuickStudentsLoading(false);
        }
    }

    async function quickBuildSubmit() {
        const targetClassId = quickForm.getFieldValue('target_class_id');
        if (!targetClassId) {
            messageService.info('请选择目标班级');
            return;
        }
        if (quickSelectedAccounts.length === 0) {
            messageService.info('请至少选择一个学生');
            return;
        }

        setQuickLoading(true);
        try {
            await classApiService.appendStudents(targetClassId, quickSelectedAccounts as string[]);
            messageService.success('快速建班成功：已追加为副班级');
            setOpenQuickBuild(false);
            await getMaxClass();
            await get_class_list(0);
        } catch (error: any) {
            messageService.error(error?.message || '快速建班失败');
        } finally {
            setQuickLoading(false);
        }
    }


    useEffect(() => {
        setTimeout(async() => {
            queryWord = "";
            await getMaxClass();
            await get_class_list(0);
            setLoading(false);
        }, 0);
    }, []);

    return (
        <>
            {!error && <ProTable<TableListItem>
                columns={columns}
                dataSource={dataSource}
                pagination={tableParams.pagination}
                onChange={handleTableChange}
                options={{
                    reload: ((_e: React.MouseEvent<HTMLSpanElement>, __action?: ActionType) => refreshForm1())
                }}
                rowSelection={{
                    selectedRowKeys,
                    //selections: [Table.SELECTION_ALL],
                    onSelect: (record, selected) => {
                        let arr = []
                        if (selected) {
                            arr = Array.from(new Set([...selectedRowKeys, record.key]))
                        } else {
                            arr = selectedRowKeys.filter(item => {
                                return item !== record.key
                            })
                        }
                        setSelectedRowKeys(arr)
                    },
                    onChange: (selectedRowKeys) => {
                        setSelectedRowKeys(selectedRowKeys)
                    }
                }}
                tableAlertOptionRender={({ selectedRowKeys }) => {
                    return (
                        <Space size={16}>
                            <a onClick={() => { setSelectedRowKeys(selectedRowKeys); setShowDeleteClassList(true); }}>批量删除</a>
                        </Space>
                    );
                }}
                toolbar={{
                    search: {
                        onSearch: async (value: string) => {
                            queryWord = value;
                            await getMaxClass();
                            await get_class_list(0);
                            /*
                            const filteredData = dataSource.filter(item =>
                                item.name.toLowerCase().includes(value.toLowerCase())
                            );*/
                            //setSearchDataSource(filteredData);
                        },
                        placeholder: "请输入班级名"
                    },
                    actions: [
                        <Button
                            key="addClass"
                            type="primary"
                            onClick={() => { form1.resetFields(); setOpen1(true) }}
                        >
                            添加班级
                        </Button>,
                        <Button
                            key="addList"
                            type="primary"
                            onClick={() => { setUploadKey(uploadKey + 1); setOpen2(true) }}
                        >
                            批量添加
                        </Button>,
                        <Button
                            key="quickBuild"
                            type="primary"
                            onClick={openQuickBuildModal}
                        >
                            快速建班
                        </Button>,
                    ],
                }}
                rowKey="key"
                search={false}
            />}

            <Modal
                title="添加班级"
                open={open1}
                onOk={() => { form1.submit() }}
                confirmLoading={confirmLoading1}
                onCancel={() => { setOpen1(false) }}
            >
                <Form form={form1} {...formItemLayout} onFinish={formSubmit1} style={{ maxWidth: 600 }}>
                    {/*<Form.Item label="班级ID" name="class_id" rules={[{ required: true, message: '请输入班级ID' }]}>
                        <Input />
            </Form.Item>*/}
                    <Form.Item label="班级名称" name="class_name" rules={[{ required: true, message: '请输入班级名称' },
                    {
                        message: '请勿输入特殊字符',
                        pattern: /^((?!\+|\.|\\|\/|:|\*|\?|<|>|\|| |'|%|@|#|&|\$|\^|&|\*).){1,20}$/
                    }]}>
                        <Input maxLength={20} />
                    </Form.Item>
                </Form>
            </Modal>

            <Modal
                title="批量添加"
                open={open2}
                confirmLoading={false}
                onCancel={() => { setOpen2(false); setXlxsAnalysisData(null); }}
                onOk={addListSumbit}
            >
                <div key={uploadKey}>
                    <Upload.Dragger {...props} onChange={({ file }) => { xlxsAnalysis(file) }} accept=".xlsx" onDrop={(event) => {
                        event.preventDefault();
                        event.stopPropagation();
                    }}
                        beforeUpload={() => false}
                        maxCount={1}
                    >
                        <p className="ant-upload-drag-icon">
                            <InboxOutlined />
                        </p>
                        <p className="ant-upload-text">点击或拖拽.xlxs文件到此处</p>
                        <p className="ant-upload-hint">
                            请将xlxs文件拖入网页，网页会自动识别excel文件中
                            “班级”等列的内容并自动填充。
                        </p>
                    </Upload.Dragger>
                </div>
            </Modal>

            <Modal
                title="编辑信息"
                open={open3}
                onOk={() => { form3.submit() }}
                onCancel={() => { setOpen3(false) }}
            >

                <Form {...formItemLayout} form={form3} onFinish={class_edit_submit} style={{ maxWidth: 600 }} >
                    {<Form.Item label="班级ID" name="class_id" rules={[{ required: true, message: '请输入班级ID' }]} >
                        <Input disabled />
                    </Form.Item>}
                    <Form.Item label="班级名称" name="class_name" rules={[{ required: true, message: '请输入班级名称' },
                    {
                        message: '请勿输入特殊字符',
                        pattern: /^((?!\+|\.|\\|\/|:|\*|\?|<|>|\|| |'|%|@|#|&|\$|\^|&|\*).){1,20}$/
                    }]}>
                        <Input maxLength={20} />
                    </Form.Item>
                </Form>
            </Modal>



            <Modal title="删除班级" open={showDeleteClass} onOk={() => { class_delete_submit() }} onCancel={() => { setShowDeleteClass(false) }} destroyOnHidden okType="danger" okText="确定">
                <p>您确定要删除班级<b>{deleteClassName}</b>吗？</p>
            </Modal>


            <Modal title="删除选中的班级" open={showDeleteClassList} onOk={() => { class_list_delete_submit() }} onCancel={() => { setShowDeleteClassList(false) }} destroyOnHidden okType="danger" okText="确定">
                <p>您确定要删除所有选中的班级吗？</p>
            </Modal>

            <Modal
                title="快速建班（追加副班级）"
                open={openQuickBuild}
                onOk={quickBuildSubmit}
                confirmLoading={quickLoading}
                onCancel={() => { setOpenQuickBuild(false) }}
                width={980}
                okText="确认追加副班级"
                cancelText="取消"
            >
                <Form
                    form={quickForm}
                    layout="vertical"
                    onValuesChange={(changed) => {
                        if (changed.source_class_ids) {
                            loadQuickStudents();
                        }
                    }}
                >
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 12 }}>
                        <Form.Item label="来源班级（可多选）" name="source_class_ids" rules={[{ required: true, message: '请选择来源班级' }]}>
                            <Select mode="multiple" options={allClasses} showSearch optionFilterProp="label" placeholder="选择要抽取学生的班级" />
                        </Form.Item>
                        <Form.Item label="目标班级（第三班级）" name="target_class_id" rules={[{ required: true, message: '请选择目标班级' }]}>
                            <Select options={allClasses} showSearch optionFilterProp="label" placeholder="选择学生要加入的班级" />
                        </Form.Item>
                        <Form.Item label="学生筛选（姓名前缀）" name="query_word">
                            <Input.Search allowClear placeholder="留空表示不过滤" onSearch={loadQuickStudents} enterButton="筛选" />
                        </Form.Item>
                    </div>
                </Form>

                <div style={{ marginBottom: 10, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <Space wrap>
                        <Tag color="blue">候选学生 {quickStudents.length} 人</Tag>
                        <Tag color="green">已选 {quickSelectedAccounts.length} 人</Tag>
                    </Space>
                    <Space>
                        <Button
                            onClick={() => setQuickSelectedAccounts(quickStudents.map((item) => item.account))}
                            disabled={quickStudents.length === 0}
                        >
                            全选筛选结果
                        </Button>
                        <Button onClick={() => setQuickSelectedAccounts([])}>
                            清空选择
                        </Button>
                    </Space>
                </div>

                <Table<QuickStudentItem>
                    rowKey="account"
                    loading={quickStudentsLoading}
                    dataSource={quickStudents}
                    pagination={{ pageSize: 12, showSizeChanger: true }}
                    rowSelection={{
                        selectedRowKeys: quickSelectedAccounts,
                        onChange: (keys) => setQuickSelectedAccounts(keys),
                    }}
                    columns={[
                        { title: '姓名', dataIndex: 'name', key: 'name' },
                        { title: '学号', dataIndex: 'account', key: 'account' },
                        { title: '当前班级', key: 'class_names', render: (_, record) => renderClassNames(record) },
                    ]}
                    size="small"
                    bordered
                    scroll={{ y: 420 }}
                />
                <Divider style={{ margin: '12px 0' }} />
                <div style={{ color: '#8c8c8c', fontSize: 12 }}>
                    说明：该操作仅为学生追加“副班级”，不会修改学生主班级。
                </div>
            </Modal>




        </>
    );
};

export default ClassList;
