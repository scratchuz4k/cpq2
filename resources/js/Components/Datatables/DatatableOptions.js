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

import { v4 as uuid } from 'uuid';

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
        this.sort = { order: 'desc', orderBy: 'updated_at' };
        this.allowedActions = { create: false, update: false, destroy: false, link: false, export: false };
    }

    /**
     * Adds a column to the datatable
     * @param {string} key
     * @param {string} title
     * @param {object} style
     */
    addColumn(column, title, style = {}) {
        this.columns.push({ column: column, title: title, style: style });
        this.filters.push({ column: column, value: null, operator: FilterOperators.CONTAINS });
    }

    /**
     * Adds a filter to querystring in the url as a parameter
     * @param {string} column
     * @param {FilterOperators} operator
     * @param {string} value
     * @param {('where'|'orWhere')} condition
     */
    addQuery(column, operator, value, condition = 'where') {
        this.query.push({ column: column, operator: operator, value: value, function: condition });
    }

    /**
     * Adds a filter to querystring in the url as a parameter
     * @param {string} key
     * @param {string} column
     * @param {FilterOperators} operator
     * @param {string} value
     * @param {('where'|'orWhere')} condition
     */
    addGroupQuery(key, column, operator, value, condition = 'orWhere') {
        let index = this.query.findIndex((q) => q.key === key);
        if (index === -1) {
            this.query[index] = [{ key: key, group: [] }];
            return;
        }
        this.query[index].group.push({ column: column, operator: operator, value: value, function: condition });
    }

    /**
     * Adds a filter to columns in the datatable
     * @param {string} column - The column name to add the filter to
     * @param {('text'|'select'|'checkbox')} type - The type of filter: 'text', 'select', or 'checkbox'
     * @param {array} options - Options for the filter (used for 'select' type)
     */

    addColumnFilter(column, type, options = []) {
        this.columnFilters[column] = { type: type, options: options };
    }

    /**
     * Sets the sorting for the datatable
     * @param {('asc'|'desc')} order - The order of sorting: 'asc' or 'desc'
     * @param {string} orderBy - The column to sort by
     */
    setSort(order, orderBy) {
        this.sort = { orderBy: orderBy, order: order };
    }

    /**
     * Sets the allowed actions for the datatable
     * @param {boolean} create - Whether the create action is allowed
     * @param {boolean} update - Whether the update action is allowed
     * @param {boolean} destroy - Whether the destroy action is allowed
     * @param {boolean} link - Whether the link action is allowed
     * @param {boolean} hasExport - Whether the export action is allowed
     */
    setAllowedActions(create = false, update = false, destroy = false, link = false, hasExport = false) {
        this.allowedActions = { create, update, destroy, link, export: hasExport };
    }
}
