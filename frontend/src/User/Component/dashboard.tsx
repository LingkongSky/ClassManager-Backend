import React, { useEffect, useState } from 'react';
import {
    AlignLeftOutlined,
    CommentOutlined,
    FileOutlined,
    PieChartOutlined,
    QuestionCircleOutlined,
    ReadOutlined,
    SlidersOutlined,
    TeamOutlined,
    UserOutlined,
} from '@ant-design/icons';
import type { MenuProps } from 'antd';
import { Breadcrumb, FloatButton, Layout, Menu, theme, Tooltip } from 'antd';
const { Header, Content, Footer, Sider } = Layout;
import Todos from '../Component/Menu/Todos.tsx';
import MemberList from '../Component/Menu/MemberList.tsx';
import UserCenter from '../Component/Menu/UserCenter.tsx';
import { ConfigProvider } from "antd";
import ActivityCreate from '../Component/Menu/ActivityCreate.tsx';
import { ClassManagerSite, deceteMobile, FootText, WebsiteTitle } from '../../global.ts';
import ActivityEdit from '../Component/Menu/ActivityEdit.tsx';
import ActivityContent from '../Component/Menu/ActivityContent.tsx';
import TodosList from '../Component/Menu/TodosList.tsx';
import ClassList from '../Component/Menu/ClassList.tsx';
import PermissionGroupEdit from '../Component/Admin/PermissionGroupEdit.tsx';
import PermissionContent from '../Component/Admin/PermissionContent.tsx';
import { userCenterApiService } from '../../Api/userCenter.ts';
import zhCN from 'antd/locale/zh_CN';

type MenuItem = Required<MenuProps>['items'][number];

function getItem(
    label: React.ReactNode,
    key: React.Key,
    icon?: React.ReactNode,
    children?: MenuItem[],
): MenuItem {
    return {
        key,
        icon,
        children,
        label,
    } as MenuItem;
}

export const normFile = (e: any) => {
    if (Array.isArray(e)) {
        return e;
    }
    return e && e.fileList;
};

export const formItemLayout = {
    labelCol: {
        xs: { span: 24 },
        sm: { span: 6 },
    },
    wrapperCol: {
        xs: { span: 24 },
        sm: { span: 14 },
    },
};
/*
const initialItems: MenuItem[] = [
    getItem('未读公告', 'unread', <CommentOutlined />, [
    ]),
    {type: 'divider'},
    getItem('已读公告', 'read', <CommentOutlined />, [
    ]),
];

*/

const DashBoard: React.FC = () => {
    /*
        const [showMail, setShowMail] = useState(false);
        const [mailTitle,setMailTitle] = useState("");
        const [markdownContet, setMarkdownContent] = useState(``);
    
    
        const [mailItems, setMailItems] = useState<MenuItem[]>(initialItems);
        const [mailLoading,setMailLoading] = useState(true);
    */


    var title_dict = ["待办事项", "活动总览", "活动管理", "活动管理/发布活动", "活动管理/编辑活动",
        "成员管理", "成员管理/成员名单", "成员管理/班级管理", "个人中心", "活动管理/活动详情",
        "权限管理", "权限管理/编辑管理员", "权限管理/编辑权限组", "平台指南", "公告管理/创建公告", "公告管理/编辑公告", "公告管理/管理公告", "公告管理"];

    const userBoard: MenuItem[] =
        [
            getItem('待办事项', '0', <PieChartOutlined />),
            getItem('活动总览', '1', <AlignLeftOutlined />),
            getItem('个人中心', '8', <FileOutlined />),
            // getItem('江农学生论坛', '13', <CoffeeOutlined />),
            getItem('平台指南', '14', <ReadOutlined />),
            //getItem('AI助手（维护中）', '15', <BulbOutlined />)
        ];

    const adminBoard: MenuItem[] = [
        getItem('待办事项', '0', <PieChartOutlined />),
        getItem('活动总览', '1', <AlignLeftOutlined />),
        getItem('活动管理', '2', <UserOutlined />, [
            getItem('发布活动', '3'),
            getItem('编辑活动', '4'),
            getItem('活动详情', '9')]),
        getItem('成员管理', '5', <TeamOutlined />, [
            getItem('成员名单', '6')
        ]),
        /*  
              getItem('公告管理', '20', <CommentOutlined />, [
              getItem('创建公告', '17'), 
              getItem('编辑公告', '18'),
              getItem('管理公告', '19')
          ]),
      */
        getItem('个人中心', '8', <FileOutlined />),
        // getItem('江农学生论坛', '13', <CoffeeOutlined />),
        getItem('平台指南', '14', <ReadOutlined />),
        //getItem('AI助手（维护中）', '15', <BulbOutlined />)
    ];




    const superAdminBoard: MenuItem[] = [
        getItem('待办事项', '0', <PieChartOutlined />),
        getItem('活动总览', '1', <AlignLeftOutlined />),
        getItem('活动管理', '2', <UserOutlined />, [
            getItem('发布活动', '3'),
            getItem('编辑活动', '4'),
            getItem('活动详情', '9')]),
        getItem('成员管理', '5', <TeamOutlined />, [
            getItem('成员名单', '6'),
            getItem('班级管理', '7')
        ]),
        /* getItem('公告管理', '20', <CommentOutlined />, [
             getItem('创建公告', '17'),
             getItem('编辑公告', '18'),
             getItem('管理公告', '19')
         ]),*/
        getItem('权限管理', '10', <SlidersOutlined />, [
            getItem('编辑管理员', '11'),
            getItem('编辑权限组', '12')
        ]),
        getItem('个人中心', '8', <FileOutlined />),
        //getItem('江农学生论坛', '13', <CoffeeOutlined />),
        getItem('平台指南', '14', <ReadOutlined />),
        //getItem('AI助手（维护中）', '15', <BulbOutlined />)
    ]




    const [collapsed, setCollapsed] = useState(false);
    const [selectedContent, setSelectedContent] = useState('0');
    const [selectedTitle, setSelectedTitle] = useState('待办事项');
    const [items, setItems] = useState<MenuItem[]>(userBoard);
    const [isMobile, _setIsMobile] = useState(deceteMobile());
    //  const [unreadCount,setUnreadCount] = useState(0);
    const [_permissionLevel, setPremissionLevel] = useState(0);

    //点击不同的模块切换页面
    const handleClick = (e: any) => {
        // console.log('Clicked item:', e.key);
        if (e.key == "13") {
            window.open("https://www.jxau-forum.com");
            return;
        } else if (e.key == "14") {
            window.open("/Docs/");
            return;
        } else if (e.key == "15") {
            return;
        }

        setSelectedContent(e.key);
        setSelectedTitle(title_dict[e.key]);
    }

    const {
        token: { colorBgContainer, borderRadiusLG },
    } = theme.useToken();



    /*
        const fetchUnreadCount = async () => {
            try {
                const count = await getUnreadMailCount();
                setUnreadCount(count);
            } catch (error) {
                console.error('获取未读邮件失败:', error);
            }
        };
    
        // 显示邮件模态框
        const showMailModal = async () => {
            setMailLoading(true);
            try {
                const mailData = await getMailList();
                const tempItems: MenuItem[] = [
                    getItem('未读消息', 'unread', <CommentOutlined />, mailData.unread.map((mail: any) => ({
                        key: mail.mail_id,
                        label: mail.title
                    }))),
                    { type: 'divider' },
                    getItem('已读消息', 'read', <CommentOutlined />, mailData.read.map((mail: any) => ({
                        key: mail.mail_id,
                        label: mail.title
                    }))),
                ];
                setMailItems(tempItems);
            } catch (error) {
                console.error('获取邮件列表失败:', error);
            } finally {
                setMailLoading(false);
                setShowMail(true);
            }
        };
    
        // 点击邮件项获取详情
        const handleClick2 = async (e: any) => {
            const mail_id = e.key;
            const type = e.keyPath[1];
            setMailLoading(true);
            try {
                const mailContent = await getMailContent(mail_id, type);
                setMailTitle(mailContent.title);
                setMarkdownContent(Base64.decode(mailContent.content));
                setUnreadCount(mailContent.unread_count);
            } catch (error) {
                console.error('获取邮件详情失败:', error);
            } finally {
                setMailLoading(false);
            }
        };
    
    */



    useEffect(() => {

        setTimeout(async () => {

            if (isMobile) {
                setCollapsed(true);
            }
            try {
                const data = await userCenterApiService.getUserInfo();
                if (data.data.level == "5") {
                    setItems(adminBoard);
                    setPremissionLevel(1);
                } else if (data.data.level == "10") {
                    setItems(superAdminBoard);
                    setPremissionLevel(2);
                } else if (data.data.level != "0") {
                    window.location.href = "/login.html";
                }

            } catch (error) {
                window.location.href = "/login.html";
            }
            // fetchUnreadCount();
        });
    }, []);


    return (
        <ConfigProvider locale={zhCN}>
            <Layout style={{ minHeight: '100vh' }}>

                <Sider collapsible collapsed={collapsed} onCollapse={(value) => setCollapsed(value)} style={{ position: 'relative' }}>
                    <Menu theme="dark" defaultSelectedKeys={['0']} mode="inline" items={items} onClick={handleClick} />
                </Sider>
                <Layout>

                    <Header style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                        <b id="user_title" onClick={() => { window.location.href = "./"; }}>{WebsiteTitle}</b>
                        {/* (!isMobile || collapsed) &&
                        <Badge id="mail_button" count={unreadCount}><Button type="primary" icon={<SoundOutlined />} onClick={() => showMailModal()}>通知</Button></Badge>
                        */}
                    </Header>

                    <Content style={{ margin: '0 16px' }}>

                        <Breadcrumb style={{ margin: '16px 0' }} items={[
                            { title: selectedTitle }
                        ]} >
                        </Breadcrumb>

                        <div
                            style={{
                                padding: 24,
                                minHeight: 360,
                                background: colorBgContainer,
                                borderRadius: borderRadiusLG,
                            }}
                        >

                            {selectedContent == '0' && <Todos />}
                            {selectedContent == '1' && <TodosList />}
                            {selectedContent == '3' && <ActivityCreate />}
                            {selectedContent == '4' && <ActivityEdit />}
                            {selectedContent == '6' && <MemberList />}
                            {selectedContent == '7' && <ClassList />}
                            {selectedContent == '8' && <UserCenter />}
                            {selectedContent == '9' && <ActivityContent />}
                            {selectedContent == '11' && <PermissionContent />}
                            {selectedContent == '12' && <PermissionGroupEdit />}
                            {/*selectedContent == '17' && <MailCreate type={permissionLevel}/>*/}
                            {/*selectedContent == '18' && <MailEdit />*/}
                            {/*selectedContent == '19' && <MailContent />*/}


                        </div>
                    </Content>
                    <Footer style={{ textAlign: 'center' }}>
                        {FootText}
                    </Footer>

                </Layout>
            </Layout>



            <Tooltip title="悬浮球" color={"#87d068"}>
                <FloatButton.Group
                    trigger="click"
                    type="primary"
                    style={{ insetInlineEnd: isMobile ? 10 : 50 }}
                    icon={<QuestionCircleOutlined />}
                >
                    <Tooltip title="使用帮助" color={"#87d068"}>
                        <FloatButton onClick={() => { window.open(ClassManagerSite + "/Docs/") }} />
                    </Tooltip>

                    <Tooltip title="交流群" color={"#87d068"}>
                        <FloatButton icon={<CommentOutlined />} onClick={() => { window.open("https://qm.qq.com/q/ctAiS9gwsE") }} />
                    </Tooltip>

                </FloatButton.Group>
            </Tooltip>


            {/*<Modal
                title="公告"
                open={showMail}
                onCancel={() => { setShowMail(false) }}
                footer={null}
                width={isMobile ?  "80%" : "50%"}
            >
                <Splitter style={{boxShadow: '0 0 10px rgba(0, 0, 0, 0.1)',height: "60vh"}}>
                  
                    <Splitter.Panel min="10%" max="50%" defaultSize="25%" resizable={false}>

                        <Menu
                            mode="inline"
                            items={mailItems}
                            onClick= {handleClick2}  
                            defaultOpenKeys={['unread','read']}                            
                        />
                    </Splitter.Panel>
                     <Splitter.Panel style={{marginLeft: '10px'}}>

                        <Title level={3}>{mailTitle}</Title>
                        <Divider />
                        <Markdown remarkPlugins={[remarkGfm]}>{markdownContet}</Markdown>

                        {mailLoading && <Skeleton />}

                    </Splitter.Panel>

                </Splitter>

            </Modal>*/}

        </ConfigProvider>
    );
};

export default DashBoard;