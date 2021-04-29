const axios = require('axios');
const axiosInstance = axios.create({});

/**
 * Axios GET API.
 * @param url
 * @param thisComponent
 * @param loadingOverlay
 * @returns {Promise<AxiosResponse<any>>}
 */
const getApi = (url, thisComponent, loadingOverlay) => {
    if (loadingOverlay === true) {
        addLoadingOverLay(thisComponent);
    }
    return axiosInstance.get(url).then(response => response.data);
}

/**
 * Add vue loading overlay indicator.
 * @param thisComponent
 */
function addLoadingOverLay (thisComponent) {
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

    function hideLoader(){
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

/**
 * Axios Post API.
 * @param url
 * @param postData
 * @returns {Promise<AxiosResponse<any>>}
 */
const postApi = (url, postData) => {
    return axiosInstance.post(url, postData).then(response => response.data);
};

export { getApi, postApi};