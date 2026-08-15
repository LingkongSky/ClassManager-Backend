import React, { useEffect, useState } from 'react';
import { Alert, Card, Col, Row, Button, Empty, Modal, Upload, Badge, Typography, Form, Input, Divider, Space } from 'antd';
const { Dragger } = Upload;
// const { Text } = Typography;
import { InboxOutlined } from '@ant-design/icons';
import Skeleton1 from '../../../Module/Skeleton.tsx';
import { FileMaxUplaodSize } from '../../../global.ts';
import { normFile } from '../dashboard.tsx';
import { HomeWorks } from './Todos.tsx';
import messageService from '../../../Module/messageService.ts';
import { todosApiService } from '../../../Api/todos.ts';
import { Base64 } from 'js-base64';
import { useCachedState } from '../../../Module/DataCache.ts';

//请求所在班级的所有（开放）活动，再根据所获取的活动id在文件记录中搜索并修改状态
//将结果拆解并渲染为卡片
// card右上角贴标签表示结果

interface Cards {
    activity_id: string,
    title: string,
    name: string,
    description: string,
    start_time: string,
    deadline: string,
    status: number,
    is_finished: boolean
}



const TodosList: React.FC = () => {

    /*
    去除按钮
    默认显示全部
    请求新接口 
    包括已结束的一律显示
    */
    const [cards, setCards, hasCachedCards] = useCachedState<Cards[]>('cards:started', []);
    const [loading, setLoading] = useState(!hasCachedCards);
    const [buttonLoading, setButtonLoading] = useState(false);
    const [homeworks, setHomeworks] = useState<null | HomeWorks>(null);
    const [error, setError] = useState(false);
    const [none, setNone] = useState(false);
    const [open, setOpen] = useState(false);
    const [rows, _setRows] = useState(2);
    const [expanded, setExpanded] = useState(false);
    const [form1] = Form.useForm();


    const [queryActivityCount, setQueryActivityCount] = useState(0);
    const [maxActivityCount, setMaxActivityCount] = useState(0);
    const [showLoadingMore, setShowLoadingMore] = useState(false);

    const [showDeleteFile, setShowDeleteFile] = useState(false);
    const [deleteActivityName, setDeleteActivityName] = useState("");
    const [deleteActivityId, setDeleteActivityId] = useState("");

    // 获取活动卡片
    async function get_activity(type: number) {
        try {
            const data = await todosApiService.getActivityList(queryActivityCount);

            const parsedData = data.data;
            const cards_arr: Array<Cards> = parsedData.map((item: any) => ({
                activity_id: item.activity_id,
                title: item.title,
                name: item.name,
                start_time: item.start_time,
                description: item.description,
                deadline: item.stop_time,
                is_finished: item.is_finished,
                status: item.status
            }));

            // 排序逻辑
            cards_arr.sort((a: Cards, b: Cards) => {
                if (a.is_finished && !b.is_finished) {
                    return 1;
                } else if (!a.is_finished && b.is_finished) {
                    return -1;
                }
                return 0;
            });

            cards_arr.sort((a: Cards, b: Cards) => {
                if (a.status && !b.status) {
                    return -1;
                } else if (!a.status && b.status) {
                    return 1;
                }
                return 0;
            });

            // 更新状态
            const newQueryCount = queryActivityCount + cards_arr.length;
            setQueryActivityCount(newQueryCount);
            if(type == 0){
                setCards([...cards_arr]);
            }else{
                setCards([...cards, ...cards_arr]);
            }

            // 判断是否显示加载更多按钮
            if (maxActivityCount > newQueryCount) {
                setShowLoadingMore(true);
            } else if (maxActivityCount != 0) {
                setShowLoadingMore(false);
            }
        } catch (error: any) {
            messageService.error(error.message);
            if (!hasCachedCards) setError(true);
        }
    }

    //下载已提交的作业
    async function homeworkGet(activity_id: any) {
        try {
            const data = await todosApiService.downloadHomework(activity_id);

            if (data.code == 1) {
                messageService.error("下载失败: 活动不存在");
            } else if (data.code == 2) {
                messageService.error("下载失败: 提交文件不存在");
            }

        } catch (error: any) {
            messageService.error(error.message);
        }
    }

    // 提交作业
    async function homeworkSubmit(values: any) {
        setButtonLoading(true);

        if (!values.files || !values.files[0]) {
            messageService.info("请上传文件");
            setButtonLoading(false);
            return;
        }

        const file = values.files[0];
        const extensions: string[] = homeworks!.accept.split(",");
        const fileExtension = file.name.substring(file.name.lastIndexOf(".")).toLowerCase();

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

        // 创建表单数据
        const formData = new FormData();
        formData.append("activity_id", values.activity_id);

        const convertedFile = new File([file.originFileObj], file.name);
        formData.append("file", convertedFile, convertedFile.name);

        try {
            const data = await todosApiService.submitHomework(formData);

            if (data.code == 0) {
                messageService.success("提交成功");
                setOpen(false);
                setButtonLoading(false);
                await get_activity(0);
            } else if (data.code == 1) {
                messageService.error("提交失败: 活动不存在");
            } else if (data.code == 4) {
                messageService.error(`提交失败: 文件过大,最大支持${FileMaxUplaodSize / 1024 / 1024}MB`);
            } else if (data.code == 2) {
                messageService.error("请检查您的权限");
            } else {
                messageService.error("提交失败");
            }
        } catch (error: any) {
            messageService.error("提交失败: " + error.message);
            setButtonLoading(false);
            setOpen(false);
        }
    }


    async function homeworkOpen(activity_id: string) {
        form1.resetFields();

        try {
            const data = await todosApiService.openHomework(activity_id);

            if (data.code == 3 || data.data === "not found task") {
                return;
            } else if (data.code == 1 || data.data === "activity not found") {
                messageService.error("提交失败: 活动不存在");
                return;
            }

            const parsedData = data.data;
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
        } catch (error: any) {
            messageService.error(error.message);
        }

        setOpen(true);
    }

    async function file_delete() {
        try {
            const data = await todosApiService.deleteHomework(deleteActivityId);

            if (data.code == 1) {
                messageService.error("找不到活动");
            } else if (data.code == 2) {
                messageService.error("请检查您的权限");
            } else if (data.code == 3) {
                messageService.error("文件不存在");
            } else if (data.code == 0) {
                setShowDeleteFile(false);
                messageService.success("删除成功");
                await get_activity(0);
            }
        } catch (error: any) {
            messageService.error(error.message);
        }
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

    // 加载更多
    function loadingMore() {
        get_activity(1);
    }

    useEffect(() => {

        // 向后台请求 并返回渲染内容
        setTimeout(async () => {
            try {
                // 获取活动总数
                const countData = await todosApiService.getActivityCount();

                const count = Number(countData.data);
                if (!isNaN(count)) {
                    if (count > 50) {
                        setMaxActivityCount(count);
                    } else if (count == 0) {
                        setNone(true);
                    }
                }

                // 获取活动列表
                await get_activity(0);
            } catch (error: any) {
                messageService.error(error.message);
            } finally {
                setLoading(false);
            }
        });

    }, []);


    return (
        <>
            {loading && <Skeleton1 />}

            {!loading && error && <Alert
                message="加载失败"
                showIcon
                description="未捕获到的错误!"
                type="error"
            />}

            {!loading && !error && none && <Empty />}

            {!loading && !error && (
                <div>
                    <Row gutter={16}>
                        {cards?.map((card, index) =>
                        (
                            <Col key={index}>
                                <Badge.Ribbon text={(card.is_finished) ? "已提交" : "未提交"} color={(card.is_finished) ? "green" : "pink"}>
                                    <Card title={card.title} variant={'outlined'} hoverable>
                                        <p className="card_p">{card.name}</p>
                                        <p>{card.description}</p>
                                        <p>起始日期: {card.start_time}</p>
                                        <p>截止日期: {card.deadline}</p>

                                        <Space>

                                            {(card.is_finished) &&
                                                <Button onClick={() => homeworkGet(card.activity_id)} type="primary">
                                                    下载作业
                                                </Button>}
                                            {(card.is_finished) && card.status &&
                                                <Button onClick={() => {
                                                    setDeleteActivityName(card.name);
                                                    setDeleteActivityId(card.activity_id);
                                                    setShowDeleteFile(true);
                                                }}
                                                    danger>
                                                    删除提交
                                                </Button>}
                                        </Space>

                                        {(card.status == 1) && (<>
                                            {(card.is_finished) ? <><br /><br /></> : ""}
                                            <Button onClick={() => homeworkOpen(card.activity_id)} type="primary">
                                                {(card.is_finished) ? "重新提交" : "查看/提交作业"}
                                            </Button>
                                        </>)}
                                    </Card>
                                </Badge.Ribbon>

                                <br />

                            </Col>
                        )
                        )}
                    </Row>

                </div>
            )}


            <Modal
                title="提交作业"
                open={open}
                onOk={() => { form1.submit() }}
                confirmLoading={buttonLoading}
                onCancel={() => { setOpen(false) }}
                forceRender
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
                            <a key={index} onClick={() => downloadPublish(homeworks.activity_id, fileName)} target="_blank" rel="noopener noreferrer">
                                {fileName}
                                <br /></a>
                        );
                    })}

                    <br />
                    <div key={Math.random()}>

                        <Form
                            id="form"
                            form={form1}
                            name="ActivityCreate"
                            onFinish={homeworkSubmit}
                            style={{ maxWidth: 600 }}
                            autoComplete="on"
                        >

                            <Form.Item name="activity_id"
                                style={{ display: 'none' }}>
                                <Input type='hidden' />
                            </Form.Item>


                            <Form.Item name="files"
                                valuePropName="fileList"
                                getValueFromEvent={normFile} >
                                <Dragger
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
                                </Dragger>
                            </Form.Item>
                        </Form>

                    </div>
                </div>
                }

            </Modal>


            <Modal title="删除提交" open={showDeleteFile} onOk={() => { file_delete() }} onCancel={() => { setShowDeleteFile(false) }} destroyOnHidden okType="danger" okText="确定">
                <p>您确定要删除已经提交的<b>{deleteActivityName}</b>吗？</p>
            </Modal>

            {showLoadingMore && <Divider><a onClick={loadingMore}>继续加载</a></Divider>}
        </>
    )
};

export default TodosList;
