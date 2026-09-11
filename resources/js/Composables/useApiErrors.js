/**
 * Collect every error message from a failed HTTP response into a flat list.
 *
 * Accepts an axios error, its `response`, or a `response.data` payload and
 * understands the shapes this API returns:
 *   - Laravel validation: `{ message, errors: { field: [msg, ...] } }`
 *   - single message:      `{ message: '...' }`
 *   - single error:        `{ error: '...' }`
 *   - plain string body
 *
 * @param {*} error
 * @returns {string[]} unique, de-duplicated error messages
 */
export function getResponseErrors(error) {
    const data = error?.response?.data ?? error?.data ?? error;

    if (!data) {
        return [];
    }

    if (typeof data === 'string') {
        return [data];
    }

    const messages = [];
    const bag = data.errors ?? data.error;

    if (bag && typeof bag === 'object') {
        Object.values(bag).forEach((value) => {
            (Array.isArray(value) ? value : [value]).forEach((entry) => {
                if (entry != null && typeof entry !== 'object') {
                    messages.push(String(entry));
                }
            });
        });
    } else if (typeof bag === 'string') {
        messages.push(bag);
    }

    // Only fall back to the summary `message` when there were no field errors,
    // otherwise it just duplicates them ("... (and 2 more errors)").
    if (messages.length === 0 && typeof data.message === 'string') {
        messages.push(data.message);
    }

    return [...new Set(messages)];
}

/**
 * Flatten a failed response's errors into one HTML string for notyf.
 *
 * @param {*} error
 * @param {string} [fallback]
 * @returns {string}
 */
export function formatResponseErrors(error, fallback = 'Something went wrong') {
    const messages = getResponseErrors(error);

    return messages.length ? messages.join('<br>') : fallback;
}
