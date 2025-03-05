
export const format = {
    date: (date) => {
        if (!date) {
            return '';
        }

        const now = Date.now();
        const parsedDate = Date.parse(date);

        if (isNaN(parsedDate)) {
            return ''; // Invalid date format
        }

        const oneMinuteAgo = now - 60 * 1000; // 1 minute in milliseconds
        const oneDayAgo = now - 24 * 60 * 60 * 1000; // 1 day in milliseconds

        // "just now" if within the last minute
        if (parsedDate >= oneMinuteAgo) {
            return 'just now';
        }

        // If within the last 24 hours, return time difference (like "5 hours ago")
        if (parsedDate >= oneDayAgo) {
            return timeDiffForHumans(parsedDate);
        }

        // Otherwise, return formatted date as 'Y-m-d H:i'
        const dt = new Date(parsedDate);
        return dt.toISOString().slice(0, 16).replace('T', ' '); // Y-m-d H:i format
    },

    speed: (value) => {
        let temp = value ?? 0;
        let number = temp;
        let suffix = "Mb";
        let rounding = 10;

        if (temp < 1000) {
            number = temp;
        } else if (temp < 10000) {
            number = temp / 1000;
            suffix = "Gb";
            rounding = 1;
        } else {
            number = temp / 1000;
            suffix = "Gb";
            rounding = 1;
        }

        let result = //TODO: remove round if temp < 1000
            parseInt(Math.ceil(number / rounding) * rounding) +
            " " +
            suffix;
        return result;
    },
    toCamelCase: (str) => {
        if (!str) {
            return ''
        }
        return str
            .toLowerCase()
            .replace(/_([a-z])/g, (_, letter) => letter.toUpperCase()) // Convert characters after dashes to uppercase
            .replace(/^([a-z])/, (_, letter) => letter.toUpperCase());;
    },
    toSnakeCase: (str) => {
        if (!str) {
            return ''
        }
        return str
            .replace(/([a-z])([A-Z])/g, '$1_$2') // Insert an underscore between lowercase and uppercase letters
            .replace(/\s+/g, '_')               // Replace spaces with underscores
            .toLowerCase();

    },
    toLowerCase: (str) => {
        if (!str) {
            return ''
        }
        return str
            .replace(/([a-z])([A-Z])/g, '$1$2') // Insert an underscore between lowercase and uppercase letters
            .replace(/\s+/g, '')               // Replace spaces with underscores
            .toLowerCase();

    }
}


// Helper function to return human-readable time differences
function timeDiffForHumans(date) {
    const now = Date.now();
    const diffInSeconds = Math.floor((now - date) / 1000);

    const secondsInMinute = 60;
    const secondsInHour = 60 * 60;
    const secondsInDay = 24 * 60 * 60;

    if (diffInSeconds < secondsInMinute) {
        return `${diffInSeconds} seconds ago`;
    }

    if (diffInSeconds < secondsInHour) {
        const minutes = Math.floor(diffInSeconds / secondsInMinute);
        return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    }

    if (diffInSeconds < secondsInDay) {
        const hours = Math.floor(diffInSeconds / secondsInHour);
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    }

    const days = Math.floor(diffInSeconds / secondsInDay);
    return `${days} day${days > 1 ? 's' : ''} ago`;
}


export default format
