import React from 'react';
import ReactDOM from 'react-dom/client'
import { App as AntdApp } from 'antd';
import DashBoard from './Component/dashboard.tsx'
import { WebsiteTitle } from '../global.ts'
import { isAppBrowser } from "../Module/navigator.ts"
import './user.css'
import { MessageInstance } from 'antd/es/message/interface.js';

declare global {
  var antdMessage: MessageInstance | null;
}

window.antdMessage = null;

export let message: MessageInstance | null = null;

// 主组件
const RootComponent = () => {
  const { message: messageApi } = AntdApp.useApp();

  React.useEffect(() => {
    message = messageApi;
    window.antdMessage = messageApi;
  }, [messageApi]);

  return (
    <>
      <title>{WebsiteTitle}</title>
      <DashBoard />
    </>
  );
};


const container = document.getElementById('root');
if (container) {
  const root = ReactDOM.createRoot(container);
  
  root.render(
    <AntdApp>
      <RootComponent />
    </AntdApp>
  );
}

window.onload = function () {
    import('../Module/FastClick.ts');
}

if (isAppBrowser()) {
    window.location.href = "redirect.html";
}