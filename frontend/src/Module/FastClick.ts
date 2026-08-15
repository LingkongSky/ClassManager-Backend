import { FastClick, deviceIsIOS } from 'fastclick'

FastClick.prototype.focus = function (targetElement: {
    setSelectionRange: (arg0: any, arg1: any) => void;
    type: string | string[];
    value: string | any[];
    focus: () => void;
}) {
    var length;
    if (
        deviceIsIOS &&
        targetElement.setSelectionRange &&
        targetElement.type.indexOf("date") !== 0 &&
        targetElement.type !== "time" &&
        targetElement.type !== "month" &&
        targetElement.type !== "email"
    ) {
        length = targetElement.value.length;
        targetElement.setSelectionRange(length, length);
    }
    targetElement.focus();
};

function initializeFastClick() {
    FastClick.attach(document.body);
}

document.addEventListener('DOMContentLoaded', initializeFastClick);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFastClick);
} else {
    initializeFastClick();
}

if ("addEventListener" in document) {
    document.addEventListener(
        "DOMContentLoaded",
        initializeFastClick,
        false
    );
}