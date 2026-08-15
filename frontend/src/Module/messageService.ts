import { message } from '../User/user.tsx';

const messageService = {
    success: (content: string) => {
        if (message) {
            message.success(content);
        } else {
            console.warn('Message service not initialized yet');
            import('antd').then(({ message: antdMessage }) => {
                antdMessage.success(content);
            });
        }
    },
    error: (content: string) => message?.error(content),
    info: (content: string) => message?.info(content),
    warning: (content: string) => message?.warning(content),
};

export default messageService;