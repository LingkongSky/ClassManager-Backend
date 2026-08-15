export function isAppBrowser() {
    if (navigator.userAgent.toLocaleLowerCase().indexOf("micromessenger") > -1 || (navigator.userAgent.indexOf("MQQBrowser") > -1 || navigator.userAgent.indexOf("QQTheme") > -1)) {
        return true;
    } else {
        return false;
    }
}