// cypress/support/utils.js

import crypto from "crypto";

export const generateHash = (input) => {
    return crypto.createHash("md5").update(input).digest("hex"); // SHA-256 hash
}
export const getCurrentDate = () => {
    const currentDate = new Date();
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, "0");
    const day = String(currentDate.getDate()).padStart(2, "0");
    const hour = String(currentDate.getHours()).padStart(2, "0");
    const minute = String(currentDate.getMinutes()).padStart(2, "0");
    const millisecond = String(currentDate.getMilliseconds()).padStart(3, "0");

    // Create a readable date string
    const readableDate = `${year}-${month}-${day} ${hour}:${minute}:${millisecond}`;

    return readableDate;
}

export const slugify = (string) => {
    return string
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}
