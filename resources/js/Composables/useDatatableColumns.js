import { computed, reactive, ref } from 'vue';

const DEFAULT_COL_WIDTH = 150;
const MIN_AUTO_WIDTH = 80;
const MAX_AUTO_WIDTH = 400;

export function useDatatableColumns(tableId, columns) {
    const STORAGE_KEY = 'datatable_' + tableId;

    const loadState = () => JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');

    const saveState = (patch) => {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({ ...loadState(), ...patch }));
    };

    const saved = loadState();

    // ── Hidden columns ────────────────────────────────────────────────────────

    const hiddenColumns = ref(saved.hiddenColumns ?? []);

    const isUserHidden = (col) => hiddenColumns.value.includes(col.column);

    const toggleHidden = (col) => {
        const idx = hiddenColumns.value.indexOf(col.column);
        if (idx > -1) hiddenColumns.value.splice(idx, 1);
        else hiddenColumns.value.push(col.column);
        saveState({ hiddenColumns: hiddenColumns.value });
    };

    // ── Column widths & resize ────────────────────────────────────────────────

    const colWidths = ref(saved.colWidths ?? {});
    const hasCustomWidths = computed(() => Object.keys(colWidths.value).length > 0);

    const getColWidth = (col) => colWidths.value[col.column] ?? DEFAULT_COL_WIDTH;

    // One-time initial layout: only runs while colWidths is completely empty (this table has
    // never been resized or auto-sized before), so it never fights a user's manual resize later.
    const applyMeasuredWidths = (measurements) => {
        if (hasCustomWidths.value) return;

        const clamped = {};
        Object.entries(measurements).forEach(([column, width]) => {
            clamped[column] = Math.min(MAX_AUTO_WIDTH, Math.max(MIN_AUTO_WIDTH, Math.round(width)));
        });
        colWidths.value = clamped;
        saveState({ colWidths: colWidths.value });
    };

    let resizing = null;

    const startResize = (e, col) => {
        e.preventDefault();
        e.stopPropagation();
        resizing = { col, startX: e.clientX, startWidth: getColWidth(col) };
        document.addEventListener('mousemove', onResize);
        document.addEventListener('mouseup', stopResize);
    };

    const onResize = (e) => {
        if (!resizing) return;
        const newWidth = Math.max(50, resizing.startWidth + (e.clientX - resizing.startX));
        colWidths.value[resizing.col.column] = newWidth;
        saveState({ colWidths: colWidths.value });
    };

    const stopResize = () => {
        resizing = null;
        document.removeEventListener('mousemove', onResize);
        document.removeEventListener('mouseup', stopResize);
    };

    // ── Column ordering & drag-drop ───────────────────────────────────────────

    const columnOrder = ref(
        saved.columnOrder
            ? columns.slice().sort((a, b) => {
                  const ai = saved.columnOrder.indexOf(a.column);
                  const bi = saved.columnOrder.indexOf(b.column);
                  return (ai === -1 ? 999 : ai) - (bi === -1 ? 999 : bi);
              })
            : columns.slice()
    );

    const saveColumnOrder = () => saveState({ columnOrder: columnOrder.value.map((c) => c.column) });

    const dragState = reactive({ fromIndex: null });

    const onDragStart = (index) => {
        dragState.fromIndex = index;
    };

    const onDragOver = (e, index) => {
        e.preventDefault();
        if (dragState.fromIndex === null || dragState.fromIndex === index) return;
        const col = columnOrder.value[dragState.fromIndex];
        if (col.isSticky) return;
        if (columnOrder.value[index].isSticky) return;
        columnOrder.value.splice(dragState.fromIndex, 1);
        columnOrder.value.splice(index, 0, col);
        dragState.fromIndex = index;
    };

    const onDrop = () => {
        dragState.fromIndex = null;
        saveColumnOrder();
    };

    // ── Derived ───────────────────────────────────────────────────────────────

    const visibleColumns = computed(() => columnOrder.value.filter((c) => !c.isHidden && !isUserHidden(c)));

    const stickyCount = computed(() => columnOrder.value.filter((c) => c.isSticky).length);

    const stickyLeft = (col, visIndex) => {
        if (!col.isSticky) return 0;
        return visibleColumns.value
            .slice(0, visIndex)
            .filter((c) => c.isSticky)
            .reduce((sum, c) => sum + getColWidth(c), 0);
    };

    return {
        // hidden columns
        hiddenColumns,
        isUserHidden,
        toggleHidden,
        // widths & resize
        colWidths,
        hasCustomWidths,
        getColWidth,
        applyMeasuredWidths,
        startResize,
        // ordering & drag
        columnOrder,
        dragState,
        onDragStart,
        onDragOver,
        onDrop,
        // derived
        visibleColumns,
        stickyCount,
        stickyLeft
    };
}
