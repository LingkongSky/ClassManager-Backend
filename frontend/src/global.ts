export const ClassManagerSite: string = "api";
export const FileMaxUplaodSize = 209715200; // 200MB
export const WebsiteTitle = "风线学生交互平台";
export const FootText: string = "风线学生交互平台 V3.0.0 ©2024 \n";
export function deceteMobile() {
    const mobileRegex = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i;
    if (navigator.userAgent.match(mobileRegex))
        return true;
    else
        return false;
}
