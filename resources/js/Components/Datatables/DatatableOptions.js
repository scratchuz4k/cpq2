import { v4 as uuid } from 'uuid';
import { FilterOperators } from '@/Composables/useApi';
export class DatatableOptions {
    /**
     *
     * @param {string} id
     * @param {string} route
     */
    constructor(route, id = null) {
        this.id = id ? id : uuid();
        this.route = route;
        this.filters = [];
        this.columns = [];
        this.columnFilters = {};
        this.query = [];
        this.groupedfilters = []; // item 5: was missing, addGroupQuery crashed on first call
        this.sort = { order: 'desc', orderBy: 'updated_at' };
        this.allow = { create: false, update: false, destroy: false, link: false, export: false, duplicate: false };
    }

    /**
     * Adds a column to the datatable
     * @param {string} column
     * @param {string} title
     * @param {{ style?: object, isHidden?: boolean, isSticky?: boolean, isSearchable?: boolean, isSortable?: boolean }} options
     * @returns {DatatableOptions}
     */
    addColumn(
        column,
        title,
        { style = {}, isHidden = false, isSticky = false, isSearchable = true, isSortable = true } = {}
    ) {
        this.columns.push({
            column: column,
            title: title,
            style: style,
            isHidden: isHidden,
            isSticky: isSticky,
            isSearchable: isSearchable,
            isSortable: isSortable
        });
        this.filters.push({ column: column, value: null, operator: FilterOperators.CONTAINS });
        return this;
    }

    /**
     * Adds a filter to querystring in the url as a parameter
     * @param {string} column
     * @param {FilterOperators} operator
     * @param {string} value
     * @param {('where'|'orWhere')} condition
     * @returns {DatatableOptions}
     */
    addQuery(column, operator, value, condition = 'where') {
        this.query.push({ column: column, operator: operator, value: value, function: condition });
        return this;
    }

    /**
     * Clears all filters previously added via addQuery
     * @returns {DatatableOptions}
     */
    clearQuery() {
        this.query = [];
        return this;
    }

    /**
     * Adds a grouped filter to querystring in the url as a parameter
     * @param {string} key
     * @param {string} column
     * @param {FilterOperators} operator
     * @param {string} value
     * @param {('where'|'orWhere')} condition
     * @returns {DatatableOptions}
     */
    addGroupQuery(key, column, operator, value, condition = 'orWhere') {
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
        return this;
    }

    /**
     * Configures a custom widget for a column's filter-panel row, instead of the default text input.
     * @param {string} column - The column name to add the filter to
     * @param {('text'|'select'|'multiselect'|'checkbox')} type
     * @param {array} [options] - Static `{label, value}` (or plain value) options. Omit for
     *  'select'/'multiselect' to source live options from the response's facet counts instead.
     * @param {FilterOperators} [operator] - Defaults to 'contains' for text, 'in' for multiselect, 'equals' otherwise.
     * @returns {DatatableOptions}
     */
    addColumnFilter(column, type, options = [], operator = null) {
        const defaultOperator = type === 'text' ? 'contains' : type === 'multiselect' ? 'in' : 'equals';
        this.columnFilters[column] = { type: type, options: options, operator: operator ?? defaultOperator };
        return this;
    }

    /**
     * Sets the sorting for the datatable
     * @param {('asc'|'desc')} order - The order of sorting: 'asc' or 'desc'
     * @param {string} orderBy - The column to sort by
     * @returns {DatatableOptions}
     */
    setSort(order, orderBy) {
        this.sort = { orderBy: orderBy, order: order };
        return this;
    }

    /**
     * Sets the allowed actions for the datatable
     * @param {boolean} create - Whether the create action is allowed
     * @param {boolean} update - Whether the update action is allowed
     * @param {boolean} destroy - Whether the destroy action is allowed
     * @param {boolean} link - Whether the link action is allowed
     * @param {boolean} allowExport - Whether the export action is allowed
     * @param {Function|false} duplicate - Callback fn receiving item id, or false to disable
     * @returns {DatatableOptions}
     */
    setAllowedActions({
        create = false,
        update = false,
        destroy = false,
        link = false,
        allowExport = false,
        duplicate = false
    } = {}) {
        this.allow = { create, update, destroy, link, export: allowExport, duplicate };
        return this;
    }
}
