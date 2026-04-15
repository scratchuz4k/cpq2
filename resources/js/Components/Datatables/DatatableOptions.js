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
        this.sort = { order: 'desc', orderBy: 'updated_at' };
        this.allow = { create: false, update: false, destroy: false, link: false, export: false };
    }

    /**
     * Adds a column to the datatable
     * @param {string} key
     * @param {string} title
     * @param {object} style
     * @param {boolean} isHidden
     * @param {boolean} isSticky
     * @param {boolean} isSearchable
     * @param {boolean} isSortable
     */
    addColumn(
        column,
        title,
        style = {},
        { isHidden = false, isSticky = false, isSearchable = true, isSortable = true } = {}
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
     * @param {boolean} allowExport - Whether the export action is allowed
     */
    setAllowedActions({ create = false, update = false, destroy = false, link = false, allowExport = false } = {}) {
        this.allow = { create, update, destroy, link, export: allowExport };
    }
}
