import qs from 'qs';
import { ref } from 'vue';

// Enum-like object for filter operators (mirroring PHP DataProvider)
export const FilterOperators = {
    NOT_EQUALS: 'not_equals',
    EQUALS: 'equals',
    NOT_CONTAINS: 'not_contains',
    CONTAINS: 'contains',
    STARTS_WITH: 'starts_with',
    ENDS_WITH: 'ends_with',
    GREATER_THAN: 'greater_than',
    LESS_THAN: 'less_than',
    GREATER_THAN_OR_EQUAL_TO: 'greater_than_or_equal_to',
    LESS_THAN_OR_EQUAL_TO: 'less_than_or_equal_to',
    IS_NULL: 'is_null',
    IN: 'in',
    NOT_IN: 'not_in',
    SEARCH: 'search'
};

export class Api {
    columns = [];
    filters = [];
    groupedfilters = [];
    order = 'desc';
    orderBy = 'updated_at';
    group = null;
    groupOrder = 'asc';
    search = null;
    route = null;
    meta = null;

    /**
     *
     * @param {string} route
     */
    constructor(route) {
        this.route = route;
        this.meta = new ApiMeta();
        this.items = ref([]);
        this.isLoading = ref(false);
    }

    /**
     * Adds a filter to querystring in the url as a parameter
     * @param {string} column
     * @param {FilterOperators} operator
     * @param {string} value
     * @param {('where'|'orWhere')} condition
     */

    addFilter(column, operator, value, condition = 'where') {
        this.filters.push({
            column: column,
            operator: operator,
            value: value,
            function: condition
        });
    }
    /**
     * Adds a filter to querystring in the url as a parameter
     * @param {string} key
     * @param {string} column
     * @param {FilterOperators} operator
     * @param {string} value
     * @param {('where'|'orWhere')} condition
     */
    addGroupFilter(key, column, operator, value, condition = 'orWhere') {
        let index = this.groupedfilters.findIndex((q) => q.key === key);
        if (index === -1) {
            index = this.groupedfilters.length;
            this.groupedfilters[index] = { key: key, group: [] };
        }

        this.groupedfilters[index].group.push({
            column: column,
            operator: operator,
            value: value,
            function: condition
        });
    }

    clearFilters() {
        this.filters = [];
        this.groupedfilters = [];
        this.search = null;
    }

    /**
     * Adds columns to the columns array to be sent to the backend as a searchable column
     * @param {array} columns
     */
    setColumns(columns) {
        this.columns = columns;
    }

    /**
     * Adds a search term to be sent to the backend for searching using the columns
     * @param {string} search
     */
    setSearch(search) {
        this.search = search;
    }

    /**
     * Adds a search term to be sent to the backend for searching using the columns
     * @param {group} group
     * @param {('asc'|'desc')} order
     */
    setGroup(group, order = 'asc') {
        this.group = group;
        this.groupOrder = order;
    }

    /**
     * Adds a search term to be sent to the backend for searching using the columns
     * @param {group} column
     * @param {('asc'|'desc')} order
     */
    setOrder(column, order = 'asc') {
        this.orderBy = column;
        this.order = order;
    }

    getData = async () => {
        if (this._abortController) {
            this._abortController.abort();
        }
        this._abortController = new AbortController();

        this.isLoading.value = true;
        let object = {
            order: this.order,
            orderBy: this.orderBy,
            limit: this.meta.limit,
            page: this.meta.page,
            columns: this.columns.length ? this.columns.filter((data) => data !== 'actions').join(',') : null,
            filters: [
                ...this.filters.filter((x) => x.value != null),
                ...this.groupedfilters.filter((x) => x.group.length > 0)
            ],
            search: this.search
        };

        if (this.group) {
            object.group = this.group;
            object.groupOrder = this.groupOrder;
        }

        try {
            let res = await axios.get('/api/' + this.route + '?' + qs.stringify(object), {
                signal: this._abortController.signal
            });
            this.meta.total = res.data.meta?.total ?? res.data.total;
            this.meta.first = (this.meta.page - 1) * this.meta.limit;
            this.items.value = res.data.data;
            this.isLoading.value = false;
        } catch (ex) {
            if (axios.isCancel(ex)) {
                return;
            }
            this.isLoading.value = false;
            notyf.error(ex.response.data.message);
        }
    };

    handlePaginator = (ev) => {
        this.meta.limit = ev.rows;
        this.meta.page = ev.page + 1;
        this.getData();
    };
}

class ApiMeta {
    constructor() {
        this.limit = 10;
        this.page = 1;
        this.total = 0;
        this.first = 1;
    }
}
