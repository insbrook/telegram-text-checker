'use strict';

/**
 * Sample text checking API method provider
 * @param options {Object|*}
 * @constructor
 */
function TextCorrectionApi(options) {
    options = options || {};
    this.endpoint = options.endpoint || '/';
    /*
     * Setting language is required only if you use api.textgears.com backend.
     * Sample text-correction.com can detect one
     */
    this.key = options.key || null;
    this.language = options.language || null;
}

/**
 * Makes a call to API
 * Returns Promise
 *
 * @param path {string}
 * @param params {Object|*}
 * @returns {Promise}
 */
TextCorrectionApi.prototype.callApi = function (path, params) {
    params = params || {};
    params.key = this.key;
    params.language = this.language;

    var self = this; // Or just use () => {} if you need to support modern browsers only
    return new Promise(function (resolve, reject) {
        // Feel free to replace it with any other library
        axios({
            method: 'post',
            url: self.endpoint + path,
            data: params
        })
            .then(function (response) { // onSuccess
                if (!response || !response.data || !response.data.status) {
                    reject(response);
                } else {
                    resolve(response.data);
                }
            })
            .catch(function (errorResponse) {
                reject(errorResponse);
            });
    })
};
