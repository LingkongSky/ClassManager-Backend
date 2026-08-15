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
    message,
} from 'antd';
import { useEffect, useState } from 'react';
import * as ExcelJS from 'exceljs';
import { InboxOutlined } from '@ant-design/icons';
import { formItemLayout } from '../dashboard.tsx';
import messageService from '../../../Module/messageService.ts';
import { ClassSelectList } from '../../../Interface/class.ts';
import { permissionGroupApi } from '../../../Api/permission.ts';
import { memberApiService } from '../../../Api/memberList.ts';
import { FilterValue, SorterResult } from 'antd/lib/table/interface.js';
import { useCachedState } from '../../../Module/DataCache.ts';


/*
表格分析
批量提交
*/
// 列表结构

type TableListItem = {
    key: number;
    permissions_group_id: string;
    permissions_group_name: string;
    classes: string;
    class_list: string;
};

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

let queryWord: string = "";

const PermissionGroupEdit: React.FC = () => {

    const [dataSource, setDataSource] = useCachedState<TableListItem[]>('table:permission-groups', []);
    // const [searchDataSource, setSearchDataSource] = useState<TableListItem[]>([]);

    const [, setLoading] = useState(true);
    const [error, setError] = useState(false);

    const [open1, setOpen1] = useState(false);
    const [open2, setOpen2] = useState(false);
    const [open3, setOpen3] = useState(false);
    const [confirmLoading1, setConfirmLoading1] = useState(false);

    const [classList, setClassList] = useCachedState<null | ClassSelectList[]>('options:classes', null);

    //const [editPermissionsGroupId, setEditPermissionsGroupId] = useState("");
    const [deletePermissionsGroupId, setDeletePermissionsGroupId] = useState("");
    const [deletePermissionsGroupName, setDeletePermissionsGroupName] = useState("");
    const [showDeletePermissionsGroup, setShowDeletePermissionsGroup] = useState(false);
    const [showDeletePermissionsGroupList, setShowDeletePermissionsGroupList] = useState(false);

    const [selectedRowKeys, setSelectedRowKeys] = useState<React.Key[]>([]);

    const [uploadKey, setUploadKey] = useState(0);
    const [xlxsAnalysisData, setXlxsAnalysisData] = useState<null | DataType[]>(null);

    const [form1] = Form.useForm();
    const [form3] = Form.useForm();

    const [tableParams, setTableParams] = useState<TableParams>({
        pagination: {
            current: 1,
            pageSize: 20,
            total: 20
        },
    });


    // 编辑权限组信息
    const permissions_group_edit = (permissions_group_id: any, permissions_group_name: any, class_list: any) => {

        // setEditPermissionsGroupId(permissions_group_id);

        form3.setFieldsValue({
            permissions_group_id: permissions_group_id,
            permissions_group_name: permissions_group_name,
            class_list: class_list.split("|")
        });

        setOpen3(true);
    }

    // 删除权限组
    const permissions_group_delete = (permissions_group_id: any, permissions_group_name: any) => {
        setDeletePermissionsGroupId(permissions_group_id);
        setDeletePermissionsGroupName(permissions_group_name);
        setShowDeletePermissionsGroup(true);
    }



    const columns: ProColumns<TableListItem>[] = [
        {
            title: '权限组ID',
            dataIndex: 'permissions_group_id',
            key: 'permissions_group_id',
            render: (_) => <a>{_}</a>,
        },
        {
            title: '权限组名称',
            dataIndex: 'permissions_group_name',
            key: 'permissions_group_name',
        },
        {
            title: '管理班级ID',
            dataIndex: 'class_list',
            key: 'class_list',
            // hideInTable: true
            hidden: true
        },
        {
            title: '管理班级',
            dataIndex: 'classes',
            key: 'classes',
            render: (text) => text == " " ? "-" : text,
        },
        {
            title: '操作',
            key: 'option',
            valueType: 'option',
            render: (_, entity) => [
                <a style={{ whiteSpace: "nowrap" }} onClick={() => permissions_group_edit(entity.permissions_group_id, entity.permissions_group_name, entity.class_list)} key="edit">编辑</a>,
                <a style={{ whiteSpace: "nowrap" }} onClick={() => permissions_group_delete(entity.permissions_group_id, entity.permissions_group_name)} key="delete">删除</a>,
            ],
        },
    ];

    // 获取班级列表
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
            messageService.error('获取班级列表失败: ' + error.message);
        }
    }

    // 提交单个权限组信息
    async function formSubmit1(values: any) {
        setConfirmLoading1(true);

        try {
            const class_list = values.class_list.length > 0
                ? values.class_list.join("|")
                : "";

            const param = [{
                name: values.permissions_group_name,
                target: class_list
            }];

            await permissionGroupApi.addPermissionsGroup(JSON.stringify(param));
            messageService.info('提交成功!');

            setLoading(true);
            await Promise.all([
                getMaxPermissionGroup(),
                get_permissions_group_list(0)
            ]);
            setLoading(false);
        } catch (error: any) {
            messageService.error('提交失败: ' + (error.message || error));
        } finally {
            setConfirmLoading1(false);
            setOpen1(false);
        }
    }



    // 提交权限组信息编辑
    async function permissions_group_edit_sumbit(values: any) {
        try {
            const classList = values.class_list.join("|");

            await permissionGroupApi.editPermissionsGroup(
                values.permissions_group_id,
                values.permissions_group_name,
                classList
            );

            messageService.info('编辑成功!');
            setLoading(true);
            await get_permissions_group_list(0);
            setLoading(false);
        } catch (error: any) {
            messageService.error('编辑失败: ' + (error.message || error));
        } finally {
            setOpen3(false);
        }
    }



    // 提交权限组删除
    async function permissions_group_delete_submit() {
        try {
            const arr = [deletePermissionsGroupId];

            await permissionGroupApi.deletePermissionsGroup(JSON.stringify(arr));

            messageService.info('删除成功!');
            setLoading(true);
            await Promise.all([
                getMaxPermissionGroup(),
                get_permissions_group_list(0)
            ]);
            setLoading(false);
        } catch (error: any) {
            messageService.error('删除失败: ' + (error.message || error));
        } finally {
            setShowDeletePermissionsGroup(false);
        }
    }
    function refreshForm1() {
        getMaxPermissionGroup();
        get_permissions_group_list(0);
        messageService.success('刷新成功');
    }

    // 提交权限组批量删除
    async function permissions_group_list_delete_submit() {
        try {
            await permissionGroupApi.deletePermissionsGroup(
                JSON.stringify(selectedRowKeys)
            );

            messageService.info('删除成功!');
            setLoading(true);
            await Promise.all([
                getMaxPermissionGroup(),
                get_permissions_group_list(0)
            ]);
            setLoading(false);
        } catch (error: any) {
            messageService.error('删除失败: ' + (error.message || error));
        } finally {
            setShowDeletePermissionsGroupList(false);
            setSelectedRowKeys([]);
        }
    }

    type DataType = {
        name: string;
        target: string;
    };


    // 提交权限组批量添加
    function xlxsAnalysis(file: any) {
        if (file.type !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            message.error('只能上传.xlsx文件');
            return;
        }

        const workbook = new ExcelJS.Workbook();

        const reader = new FileReader();
        reader.onload = () => {
            /*
                        const arrayBuffer = reader.result as ArrayBuffer;
                        const data = new Uint8Array(arrayBuffer);
                        workbook.xlsx.load(data)
            */
            const arrayBuffer = reader.result as ArrayBuffer;
            workbook.xlsx.load(arrayBuffer)
                .then(() => {
                    const worksheet = workbook.getWorksheet('Sheet1');
                    var columnsData: Array<DataType> = [];
                    const columnNames = ['名称', '管理班级'];

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
                                name: '',
                                target: ''
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
                                if (columnName == columnNames[0]) rowData['name'] = texts;
                                if (columnName == columnNames[1]) rowData['target'] = texts;
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



    // 提交表单解析数据
    async function addListSumbit() {
        if (xlxsAnalysisData == null || xlxsAnalysisData.length < 1) {
            messageService.info('暂无可提交的内容');
            return;
        }

        try {
            await permissionGroupApi.addPermissionsGroupList(
                JSON.stringify(xlxsAnalysisData)
            );

            messageService.info('提交成功!');
            setLoading(true);
            await Promise.all([
                getMaxPermissionGroup(),
                get_permissions_group_list(0)
            ]);
            setLoading(false);
        } catch (error: any) {
            messageService.error('提交失败: ' + (error.message || error));
            setError(true);
        } finally {
            setOpen2(false);
            setXlxsAnalysisData(null);
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

        if (!(Number(dataSource[(pagination.current! - 1) * 20]?.permissions_group_id) >= 1)) {      // 通过取值判断当前页是否有数据，没有则请求
            get_permissions_group_list((pagination.current! - 1) * 20);
        }
    }

    // 获取活动总数并进行表格数组填充初始化
    const getMaxPermissionGroup = async () => {
        try {
            const permissionGroupCount = await permissionGroupApi.getPermissionsGroupCount(queryWord);

            const count = Number(permissionGroupCount.data);
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
                    current[index]?.permissions_group_id ? current[index] : {
                        key: Math.random(),
                        permissions_group_name: "",
                        permissions_group_id: "",
                        classes: "",
                        class_list: "",
                    });
                searchDataSource = tableData.slice();
                return tableData;
            });
        } catch (error: any) {
            messageService.error(error.message || error);
            setError(true);
        }
    }


    // 请求表格内容
    async function get_permissions_group_list(query_count: number) {
        try {
            const data = await permissionGroupApi.getPermissionsGroupList(query_count, queryWord);

            const parsedData = data.data;
            const tableData: TableListItem[] = parsedData.map((item: any) => ({
                key: item.group_id,
                permissions_group_name: item.name,
                permissions_group_id: item.group_id,
                classes: item.class_name_list,
                class_list: item.class_id_list
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
            messageService.error('获取权限组列表失败: ' + (error.message || error));
        }
    }

    useEffect(() => {
        setTimeout(async () => {
            queryWord = "";
            await getMaxPermissionGroup();
            await get_permissions_group_list(0);
            await get_class_list();
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
                    reload: ((_e: React.MouseEvent<HTMLSpanElement>, _action?: ActionType) => refreshForm1())
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
                            <a onClick={() => { setSelectedRowKeys(selectedRowKeys); setShowDeletePermissionsGroupList(true); }}>批量删除</a>
                        </Space>
                    );
                }}
                toolbar={{
                    search: {
                        onSearch: (value: string) => {
                            queryWord = value;
                            getMaxPermissionGroup();
                            get_permissions_group_list(0);
                        },
                        placeholder: "请输入权限组名称"
                    },
                    actions: [
                        <Button
                            key="addPermissionsGroup"
                            type="primary"
                            onClick={() => { form1.resetFields(); setOpen1(true) }}
                        >
                            添加权限组
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
                title="添加权限组"
                open={open1}
                onOk={() => { form1.submit() }}
                confirmLoading={confirmLoading1}
                onCancel={() => { setOpen1(false) }}
            >
                <Form form={form1} {...formItemLayout} onFinish={formSubmit1} style={{ maxWidth: 600 }}>
                    <Form.Item label="权限组名称" name="permissions_group_name" rules={[{ required: true, message: '请输入权限组名称' },
                    {
                        message: '请勿输入特殊字符',
                        pattern: /^((?!\+|\.|\\|\/|:|\*|\?|<|>|\|| |'|%|@|#|&|\$|\^|&|\*).){1,99}$/
                    }]}>
                        <Input maxLength={99} />
                    </Form.Item>
                    <Form.Item label="管理班级" name="class_list" rules={[{ required: true, message: '请选择管理班级' }]}>
                        <Select
                            showSearch
                            optionFilterProp="label"
                            mode="tags"
                            options={classList!}
                        />
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
                            “名称”,“管理班级”等列的内容并自动填充。请在管理班级列下填入目标班级的名称，用|隔开。
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

                <Form {...formItemLayout} form={form3} onFinish={permissions_group_edit_sumbit} style={{ maxWidth: 600 }} >
                    {<Form.Item label="权限组ID" name="permissions_group_id" rules={[{ required: true, message: '请输入权限组ID' }]} >
                        <Input disabled />
                    </Form.Item>}
                    <Form.Item label="权限组名称" name="permissions_group_name" rules={[{ required: true, message: '请输入权限组名称' },
                    {
                        message: '请勿输入特殊字符',
                        pattern: /^((?!\+|\.|\\|\/|:|\*|\?|<|>|\|| |'|%|@|#|&|\$|\^|&|\*).){1,40}$/
                    }]}>
                        <Input maxLength={40} />
                    </Form.Item>
                    <Form.Item label="管理班级" name="class_list" rules={[{ required: true, message: '请输入被管理的班级' }]}>
                        <Select
                            showSearch
                            optionFilterProp="label"
                            mode="tags"
                            options={classList!}

                        />
                    </Form.Item>
                </Form>
            </Modal>



            <Modal title="删除权限组" open={showDeletePermissionsGroup} onOk={() => { permissions_group_delete_submit() }} onCancel={() => { setShowDeletePermissionsGroup(false) }} destroyOnHidden okType="danger" okText="确定">
                <p>您确定要删除权限组<b>{deletePermissionsGroupName}</b>吗？</p>
            </Modal>


            <Modal title="删除选中的权限组" open={showDeletePermissionsGroupList} onOk={() => { permissions_group_list_delete_submit() }} onCancel={() => { setShowDeletePermissionsGroupList(false) }} destroyOnHidden okType="danger" okText="确定">
                <p>您确定要删除所有选中的权限组吗？</p>
            </Modal>

        </>
    );
};

export default PermissionGroupEdit;
