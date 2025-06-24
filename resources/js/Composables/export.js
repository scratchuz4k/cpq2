import axios from "axios";

export const exportData = async (filters, route) => {
    try {
        let res = await axios
            .post(route + "/export", filters, {
                responseType: "blob",
            })
            .then((res) => {
                if (res.status === 204) {
                    notyf.open({
                        type: "warning",
                        message:
                            "There's no data to export for these filters. Please redefine your filters.",
                    });
                    return;
                }

                const url = window.URL.createObjectURL(new Blob([res.data]));
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute(
                    "download",
                    res.headers["content-disposition"].split("filename=")[1]
                );
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        return true;
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? "Something went wrong");
        return false;
    }
};
