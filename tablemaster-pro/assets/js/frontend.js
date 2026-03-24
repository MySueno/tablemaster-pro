/**
 * TableMaster Pro - Frontend JavaScript
 * Vanilla JS, no dependencies.
 */
(function () {
    'use strict';

    var DEBOUNCE_DELAY = 300;

    function debounce(fn, delay) {
        var timer;
        return function () {
            var args = arguments;
            var ctx = this;
            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(ctx, args);
            }, delay);
        };
    }

    function TableMasterInstance(wrapper) {
        this.wrapper       = wrapper;
        this.tableId       = wrapper.getAttribute('data-table-id');
        this.perPage       = parseInt(wrapper.getAttribute('data-per-page'), 10) || 10;
        this.collapsible   = wrapper.getAttribute('data-collapsible') === '1';
        this.mobileMode    = wrapper.getAttribute('data-mobile-mode') || 'scroll';
        this.defaultSortCol= wrapper.getAttribute('data-default-sort-col') || '';
        this.defaultSortDir= wrapper.getAttribute('data-default-sort-dir') || 'asc';

        this.table     = wrapper.querySelector('.tmp-table');
        this.tbody     = wrapper.querySelector('.tmp-tbody');
        this.thead     = wrapper.querySelector('thead');
        this.infoEl    = wrapper.querySelector('.tmp-info-text');
        this.paginationEl = wrapper.querySelector('.tmp-pagination');
        this.searchEl  = wrapper.querySelector('.tmp-search');
        this.ppSelect  = wrapper.querySelector('.tmp-per-page-select');

        this.searchTerm    = '';
        this.colFilters    = {};
        this.sortColIndex  = null;
        this.sortDir       = 'asc';
        this.currentPage   = 1;

        this.allRows  = [];
        this.headers  = [];

        this.init();
    }

    TableMasterInstance.prototype.init = function () {
        var self = this;

        // Collect headers
        this.headers = Array.prototype.slice.call(this.thead.querySelectorAll('.tmp-th'));

        // Collect all rows
        this.allRows = Array.prototype.slice.call(this.tbody.querySelectorAll('.tmp-row'));

        // Build parent-child map
        this.buildTree();

        // Collapsible groups
        if (this.collapsible) {
            this.initCollapsible();
        }

        // Search
        if (this.searchEl) {
            this.searchEl.addEventListener('input', debounce(function () {
                self.searchTerm = self.searchEl.value.trim().toLowerCase();
                self.currentPage = 1;
                self.applyFilters();
            }, DEBOUNCE_DELAY));
        }

        // Per-page selector
        if (this.ppSelect) {
            this.ppSelect.addEventListener('change', function () {
                self.perPage = parseInt(self.ppSelect.value, 10);
                self.currentPage = 1;
                self.renderPagination();
                self.applyPagination();
            });
        }

        // Sorting
        var sortableHeaders = this.thead.querySelectorAll('.tmp-th.tmp-sortable');
        sortableHeaders.forEach(function (th) {
            th.addEventListener('click', function () {
                self.handleSort(th);
            });
            th.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    self.handleSort(th);
                }
            });
        });

        // Column filters
        var colFilterEls = this.wrapper.querySelectorAll('.tmp-col-filter');
        colFilterEls.forEach(function (sel) {
            self.populateColFilter(sel);
            sel.addEventListener('change', function () {
                self.colFilters[sel.getAttribute('data-col-id')] = sel.value;
                self.currentPage = 1;
                self.applyFilters();
            });
        });

        // Default sort
        if (this.defaultSortCol !== '') {
            var colIdx = parseInt(this.defaultSortCol, 10);
            var sortableHeader = this.headers[colIdx] || null;
            if (sortableHeader && sortableHeader.classList.contains('tmp-sortable')) {
                this.sortColIndex = colIdx;
                this.sortDir      = this.defaultSortDir;
                this.applySortClass(sortableHeader);
                this.sortRows();
            }
        }

        this.applyFilters();
    };

    TableMasterInstance.prototype.buildTree = function () {
        var self = this;
        this.rowMap = {};
        this.allRows.forEach(function (row) {
            self.rowMap[row.getAttribute('data-row-id')] = row;
        });
    };

    TableMasterInstance.prototype.initCollapsible = function () {
        var self = this;
        this.allRows.forEach(function (row) {
            var rowType = row.getAttribute('data-row-type');
            var isGroup = rowType === 'group_1' || rowType === 'group_2' || rowType === 'group_3';
            if (!isGroup) return;

            var btn = row.querySelector('.tmp-toggle-btn');
            if (!btn) return;

            // Apply initial collapsed state
            var isCollapsed = row.getAttribute('data-collapsed') === '1';
            if (isCollapsed) {
                self.setGroupCollapsed(row, true);
            }

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var currentlyCollapsed = row.getAttribute('data-collapsed') === '1';
                self.setGroupCollapsed(row, !currentlyCollapsed);
            });
        });
    };

    TableMasterInstance.prototype.setGroupCollapsed = function (groupRow, collapse) {
        var rowId   = groupRow.getAttribute('data-row-id');
        var rowType = groupRow.getAttribute('data-row-type');
        var btn     = groupRow.querySelector('.tmp-toggle-btn');
        var icon    = groupRow.querySelector('.tmp-toggle-icon');
        var self    = this;

        groupRow.setAttribute('data-collapsed', collapse ? '1' : '0');
        if (btn) btn.setAttribute('aria-expanded', collapse ? 'false' : 'true');
        if (icon) icon.textContent = collapse ? '▶' : '▼';

        // Hide/show direct and nested children
        self.allRows.forEach(function (row) {
            var parentId = row.getAttribute('data-parent-id');
            if (parentId === rowId) {
                if (collapse) {
                    row.classList.add('tmp-row-hidden');
                    // Also collapse children of children
                    var childType = row.getAttribute('data-row-type');
                    if (childType !== 'data') {
                        self.setGroupCollapsed(row, true);
                    }
                } else {
                    row.classList.remove('tmp-row-hidden');
                    // Restore based on their own collapsed state
                    var childCollapsed = row.getAttribute('data-collapsed') === '1';
                    if (!childCollapsed) {
                        // Only show their children if not themselves collapsed
                    }
                }
            }
        });
    };

    TableMasterInstance.prototype.getVisibleDataRows = function () {
        return this.allRows.filter(function (row) {
            return row.getAttribute('data-row-type') === 'data' &&
                   !row.classList.contains('tmp-row-filtered');
        });
    };

    TableMasterInstance.prototype.applyFilters = function () {
        var self      = this;
        var term      = this.searchTerm;
        var colFilters= this.colFilters;
        var hasFilter = term.length > 0 || Object.values(colFilters).some(function (v) { return v !== ''; });

        if (!hasFilter) {
            this.allRows.forEach(function (row) {
                row.classList.remove('tmp-row-filtered');
            });
            this.rebuildZebra();
            this.renderPagination();
            this.applyPagination();
            return;
        }

        // Mark data rows as filtered or not
        var matchedRowIds = {};
        this.allRows.forEach(function (row) {
            var rowType = row.getAttribute('data-row-type');
            if (rowType !== 'data') return;

            var cells = row.querySelectorAll('.tmp-td');
            var rowText = '';
            var colMatch = true;

            cells.forEach(function (td) {
                var colId  = td.getAttribute('data-col-id');
                var text   = td.textContent.toLowerCase().trim();
                rowText   += text + ' ';
                if (colFilters[colId] && colFilters[colId] !== '') {
                    if (text !== colFilters[colId].toLowerCase()) {
                        colMatch = false;
                    }
                }
            });

            var termMatch = term === '' || rowText.indexOf(term) !== -1;

            if (termMatch && colMatch) {
                row.classList.remove('tmp-row-filtered');
                matchedRowIds[row.getAttribute('data-row-id')] = true;
                // Bubble up parents
                var pid = row.getAttribute('data-parent-id');
                while (pid) {
                    matchedRowIds[pid] = true;
                    var parentRow = self.rowMap[pid];
                    if (parentRow) {
                        pid = parentRow.getAttribute('data-parent-id');
                    } else {
                        pid = null;
                    }
                }
            } else {
                row.classList.add('tmp-row-filtered');
            }
        });

        // Filter group rows
        this.allRows.forEach(function (row) {
            var rowType = row.getAttribute('data-row-type');
            if (rowType === 'data') return;
            var rowId = row.getAttribute('data-row-id');
            if (matchedRowIds[rowId]) {
                row.classList.remove('tmp-row-filtered');
                row.classList.remove('tmp-row-hidden');
            } else {
                row.classList.add('tmp-row-filtered');
            }
        });

        this.rebuildZebra();
        this.currentPage = 1;
        this.renderPagination();
        this.applyPagination();
    };

    TableMasterInstance.prototype.rebuildZebra = function () {
        var idx = 0;
        this.allRows.forEach(function (row) {
            if (row.getAttribute('data-row-type') === 'data') {
                row.classList.remove('tmp-odd', 'tmp-even');
                row.classList.add(idx % 2 === 0 ? 'tmp-odd' : 'tmp-even');
                idx++;
            }
        });
    };

    TableMasterInstance.prototype.handleSort = function (th) {
        var colIdx = this.headers.indexOf(th);
        if (colIdx === -1) return;

        if (this.sortColIndex === colIdx) {
            this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColIndex = colIdx;
            this.sortDir = 'asc';
        }

        this.headers.forEach(function (h) {
            h.classList.remove('tmp-sort-asc', 'tmp-sort-desc');
            h.setAttribute('aria-sort', 'none');
        });

        this.applySortClass(th);
        this.sortRows();
        this.rebuildZebra();
        this.currentPage = 1;
        this.renderPagination();
        this.applyPagination();
    };

    TableMasterInstance.prototype.applySortClass = function (th) {
        th.classList.add(this.sortDir === 'asc' ? 'tmp-sort-asc' : 'tmp-sort-desc');
        th.setAttribute('aria-sort', this.sortDir === 'asc' ? 'ascending' : 'descending');
    };

    TableMasterInstance.prototype.sortRows = function () {
        if (this.sortColIndex === null) return;

        var self      = this;
        var colIdx    = this.sortColIndex;
        var colType   = (this.headers[colIdx] || {}).getAttribute ? this.headers[colIdx].getAttribute('data-col-type') : 'text';
        var dir       = this.sortDir === 'asc' ? 1 : -1;
        var collapsible = this.collapsible;

        // Adjust colIdx for toggle column
        var actualCellIdx = collapsible ? colIdx + 1 : colIdx;

        // Group rows by parent to sort within groups
        var groups = {}; // parentId => [rows]
        this.allRows.forEach(function (row) {
            var pid = row.getAttribute('data-parent-id') || '__root__';
            if (!groups[pid]) groups[pid] = [];
            groups[pid].push(row);
        });

        // Sort data rows within each group
        Object.keys(groups).forEach(function (pid) {
            var groupRows = groups[pid];
            var dataRows  = groupRows.filter(function (r) { return r.getAttribute('data-row-type') === 'data'; });
            var nonData   = groupRows.filter(function (r) { return r.getAttribute('data-row-type') !== 'data'; });

            dataRows.sort(function (a, b) {
                var cellA = a.querySelectorAll('td')[actualCellIdx];
                var cellB = b.querySelectorAll('td')[actualCellIdx];
                var va = cellA ? cellA.textContent.trim() : '';
                var vb = cellB ? cellB.textContent.trim() : '';

                if (colType === 'number') {
                    return dir * (parseFloat(va) - parseFloat(vb));
                }
                if (colType === 'date') {
                    return dir * (new Date(va) - new Date(vb));
                }
                return dir * va.localeCompare(vb, undefined, { numeric: true });
            });

            // Re-append: non-data rows first (group headers stay in position), then sorted data
            nonData.concat(dataRows).forEach(function (row) {
                self.tbody.appendChild(row);
            });
        });
    };

    TableMasterInstance.prototype.applyPagination = function () {
        var visibleRows = this.allRows.filter(function (row) {
            return !row.classList.contains('tmp-row-filtered') &&
                   !row.classList.contains('tmp-row-hidden');
        });

        var dataRows = visibleRows.filter(function (row) {
            return row.getAttribute('data-row-type') === 'data';
        });

        var total = dataRows.length;

        if (this.perPage === -1) {
            visibleRows.forEach(function (row) {
                row.style.display = '';
            });
            this.updateInfo(total, total, 1, 1);
            return;
        }

        var totalPages = Math.max(1, Math.ceil(total / this.perPage));
        if (this.currentPage > totalPages) this.currentPage = totalPages;

        var start = (this.currentPage - 1) * this.perPage;
        var end   = start + this.perPage;

        var dataShown = 0;
        var showing   = 0;
        visibleRows.forEach(function (row) {
            var isData = row.getAttribute('data-row-type') === 'data';
            if (isData) {
                if (dataShown >= start && dataShown < end) {
                    row.style.display = '';
                    showing++;
                } else {
                    row.style.display = 'none';
                }
                dataShown++;
            } else {
                // Group header: show if any of its children would be shown on this page
                row.style.display = '';
            }
        });

        this.updateInfo(showing, total, this.currentPage, totalPages);
    };

    TableMasterInstance.prototype.updateInfo = function (showing, total, page, totalPages) {
        if (!this.infoEl) return;
        if (total === 0) {
            this.infoEl.textContent = 'Geen resultaten gevonden.';
            return;
        }
        var start = ((page - 1) * (this.perPage === -1 ? total : this.perPage)) + 1;
        var end   = Math.min(start + (this.perPage === -1 ? total : this.perPage) - 1, total);
        this.infoEl.textContent = start + '–' + end + ' van ' + total + ' resultaten';
    };

    TableMasterInstance.prototype.renderPagination = function () {
        if (!this.paginationEl) return;
        this.paginationEl.innerHTML = '';

        var dataRows = this.allRows.filter(function (row) {
            return row.getAttribute('data-row-type') === 'data' &&
                   !row.classList.contains('tmp-row-filtered');
        });

        var total = dataRows.length;
        if (this.perPage === -1 || total === 0) return;

        var totalPages = Math.max(1, Math.ceil(total / this.perPage));
        if (totalPages <= 1) return;

        var self = this;

        function makeBtn(label, page, disabled, active) {
            var btn = document.createElement('button');
            btn.className = 'tmp-page-btn' + (active ? ' tmp-active' : '');
            btn.textContent = label;
            btn.setAttribute('aria-label', 'Pagina ' + page);
            if (disabled) {
                btn.disabled = true;
            } else {
                btn.addEventListener('click', function () {
                    self.currentPage = page;
                    self.renderPagination();
                    self.applyPagination();
                });
            }
            return btn;
        }

        this.paginationEl.appendChild(makeBtn('‹', this.currentPage - 1, this.currentPage === 1, false));

        var windowSize = 2;
        for (var i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= this.currentPage - windowSize && i <= this.currentPage + windowSize)) {
                this.paginationEl.appendChild(makeBtn(i, i, false, i === this.currentPage));
            } else if (i === this.currentPage - windowSize - 1 || i === this.currentPage + windowSize + 1) {
                var ellipsis = document.createElement('span');
                ellipsis.textContent = '…';
                ellipsis.className = 'tmp-page-ellipsis';
                this.paginationEl.appendChild(ellipsis);
            }
        }

        this.paginationEl.appendChild(makeBtn('›', this.currentPage + 1, this.currentPage === totalPages, false));
    };

    TableMasterInstance.prototype.populateColFilter = function (sel) {
        var colId = sel.getAttribute('data-col-id');
        var values = new Set();

        this.allRows.forEach(function (row) {
            if (row.getAttribute('data-row-type') !== 'data') return;
            var td = row.querySelector('.tmp-td[data-col-id="' + colId + '"]');
            if (td) {
                var v = td.textContent.trim();
                if (v) values.add(v);
            }
        });

        var sorted = Array.from(values).sort();
        sorted.forEach(function (v) {
            var opt = document.createElement('option');
            opt.value = v;
            opt.textContent = v;
            sel.appendChild(opt);
        });
    };

    // Export functionality
    function initExport(wrapper) {
        var btns = wrapper.querySelectorAll('.tmp-export-btn');
        btns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var format = btn.getAttribute('data-format');
                if (format === 'csv') {
                    exportCSV(wrapper);
                } else if (format === 'print') {
                    window.print();
                }
            });
        });
    }

    function exportCSV(wrapper) {
        var table = wrapper.querySelector('.tmp-table');
        if (!table) return;

        var rows  = [];
        var ths   = table.querySelectorAll('thead .tmp-th');
        var hRow  = [];
        ths.forEach(function (th) {
            if (!th.classList.contains('tmp-toggle-col')) {
                hRow.push('"' + th.textContent.replace(/"/g, '""').trim() + '"');
            }
        });
        rows.push(hRow.join(','));

        table.querySelectorAll('tbody .tmp-row').forEach(function (row) {
            if (row.classList.contains('tmp-row-filtered')) return;
            if (row.style.display === 'none') return;
            var cols = [];
            row.querySelectorAll('.tmp-td').forEach(function (td) {
                if (!td.classList.contains('tmp-toggle-cell')) {
                    cols.push('"' + td.textContent.replace(/"/g, '""').trim() + '"');
                }
            });
            rows.push(cols.join(','));
        });

        var csv  = rows.join('\r\n');
        var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href   = url;
        a.download = 'tabel-export.csv';
        a.click();
        URL.revokeObjectURL(url);
    }

    // Init all tables on page
    function initAll() {
        var wrappers = document.querySelectorAll('.tmp-wrapper');
        wrappers.forEach(function (wrapper) {
            try {
                new TableMasterInstance(wrapper);
                initExport(wrapper);
            } catch (e) {
                console.error('TableMaster init error:', e);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

})();
