export const handleResponse = <T>(
  response: any,
  resolve: (value: T | PromiseLike<T>) => void,
  reject: (reason?: any) => void,
  successCodes: number[] = [0]
) => {
  if (response.data instanceof Blob) {
    const reader = new FileReader();
    reader.onload = () => {
      try {
        const text = reader.result as string;
        const data = JSON.parse(text);
        if (successCodes.includes(data?.code)) {
          resolve(data);
          return;
        }
        reject(new Error(data?.message || '请求失败'));
      } catch {
        reject(new Error('响应解析失败'));
      }
    };
    reader.onerror = () => reject(new Error('响应解析失败'));
    reader.readAsText(response.data);
  } else {
    if (successCodes.includes(response.data?.code)) {
      resolve(response.data);
    } else {
      reject(new Error(response.data?.message || '请求失败'));
    }
  }
};
export const handleDownload = <T>(
  response: any,
  resolve: (value: T | PromiseLike<T>) => void,
  reject: (reason?: any) => void,
) => {
  try {
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;

    // 从响应头获取文件名
    const contentDisposition = response.headers['content-disposition'];
    let filename = 'homework.zip';

    if (contentDisposition) {
      const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
      const matches = filenameRegex.exec(contentDisposition);

      if (matches != null && matches[1]) {
        filename = matches[1].replace(/['"]/g, '');
        filename = decodeURIComponent(filename);

        const utf8FilenameRegex = /filename\*=UTF-8''([^;\n]+)/i;
        const utf8Matches = utf8FilenameRegex.exec(contentDisposition);

        if (utf8Matches != null && utf8Matches[1]) {
          filename = decodeURIComponent(utf8Matches[1]);
        }
      }
    }

    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);

    resolve({ code: 0, message: "success", data: null } as unknown as T);

  } catch (e: any) {
    reject(new Error(e));
  }
}

export const handleError = (error: any, reject: (reason?: any) => void) => {
  if (error.response) {
    reject(new Error(`请求失败: ${error.response.status}`));
  } else if (error.request) {
    reject(new Error('网络错误，请检查网络连接'));
  } else {
    reject(new Error(`请求配置错误: ${error.message}`));
  }
};

export const requestWithHandler = <T>(
  request: Promise<any>,
  successCodes: number[] = [0]
) =>
  new Promise<T>((resolve, reject) => {
    request
      .then(response => handleResponse<T>(response, resolve, reject, successCodes))
      .catch(error => handleError(error, reject));
  });

export const serializeData = (data: Record<string, any>) => {
  const params = new URLSearchParams();
  Object.keys(data).forEach(key => {
    params.append(key, data[key]);
  });
  return params;
};
