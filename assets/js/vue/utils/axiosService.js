const axios = require('axios');

const axiosInstance = axios.create({});
const { CancelToken } = axios;
const source = CancelToken.source();
/**
 * Add vue loading overlay indicator.
 * @param thisComponent
 */
function addLoadingOverLay(thisComponent) {
  let loader = null;
  axiosInstance.interceptors.request.use((config) => {
    loader = thisComponent.$loading.show({
      container: thisComponent.$refs.formContainer,
      loader: 'bars',
      color: '#007bff',
    });
    return config;
  }, (error) => Promise.reject(error));

  function hideLoader() {
    loader.hide();
    loader = null;
  }

  axiosInstance.interceptors.response.use((response) => {
    hideLoader();
    return response;
  }, (error) => {
    hideLoader();
    return Promise.reject(error);
  });
}

/**
 * Axios GET API.
 * @param url
 * @param thisComponent
 * @param loadingOverlay
 * @returns {Promise<AxiosResponse<any>>}
 */
export const getApi = (url, { thisComponent = null, addLoader = false } = {}) => {
  if (addLoader === true) {
    addLoadingOverLay(thisComponent);
  }
  return axiosInstance({
    url, method: 'GET', responseType: 'json', cancelToken: source.token,
  });
};

/**
 * Axios POST API.
 * @param url
 * @param postData
 * @returns {Promise<AxiosResponse<any>>}
 */
export const postApi = (url, postData) => axiosInstance.post(url, postData);

/**
 * Axios DELETE API.
 * @param url
 * @returns {Promise<AxiosResponse<any>>}
 */
export const deleteApi = (url) => axiosInstance.delete(url);

/**
 * Axios PUT API.
 * @param url
 * @param postData
 * @returns {Promise<AxiosResponse<any>>}
 */
export const putApi = (url, postData) => axiosInstance.put(url, postData);

/**
 * Axios PATCH API.
 * @param url
 * @param patchData
 * @returns {Promise<AxiosResponse<any>>}
 */
export const patchApi = (url, patchData) => axiosInstance.patch(url, patchData);

/**
 * Download blob from API.
 * @param url
 * @param config
 * @returns {Promise<AxiosResponse<any>>}
 */
export const downloadApi = (url, config) => {
  // eslint-disable-next-line no-param-reassign
  config.cancelToken = source.token;
  return axiosInstance.get(url, config);
};
