import axios from 'axios';

class Api {
  static prepareUrl(url) {
    if (url.charAt(0) !== '/') {
      return `/${url}`;
    }
    return url;
  }

  static getHeaders() {
    if (localStorage.getItem('token')) {
      return { headers: { Authorization: `Bearer ${localStorage.getItem('token')}`, 'Content-Type': 'application/json' } };
    }

    return {};
  }

  static get(url) {
    return axios.get(process.env.VUE_APP_BASE_API + Api.prepareUrl(url), Api.getHeaders());
  }

  static getFile(url) {
    const headers = Api.getHeaders();
    headers.responseType = 'blob';
    return axios.get(process.env.VUE_APP_BASE_API + Api.prepareUrl(url), headers);
  }

  static post(url, data) {
    return axios.post(process.env.VUE_APP_BASE_API + Api.prepareUrl(url), data, Api.getHeaders());
  }

  static postFormData(url, data) {
    return axios.post(process.env.VUE_APP_BASE_API + Api.prepareUrl(url), data, Api.getHeaders());
  }

  static put(url, data) {
    return axios.put(process.env.VUE_APP_BASE_API + Api.prepareUrl(url), data, Api.getHeaders());
  }

  static patch(url, data) {
    return axios.patch(process.env.VUE_APP_BASE_API + Api.prepareUrl(url), data, Api.getHeaders());
  }

  static delete(url) {
    return axios.delete(process.env.VUE_APP_BASE_API + Api.prepareUrl(url), Api.getHeaders());
  }
}

export default Api;
