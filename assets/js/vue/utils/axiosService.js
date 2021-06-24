const axios = require('axios');
const axiosInstance = axios.create({});
const CancelToken = axios.CancelToken;
const source = CancelToken.source();

/**
 * Axios GET API.
 * @param url
 * @param thisComponent
 * @param loadingOverlay
 * @returns {Promise<AxiosResponse<any>>}
 */
export const getApi = (url, {thisComponent = null, addLoader = false} = {}) => {
    if (addLoader === true) {
        addLoadingOverLay(thisComponent);
    }
    return axiosInstance({url: url, method: 'GET', responseType: "json", cancelToken: source.token});
}

/**
 * Axios POST API.
 * @param url
 * @param postData
 * @returns {Promise<AxiosResponse<any>>}
 */
export const postApi = (url, postData) => {
    return axiosInstance.post(url, postData);
};

/**
 * Axios DELETE API.
 * @param url
 * @returns {Promise<AxiosResponse<any>>}
 */
export const deleteApi = (url) => {
    return axiosInstance.delete(url);
}

/**
 * Axios PUT API.
 * @param url
 * @param postData
 * @returns {Promise<AxiosResponse<any>>}
 */
export const putApi = (url, postData) => {
    return axiosInstance.put(url, postData);
}

/**
 * Axios PATCH API.
 * @param url
 * @param patchData
 * @returns {Promise<AxiosResponse<any>>}
 */
export const patchApi = (url, patchData) => {
    return axiosInstance.patch(url, patchData);
}

/**
 * Download blob from API.
 * @param url
 * @param config
 * @returns {Promise<AxiosResponse<any>>}
 */
export const downloadApi = (url, config) => {
    config.cancelToken = source.token;
    return axiosInstance.get(url, config);
}

/**
 * Add vue loading overlay indicator.
 * @param thisComponent
 */
function addLoadingOverLay(thisComponent) {
    let loader = null;
    axiosInstance.interceptors.request.use(function (config) {
        loader = thisComponent.$loading.show({
            container: thisComponent.$refs.formContainer,
            loader: 'bars',
            color: '#007bff',
        });
        return config;
    }, function (error) {
        return Promise.reject(error);
    });

    function hideLoader() {
        loader && loader.hide();
        loader = null;
    }

    axiosInstance.interceptors.response.use(function (response) {
        hideLoader();
        return response;
    }, function (error) {
        hideLoader();
        return Promise.reject(error);
    });
}
