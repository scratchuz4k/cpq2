import { DateTime } from 'luxon';
import dayjs from 'dayjs';

let currency = JSON.parse(document.head.querySelector('meta[name="currency-config"]').content);
const GBP = new Intl.NumberFormat('en-GB', {
    style: 'currency',
    currency: 'GBP',
    minimumFractionDigits: currency.precision ?? 2,
    maximumFractionDigits: currency.precision ?? 2
});

const format = {
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
        let suffix = 'Mb';

        if (temp < 100) {
            number = Math.ceil(temp / 10) * 10;
        } else if (temp < 1000) {
            number = Math.ceil(temp / 100) * 100;
        } else {
            number = temp / 1000;
            suffix = 'Gb';
            number = Math.round(number * 10) / 10;
        }

        return number + ' ' + suffix;
    },
    simpleSpeed: (value) => {
        let temp = value ?? 0;
        let number = temp;

        if (temp < 100) {
            number = Math.ceil(temp / 10) * 10;
        } else if (temp < 1000) {
            number = Math.ceil(temp / 100) * 100;
        } else {
            number = Math.ceil(temp / 1000) * 1000;
        }

        return number;
    },

    toCamelCase: (str) => {
        if (!str) {
            return '';
        }
        return str
            .toLowerCase()
            .replace(/_([a-z])/g, (_, letter) => letter.toUpperCase()) // Convert characters after dashes to uppercase
            .replace(/^([a-z])/, (_, letter) => letter.toUpperCase());
    },
    toSnakeCase: (str) => {
        if (!str) {
            return '';
        }
        return str
            .replace(/([a-z])([A-Z])/g, '$1_$2') // Insert an underscore between lowercase and uppercase letters
            .replace(/\s+/g, '_') // Replace spaces with underscores
            .toLowerCase();
    },
    toLowerCase: (str) => {
        if (!str) {
            return '';
        }
        return str
            .replace(/([a-z])([A-Z])/g, '$1$2') // Insert an underscore between lowercase and uppercase letters
            .replace(/\s+/g, '') // Replace spaces with underscores
            .toLowerCase();
    },
    capitalizeFirstLetter: (str) => {
        if (!str) {
            return '';
        }

        return str
            .toLowerCase() // Convert the whole string to lowercase
            .replace(/\b\w/g, (char) => char.toUpperCase()); // Capitalize first letter of each word
    },
    GBP: (value) => {
        return GBP.format(value);
    },
    decimalPlaces: (value) => {
        const precision = currency.precision ?? 2;
        return `${Number(value).toFixed(precision)}`;
    },
    parsedDateRange: (rangeDate) => {
        const dateMethods = {
            start: (dt) => dt.startOf('day'),
            end: (dt) => dt.endOf('day')
        };

        return Object.fromEntries(
            Object.entries(rangeDate).map(([key, value]) => [
                key,
                value === null ? null : dateMethods[key](DateTime.fromJSDate(value)).toISO()
            ])
        );
    },
    expireDate: (date, expireDays) => {
        debugger;
        return dayjs(date)
            .add(expireDays ?? 14, 'day')
            .format('DD/MM/YYYY');
    }
};

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

export default format;
