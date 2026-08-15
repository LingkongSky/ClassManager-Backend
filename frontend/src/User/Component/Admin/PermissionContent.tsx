import type { ActionType, ProColumns } from '@ant-design/pro-components';
import { ProTable } from '@ant-design/pro-components';
import {
    Button,
    Form,
    GetProp,
    Input,
    Modal,
    Select,
    Space,
    TableProps,
    Upload,
    UploadProps,
} from 'antd';
import { useEffect, useState } from 'react';
type TableListItem = {
    key: number;
    name: string;
    account: string;
    level: string;
    class_id: string;
    class_name?: string;
    class_ids?: string[];
    class_names?: string[];
    primary_class_id?: string;
    permissions_group_id: string;
    permissions_group_name: string;
};
import * as ExcelJS from 'exceljs';
import { PasswordHash } from '../../../Module/PasswordCrypto.ts';
import { formItemLayout } from '../dashboard.tsx';
import messageService from '../../../Module/messageService.ts';
import { permissionEditApi } from '../../../Api/permission.ts'
import { memberApiService } from '../../../Api/memberList.ts';
import { FilterValue, SorterResult } from 'antd/lib/table/interface.js';
import { EyeInvisibleOutlined, EyeTwoTone, InboxOutlined } from '@ant-design/icons';
import { useCachedState } from '../../../Module/DataCache.ts';


interface PermissionGroupList {
    value: string,
    label: string
}

var searchDataSource: TableListItem[] = [];

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

interface ClassList {
    value: string,
    label: string
}

let queryWord: string = "";

const PermissionContent: React.FC = () => {

    const [dataSource, setDataSource] = useCachedState<TableListItem[]>('table:admins', []);
    const [, setLoading] = useState(true);
    const [error, setError] = useState(false);

    const [open1, setOpen1] = useState(false);
    const [open2, setOpen2] = useState(false);
    const [open3, setOpen3] = useState(false);
    const [open4, setOpen4] = useState(false);
    const [open5, setOpen5] = useState(false);
    const [confirmLoading1, setConfirmLoading1] = useState(false);
    const [confirmLoading2, setConfirmLoading2] = useState(false);
    const [confirmLoading3, setConfirmLoading3] = useState(false);

    const [classList, setClassList] = useCachedState<null | ClassList[]>('options:classes', null);


    const [editAdminId, setEditAdminId] = useState("");
    const [deleteAccount, setDeleteAccount] = useState("");
    const [deleteAccountName, setDeleteAccountName] = useState("");
    const [showDeleteAccount, setShowDeleteAccount] = useState(false);
    const [showDeleteAccountList, setShowDeleteAccountList] = useState(false);

    const [permissionGroupList, setPermissionGroupList] = useState<null | PermissionGroupList[]>(null);

    const [selectedRowKeys, setSelectedRowKeys] = useState<React.Key[]>([]);

    const [uploadKey, setUploadKey] = useState(0);
    const [xlxsAnalysisData, setXlxsAnalysisData] = useState<null | DataType[]>(null);

    const [form1] = Form.useForm();
    const [form2] = Form.useForm();
    const [form3] = Form.useForm();
    const [form4] = Form.useForm();

    const [tableParams, setTableParams] = useState<TableParams>({
        pagination: {
            current: 1,
            pageSize: 20,
            total: 20
        },
    });

    // 填充编辑信息
    const admin_edit = (account: any, permissions_group_id: any, name: any, class_ids: string[] = [], primary_class_id?: string) => {

        setEditAdminId(account);
        setOpen3(true);

        form3.setFieldsValue({
            account: account,
            name: name,
            class_ids: class_ids,
            primary_class_id: primary_class_id ?? class_ids[0],
            permissions_group_id: permissions_group_id,
        });

    }
    // 删除管理员
    const admin_delete = (account: any, name: any) => {
        setDeleteAccountName(name);
        setDeleteAccount(account);
        setShowDeleteAccount(true);
    }


    const columns: ProColumns<TableListItem>[] = [
        {
            title: '学号',
            dataIndex: 'account',
            key: 'account',
            render: (_) => <a>{_}</a>,
        },
        {
            title: '姓名',
            dataIndex: 'name',
            key: 'name',
        },
        {
            title: '权限等级',
            dataIndex: 'level',
            key: 'student_count',
        },
        {
            title: '权限组ID',
            dataIndex: 'permissions_group_id',
            key: 'permissions_group_id',
        },
        {
            title: 'class_id',
            dataIndex: 'class_id',
            key: 'class_id',
            hidden: true,
        },
        {
            title: '权限组名称',
            dataIndex: 'permissions_group_name',
            key: 'permissions_group_name',
        },
        {
            title: '操作',
            key: 'option',
            valueType: 'option',
            render: (_, entity) => [
                <a style={{ whiteSpace: "nowrap" }} onClick={() => admin_edit(entity.account, entity.permissions_group_id, entity.name, entity.class_ids, entity.primary_class_id)} key="edit">编辑</a>,
                <a style={{ whiteSpace: "nowrap" }} onClick={() => admin_password_edit(entity.account, entity.name)} key="editPasswd">修改密码</a>,
                <a style={{ whiteSpace: "nowrap" }} onClick={() => admin_delete(entity.account, entity.name)} key="delete">删除</a>,
            ],
        },
    ];



    // 提交管理员添加表单
    async function formSubmit1(values: any) {
        setConfirmLoading1(true);

        const param = [{
            account: values.account,
            permissions_group_id: values.permissions_group_id
        }];

        try {
            await permissionEditApi.setAdmin(JSON.stringify(param));
            messageService.info('提交成功!');
            setLoading(true);
            await getMaxAdmin();
            await get_admin_list(0);
            setLoading(false);
        } catch (error: any) {
            messageService.error('提交失败: ' + error.message);
        } finally {
            setConfirmLoading1(false);
            setOpen1(false);
        }
    }



    // 提交信息编辑
    async function admin_edit_sumbit(values: any) {
        setConfirmLoading2(true);

        try {
            await permissionEditApi.editAdmin(
                editAdminId,
                values.name,
                values.class_ids ?? [],
                values.primary_class_id ?? '',
                values.permissions_group_id
            );
            messageService.info('编辑成功!');
            setLoading(true);
            await get_admin_list(0);
            setLoading(false);
        } catch (error: any) {
            messageService.error('编辑失败: ' + error.message);
        } finally {
            setConfirmLoading2(false);
            setOpen3(false);
        }
    }

    // 提交权限删除
    async function admin_delete_submit() {
        const arr: Array<string> = [deleteAccount];

        try {
            await permissionEditApi.deleteAdmin(JSON.stringify(arr));
            messageService.info('删除成功!');
            setLoading(true);
            await getMaxAdmin();
            await get_admin_list(0);
            setLoading(false);
        } catch (error: any) {
            messageService.error('删除失败: ' + error.message);
        } finally {
            setShowDeleteAccount(false);
        }
    }


    // 打开管理员密码修改
    const admin_password_edit = (account: any, name: any) => {
        form4.resetFields();
        form4.setFieldsValue({
            account: account,
            name: name,
        });
        setOpen5(true);
    }


    // 提交管理员密码修改
    async function formSubmit4(values: any) {
        setConfirmLoading3(true);

        try {
            await permissionEditApi.editAdminPassword(values.account, PasswordHash(values.password));
            messageService.info('密码修改成功!');
        } catch (error: any) {
            messageService.error('密码修改失败: ' + error.message);
        } finally {
            setOpen5(false);
            setConfirmLoading3(false);
        }
    }

    // 提交权限批量删除
    async function admin_list_delete_submit() {
        try {
            await permissionEditApi.deleteAdmin(JSON.stringify(selectedRowKeys));
            messageService.info('删除成功!');
            setLoading(true);
            await getMaxAdmin();
            await get_admin_list(0);
            setLoading(false);
        } catch (error: any) {
            messageService.error('删除失败: ' + error.message);
        } finally {
            setSelectedRowKeys([]);
            setShowDeleteAccountList(false);
        }
    }


    // 提交学生添加
    async function formSubmit2(values: any) {
        setConfirmLoading2(true);
        const param = [{
            name: values.name,
            account: values.account,
            password: PasswordHash(values.password),
            class_ids: values.class_ids ?? [],
            primary_class_id: values.primary_class_id ?? '',
            permissions_group_id: values.permissions_group_id
        }];

        try {
            await permissionEditApi.addAdminUser(JSON.stringify(param));
            messageService.info('提交成功!');
            setLoading(true);
            await getMaxAdmin();
            await get_admin_list(0);
            setLoading(false);
        } catch (error: any) {
            messageService.error('提交失败: ' + error.message);
        } finally {
            setConfirmLoading2(false);
            setOpen4(false);
        }
    }


    type DataType = {
        account: string;
        permissions_group_id: string;
    };

    // 提交权限批量添加
    function xlxsAnalysis(file: any) {
        if (file.type !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            messageService.error('只能上传.xlsx文件');
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
                    const columnNames = ['学号', '权限组ID'];

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

                            if (i < 2) {
                                messageService.info('列名不匹配！');
                                throw new DOMException();
                            }
                        } else {
                            // 从第二行开始读取数据
                            var rowData: DataType = {
                                account: '',
                                permissions_group_id: ''
                            };
                            row.eachCell((cell, colNumber) => {
                                const columnName = worksheet.getCell(1, colNumber).text;
                                if (!columnNames.includes(columnName)) return;

                                var texts: string = cell.text;
                                texts = texts.split('\\').join('');
                                texts = texts.split('\n').join('');
                                texts = texts.split('\r').join('');
                                texts = texts.split('\t').join('');

                                if (columnName == columnNames[0]) rowData['account'] = texts;
                                if (columnName == columnNames[1]) rowData['permissions_group_id'] = texts;
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

        if (!(Number(dataSource[(pagination.current! - 1) * 20]?.account) >= 1)) {      // 通过取值判断当前页是否有数据，没有则请求
            get_admin_list((pagination.current! - 1) * 20);
        }
    }

    const syncPrimaryClass = (form: any, classIds?: string[]) => {
        if (!Array.isArray(classIds) || classIds.length === 0) return;
        if (classIds.length === 1) {
            form.setFieldValue('primary_class_id', classIds[0]);
            return;
        }
        const currentPrimary = form.getFieldValue('primary_class_id');
        if (!currentPrimary || !classIds.includes(currentPrimary)) {
            form.setFieldValue('primary_class_id', classIds[0]);
        }
    };

    const syncClassIdsForSingleMode = (form: any, primaryClassId?: string) => {
        if (!primaryClassId) return;
        const classIds = form.getFieldValue('class_ids');
        if (!Array.isArray(classIds) || classIds.length <= 1) {
            form.setFieldValue('class_ids', [primaryClassId]);
        }
    };

    // 提交表单解析数据
    async function addListSumbit() {
        if (!xlxsAnalysisData || xlxsAnalysisData.length < 1) {
            messageService.info('暂无可提交的内容');
            return;
        }

        try {
            await permissionEditApi.setAdmin(JSON.stringify(xlxsAnalysisData));
            messageService.info('提交成功!');
            setLoading(true);
            await getMaxAdmin();
            await get_admin_list(0);
            setLoading(false);
        } catch (error: any) {
            messageService.error('提交失败: ' + error.message);
        } finally {
            setOpen2(false);
            setXlxsAnalysisData(null);
        }
    }

    // 获取管理员总数并进行表格数组填充初始化
    const getMaxAdmin = async () => {
        try {
            const adminCount = await permissionEditApi.getAdminCount(queryWord);
            var count = Number(adminCount.data);
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
            setDataSource((current) => {
                const tableData: TableListItem[] = Array.from({ length: count }, (_, index) =>
                    current[index]?.account ? current[index] : {
                        key: Math.random(),
                        account: "",
                        name: "",
                        level: "",
                        class_id: "",
                        class_ids: [],
                        permissions_group_id: "",
                        permissions_group_name: "",
                    });
                searchDataSource = tableData.slice();
                return tableData;
            });
        } catch (error: any) {
            messageService.error(error);
            setError(true);
        }
    }


    async function refreshForm1() {
        await getMaxAdmin();
        await get_admin_list(0);
        messageService.success('刷新成功');
    }

    // 获取管理员数据
    async function get_admin_list(query_count: number) {
        try {
            const data = await permissionEditApi.getAdminList(query_count, queryWord);
            const tableData: TableListItem[] = data.data.map((item: any) => ({
                key: item.account,
                account: item.account,
                name: item.name,
                level: item.level,
                class_id: item.class_id,
                class_name: item.class_name,
                class_ids: item.class_ids,
                class_names: item.class_names,
                primary_class_id: item.primary_class_id,
                permissions_group_id: item.permissions_group_id,
                permissions_group_name: item.permissions_group_name
            }));

            const dataLength = tableData.length;
            const tempData: TableListItem[] = searchDataSource.slice();
            for (let i = 0; i < dataLength; i++) {
                tempData[i + query_count] = tableData[i];
            }

            setDataSource(tempData.slice());
            searchDataSource = tempData.slice();
        } catch (error: any) {
            setError(true);
            messageService.error('获取数据失败: ' + error.message);
        }
    }

    async function get_permissions_group_list() {
        try {
            const respon = await permissionEditApi.getPermissionsGroupList();
            const apiData = respon.data;

            const transformedData: PermissionGroupList[] = apiData.map((item: any) => ({
                value: item.group_id,
                label: item.name
            }));

            setPermissionGroupList(transformedData);
        } catch (error: any) {
            setError(true);
            messageService.error('获取权限组列表失败: ' + error.message);
        }
    }


    // 获取班级列表
    async function get_class_list() {
        try {
            const respon = await memberApiService.getClassList();
            const apiData = respon.data;

            const transformedData: ClassList[] = apiData.map((item: any) => ({
                value: item.class_id,
                label: item.class_name
            }));

            setClassList(transformedData);
        } catch (error: any) {
            messageService.error('获取班级列表失败: ' + error.message);
        }
    }

    useEffect(() => {
        setTimeout(async () => {
            queryWord = "";
            await getMaxAdmin();
            await get_admin_list(0);
            await get_class_list();
            await get_permissions_group_list();
            setLoading(false);
        }, 0);
    }, []);


    return (
        <>
            {!error && <ProTable<TableListItem>
                columns={columns}
                dataSource={dataSource}
                pagination={tableParams.pagination}
                options={{
                    reload: ((_e: React.MouseEvent<HTMLSpanElement>, _action?: ActionType) => refreshForm1())
                }}
                onChange={handleTableChange}
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
                            <a onClick={() => { setSelectedRowKeys(selectedRowKeys); setShowDeleteAccountList(true); }}>批量删除</a>
                        </Space>
                    );
                }}
                toolbar={{
                    search: {
                        onSearch: (value: string) => {
                            queryWord = value;
                            getMaxAdmin();
                            get_admin_list(0);
                            /*
                            const filteredData = dataSource.filter(item =>
                                item.name.toLowerCase().includes(value.toLowerCase())
                            );*/
                            //setSearchDataSource(filteredData);
                        },
                        placeholder: "请输入管理员名"
                    },
                    actions: [
                        <Button
                            key="addStudent"
                            type="primary"
                            onClick={() => { form2.resetFields(); setOpen4(true); }}
                        >
                            添加学生
                        </Button>,
                        <Button
                            key="addAdmin"
                            type="primary"
                            onClick={() => { form1.resetFields(); setOpen1(true) }}
                        >
                            设置管理员
                        </Button>,
                        <Button
                            key="addList"
                            type="primary"
                            onClick={() => { setUploadKey(uploadKey + 1); setOpen2(true) }}
                        >
                            批量添加
                        </Button>,

                    ],
                }}
                rowKey="key"
                search={false}
            />}

            <Modal
                title="设置管理员权限"
                open={open1}
                onOk={() => { form1.submit() }}
                confirmLoading={confirmLoading1}
                onCancel={() => { setOpen1(false) }}
            >
                <Form form={form1} {...formItemLayout} onFinish={formSubmit1} style={{ maxWidth: 600 }}>
                    <Form.Item label="学号(账号)" name="account" rules={[{ required: true, message: '请输入学号(账号)' },
                    {
                        message: '请勿输入特殊字符',
                        pattern: /^((?!\+|\.|\\|\/|:|\*|\?|<|>|\|| |'|%|@|#|&|\$|\^|&|\*).){1,20}$/
                    }]}>
                        <Input maxLength={20} />
                    </Form.Item>
                    <Form.Item label="权限组" name="permissions_group_id" rules={[{ required: true, message: '请选择权限组' }]}>
                        <Select showSearch optionFilterProp="label" options={permissionGroupList!} />
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
                            “学号”，“权限组ID”等列的内容并自动填充。
                        </p>
                    </Upload.Dragger>
                </div>
            </Modal>


            <Modal
                title="添加学生"
                open={open4}
                onOk={() => { form2.submit() }}
                onCancel={() => { setOpen4(false) }}
                confirmLoading={confirmLoading2}
            >

                <Form
                    {...formItemLayout}
                    form={form2}
                    onFinish={formSubmit2}
                    onValuesChange={(changedValues) => {
                        if (changedValues.class_ids) {
                            syncPrimaryClass(form2, changedValues.class_ids);
                        }
                        if (changedValues.primary_class_id) {
                            syncClassIdsForSingleMode(form2, changedValues.primary_class_id);
                        }
                    }}
                    style={{ maxWidth: 600 }}
                >
                    <Form.Item label="学号（账号）" name="account" rules={[{ required: true, message: '请输入学号（账号）' },
                    {
                        message: '请勿输入特殊字符',
                        pattern: /^((?!\+|\.|\\|\/|:|\*|\?|<|>|\|| |'|%|@|#|&|\$|\^|&|\*).){1,20}$/
                    }]}>
                        <Input maxLength={20} />
                    </Form.Item>
                    <Form.Item label="姓名" name="name" rules={[{ required: true, message: '请输入姓名' },
                    {
                        message: '请勿输入特殊字符',
                        pattern: /^((?!\+|\.|\\|\/|:|\*|\?|<|>|\|| |'|%|@|#|&|\$|\^|&|\*).){1,10}$/
                    }]}>
                        <Input maxLength={10} />
                    </Form.Item>
                    <Form.Item label="初始密码" name="password" rules={[{ required: true, message: '请输入初始密码' }]}>
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

                    <Form.Item label="班级" name="class_ids">
                        <Select mode="multiple" options={classList!} showSearch optionFilterProp="label" />
                    </Form.Item>
                    <Form.Item label="主班级" name="primary_class_id">
                        <Select options={classList!} showSearch optionFilterProp="label" />
                    </Form.Item>
                    <Form.Item label="权限组" name="permissions_group_id" rules={[{ required: true, message: '请选择权限组' }]}>
                        <Select showSearch optionFilterProp="label" options={permissionGroupList!} />
                    </Form.Item>
                </Form>
            </Modal>

            <Modal
                title="编辑权限"
                open={open3}
                onOk={() => { form3.submit() }}
                onCancel={() => { setOpen3(false) }}
            >

                <Form
                    {...formItemLayout}
                    form={form3}
                    onFinish={admin_edit_sumbit}
                    onValuesChange={(changedValues) => {
                        if (changedValues.class_ids) {
                            syncPrimaryClass(form3, changedValues.class_ids);
                        }
                        if (changedValues.primary_class_id) {
                            syncClassIdsForSingleMode(form3, changedValues.primary_class_id);
                        }
                    }}
                    style={{ maxWidth: 600 }}
                >
                    {<Form.Item label="账号" name="account" rules={[{ required: true }]} >
                        <Input disabled />
                    </Form.Item>}

                    <Form.Item label="姓名" name="name" rules={[{ required: true, message: '请输入姓名' }]}>
                        <Input maxLength={10} />
                    </Form.Item>

                    <Form.Item label="班级" name="class_ids">
                        <Select mode="multiple" showSearch optionFilterProp="label" options={classList!} />
                    </Form.Item>
                    <Form.Item label="主班级" name="primary_class_id">
                        <Select showSearch optionFilterProp="label" options={classList!} />
                    </Form.Item>

                    <Form.Item label="权限组" name="permissions_group_id" rules={[{ required: true, message: '请选择权限组' }]}>
                        <Select showSearch optionFilterProp="label" options={permissionGroupList!} />
                    </Form.Item>
                </Form>
            </Modal>

            <Modal
                title="修改密码"
                open={open5}
                onOk={() => { form4.submit() }}
                confirmLoading={confirmLoading3}
                onCancel={() => { setOpen5(false) }}
            >
                <Form {...formItemLayout} form={form4} onFinish={formSubmit4} style={{ maxWidth: 600 }} >
                    <Form.Item label="学号（账号）" name="account">
                        <Input disabled />
                    </Form.Item>
                    <Form.Item label="姓名" name="name">
                        <Input disabled />
                    </Form.Item>
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

            <Modal title="删除管理员权限" open={showDeleteAccount} onOk={() => { admin_delete_submit() }} onCancel={() => { setShowDeleteAccount(false) }} destroyOnHidden okType="danger" okText="确定">
                <p>您确定要删除<b>{deleteAccountName}</b>的管理员权限吗？</p>
            </Modal>


            <Modal title="删除选中的管理员权限" open={showDeleteAccountList} onOk={() => { admin_list_delete_submit() }} onCancel={() => { setShowDeleteAccountList(false) }} destroyOnHidden okType="danger" okText="确定">
                <p>您确定要删除所有选中的对象的管理员权限吗？</p>
            </Modal>


        </>
    );
};

export default PermissionContent;
