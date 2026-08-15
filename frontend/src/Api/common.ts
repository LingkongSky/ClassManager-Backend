import axios from 'axios';
import { ClassManagerSite } from '../global.ts';
import { getCookie } from '../Module/LocalStorage.ts';

export const generalApi = axios.create({
    baseURL: `${ClassManagerSite}/`,
});

generalApi.interceptors.request.use((config) => {
  const tokenType = getCookie('token_type');
  const token = getCookie('token');

  if (tokenType && token) {
    config.headers.Authorization = `${tokenType} ${token}`;
  } else {
    delete config.headers.Authorization;
  }

  return config;
});
