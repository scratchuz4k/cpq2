import axios from 'axios';

export const checkImportType = (model) => {
    if (!model) {
        notyf.error('An import type must be selected before importing.');
        return false;
    }
    return true;
};

export const getTemplate = async (route) => {
    if (!checkImportType(route)) {
        return false;
    }
    try {
        let res = await axios
            .get(route + '/import', {
                responseType: 'blob'
            })
            .then((res) => {
                const url = window.URL.createObjectURL(new Blob([res.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', res.headers['content-disposition'].split('filename=')[1]);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        return true;
    } catch (err) {
        let errorMessage = 'Something went wrong';
        if (err.response && err.response.data instanceof Blob) {
            errorMessage = JSON.parse(await err.response.data.text());
        }
        notyf.error(errorMessage);
        return false;
    }
};
const sendImport = async (file, route, dryRun) => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('dry_run', dryRun ? 1 : 0);

    const res = await axios.post(route + '/import', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    });
    return res.data;
};

export const importPreview = (file, route) => sendImport(file, route, true);
export const importConfirm = (file, route) => sendImport(file, route, false);
