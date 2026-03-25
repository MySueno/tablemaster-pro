/**
 * TableMaster Pro - Admin JavaScript
 * Requires: jQuery, jQuery UI Sortable, wp-color-picker
 */
(function ($) {
    'use strict';

    var cfg        = window.tableMasterAdmin || {};
    var ajaxurl    = cfg.ajaxurl   || '';
    var nonce      = cfg.nonce     || '';
    var tableId    = cfg.table_id  || 0;
    var settings   = cfg.settings  || {};
    var presets    = cfg.presets   || {};
    var listUrl    = cfg.list_url  || '';
    var lang       = cfg.lang      || '';
    var i18n       = cfg.i18n      || {};

    var columns    = [];   // [{id, label, type, settings:{width,align,sortable,filterable}}, ...]
    var rows       = [];   // [{id, temp_id, row_type, parent_id, parent_temp_id, cells:{col_key: content}, is_collapsed}, ...]
    var colTempIdx    = 0;
    var rowTempIdx    = 0;
    var isDirty       = false;
    var justDraggedKey = null;
    var activeCell    = null;

    /* ===== BOOT ===== */
    $(document).ready(function () {
        initTabs();
        initColorPickers();
        initPresetButtons();
        initColumnSortable();

        if (tableId) {
            loadStructure();
        }

        bindEvents();
    });

    /* ===== TABS ===== */
    function initTabs() {
        $('.tmp-tab').on('click', function () {
            var tab = $(this).data('tab');
            $('.tmp-tab').removeClass('active');
            $('.tmp-tab-content').removeClass('active');
            $(this).addClass('active');
            $('#tmp-tab-' + tab).addClass('active');
        });
    }

    /* ===== COLOR PICKERS ===== */
    function initColorPickers() {
        var colors = (settings.colors) || {};

        $('.tmp-color-picker').each(function () {
            var $input = $(this);
            var key    = $input.data('color-key');
            if (colors[key]) {
                $input.val(colors[key]);
                $input.attr('data-default-color', colors[key]);
            }

            $input.wpColorPicker({
                change: function (event, ui) {
                    var newColor = ui.color.toString();
                    $input.val(newColor);
                    updatePreview();
                    isDirty = true;
                },
                clear: function () {
                    updatePreview();
                    isDirty = true;
                }
            });
        });

        updatePreview();
    }

    function getColorValues() {
        var colors = {};
        $('.tmp-color-picker').each(function () {
            var key = $(this).data('color-key');
            colors[key] = $(this).val() || $(this).attr('data-default-color') || '#ffffff';
        });
        return colors;
    }

    /* ===== LIVE PREVIEW (applies colors to admin row table) ===== */
    function updatePreview() {
        var c = getColorValues();

        var $adminTable = $('.tmp-admin-table');
        if ($adminTable.length === 0) return;

        $adminTable.find('thead th').css({ background: c.header_bg, color: c.header_text, borderColor: 'rgba(255,255,255,0.15)' });

        $adminTable.find('.tmp-admin-row-group_1').css({ background: c.group1_bg, color: c.group1_text });
        $adminTable.find('.tmp-admin-row-group_2').css({ background: c.group2_bg, color: c.group2_text });
        $adminTable.find('.tmp-admin-row-group_3').css({ background: c.group3_bg, color: c.group3_text });
        $adminTable.find('.tmp-admin-row-footer').css({ background: c.footer_bg, color: c.footer_text });

        var dataIdx = 0;
        $adminTable.find('.tmp-admin-row-data').each(function () {
            var bg = dataIdx % 2 === 0 ? c.odd_bg : c.even_bg;
            $(this).css({ background: bg });
            dataIdx++;
        });

        $adminTable.find('td').css({ borderColor: c.border_color || '#e0e0e0' });
    }

    /* ===== PRESETS (removed — colors are now fully custom) ===== */
    function initPresetButtons() {
    }

    /* ===== COLUMNS ===== */
    function initColumnSortable() {
        // Column sorting is no longer needed — columns are managed via table headers
    }

    function addColumn(colData) {
        var tempKey = 'new_' + (++colTempIdx);
        var col = $.extend({
            id:       0,
            temp_key: tempKey,
            label:    (i18n.add_column || 'Kolom') + ' ' + (columns.length + 1),
            type:     'text',
            settings: { width: 'auto', align: 'left', sortable: true, filterable: true, header_group1: '', header_group2: '' }
        }, colData || {});
        if (!col.settings.header_group1) col.settings.header_group1 = '';
        if (!col.settings.header_group2) col.settings.header_group2 = '';

        columns.push(col);
        rebuildRowTable();
        isDirty = true;
    }

    function renderColumnItem(col) {
        // No-op: columns are now managed inline in the row table headers
    }

    function syncColumnsFromDOM() {
        // Columns are synced directly via the popover — no DOM scan needed
    }

    /* ===== ROWS ===== */
    function addRow(rowType, parentTempId) {
        if (columns.length === 0) {
            alert(i18n.no_columns);
            return;
        }
        var tempId = 'new_row_' + (++rowTempIdx);
        var cells  = {};
        columns.forEach(function (col) {
            cells[col.temp_key || col.id] = '';
        });

        var row = {
            id:             0,
            temp_id:        tempId,
            row_type:       rowType || 'data',
            parent_id:      0,
            parent_temp_id: parentTempId || '',
            is_collapsed:   false,
            cells:          cells,
        };
        rows.push(row);
        rebuildRowTable();
        isDirty = true;
    }

    function rebuildRowTable() {
        activeCell = null;
        $('#tmp-cell-toolbar').addClass('tmp-toolbar-disabled');
        $('#tmp-tb-cell-ref').text('');
        hideMergeToolbar();
        var $wrapper = $('#tmp-rows-wrapper');
        $wrapper.find('.tmp-rows-empty').hide();
        $wrapper.find('.tmp-admin-table-wrap').remove();

        if (columns.length === 0) {
            $wrapper.find('.tmp-rows-empty').text(i18n.no_columns || 'Klik op "+ Kolom" om te beginnen.').show();
            return;
        }

        if (rows.length === 0) {
            $wrapper.find('.tmp-rows-empty').text('Nog geen rijen. Voeg rijen toe met de knoppen hierboven.').show();
        }

        var headerCols = columns.map(function (col, ci) {
            var key = col.temp_key || col.id;
            var groupLabel = col.settings.header_group1 ? ' <span class="tmp-col-group-badge" title="Groep: ' + escAttr(col.settings.header_group1) + '">' + escHtml(col.settings.header_group1) + '</span>' : '';
            return '<th class="tmp-col-header-cell" data-col-key="' + escAttr(key + '') + '" data-col-idx="' + ci + '" draggable="true">' +
                '<span class="tmp-col-header-label">' + escHtml(col.label || 'Kolom') + '</span>' +
                groupLabel +
                '<span class="tmp-col-delete-btn dashicons dashicons-no-alt" title="Kolom verwijderen"></span>' +
            '</th>';
        }).join('');

        var $table = $('<table class="tmp-admin-table"></table>');
        var $thead = $('<thead><tr><th style="width:28px;"></th><th style="width:80px;">Type</th>' + headerCols + '<th style="width:36px;"></th></tr></thead>');
        var $tbody = $('<tbody class="tmp-rows-sortable"></tbody>');

        $table.append($thead).append($tbody);

        rows.forEach(function (row) {
            var $tr = buildRowTr(row);
            $tbody.append($tr);
        });

        var $wrap = $('<div class="tmp-admin-table-wrap"></div>').append($table);
        $wrapper.append($wrap);

        $thead.find('.tmp-col-delete-btn').on('click', function (e) {
            e.stopPropagation();
            var $th = $(this).closest('.tmp-col-header-cell');
            var colKey = $th.data('col-key') + '';
            if (confirm('Kolom verwijderen?')) {
                columns = columns.filter(function(c) { return (c.temp_key || c.id) + '' !== colKey; });
                isDirty = true;
                rebuildRowTable();
            }
        });

        $thead.find('.tmp-col-header-cell').on('click', function (e) {
            e.stopPropagation();
            if ($(e.target).hasClass('tmp-col-delete-btn')) return;
            if (justDraggedKey) {
                justDraggedKey = null;
                return;
            }
            var $th = $(this);
            var colKey = $th.data('col-key') + '';

            if (e.ctrlKey || e.metaKey || e.shiftKey) {
                $th.toggleClass('tmp-col-selected');
                updateMergeToolbar();
                return;
            }

            $thead.find('.tmp-col-header-cell').removeClass('tmp-col-selected');
            hideMergeToolbar();

            if ($th.find('.tmp-col-inline-edit').length) return;
            var col = columns.find(function(c) { return (c.temp_key || c.id) + '' === colKey; });
            if (!col) return;
            var origLabel = col.label;
            var $label = $th.find('.tmp-col-header-label');
            var $delBtn = $th.find('.tmp-col-delete-btn');
            var $badge = $th.find('.tmp-col-group-badge');
            $label.hide();
            $delBtn.hide();
            if ($badge.length) $badge.hide();
            var $input = $('<input type="text" class="tmp-col-inline-edit" value="' + escAttr(col.label) + '">');
            $th.append($input);
            $input.focus().select();
            $input.on('input', function () {
                col.label = $(this).val().trim();
                isDirty = true;
            });
            function finishEdit() {
                $label.text(col.label || 'Kolom').show();
                $delBtn.show();
                if ($badge.length) $badge.show();
                $input.remove();
            }
            $input.on('blur', finishEdit);
            $input.on('keydown', function (ev) {
                if (ev.key === 'Enter') { ev.preventDefault(); finishEdit(); }
                if (ev.key === 'Escape') { col.label = origLabel; finishEdit(); }
            });
        });

        $(document).off('click.colselect').on('click.colselect', function (e) {
            if (!$(e.target).closest('.tmp-col-header-cell, .tmp-merge-toolbar').length) {
                $('.tmp-col-header-cell').removeClass('tmp-col-selected');
                hideMergeToolbar();
            }
        });

        (function initColDrag() {
            var dragSrcIdx = null;

            $thead.find('.tmp-col-header-cell').on('dragstart', function (e) {
                dragSrcIdx = parseInt($(this).data('col-idx'), 10);
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                e.originalEvent.dataTransfer.setData('text/plain', dragSrcIdx);
                $(this).addClass('tmp-col-dragging');
            });

            $thead.find('.tmp-col-header-cell').on('dragover', function (e) {
                e.preventDefault();
                e.originalEvent.dataTransfer.dropEffect = 'move';
                var $th = $(this);
                $thead.find('.tmp-col-header-cell').removeClass('tmp-col-drag-over-left tmp-col-drag-over-right');
                var targetIdx = parseInt($th.data('col-idx'), 10);
                if (targetIdx < dragSrcIdx) {
                    $th.addClass('tmp-col-drag-over-left');
                } else if (targetIdx > dragSrcIdx) {
                    $th.addClass('tmp-col-drag-over-right');
                }
            });

            $thead.find('.tmp-col-header-cell').on('dragleave', function () {
                $(this).removeClass('tmp-col-drag-over-left tmp-col-drag-over-right');
            });

            $thead.find('.tmp-col-header-cell').on('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $thead.find('.tmp-col-header-cell').removeClass('tmp-col-drag-over-left tmp-col-drag-over-right tmp-col-dragging');
                var targetIdx = parseInt($(this).data('col-idx'), 10);
                if (dragSrcIdx === null || dragSrcIdx === targetIdx) return;
                var moved = columns.splice(dragSrcIdx, 1)[0];
                columns.splice(targetIdx, 0, moved);
                isDirty = true;
                justDraggedKey = $(this).data('col-key') + '';
                rebuildRowTable();
            });

            $thead.find('.tmp-col-header-cell').on('dragend', function () {
                $thead.find('.tmp-col-header-cell').removeClass('tmp-col-dragging tmp-col-drag-over-left tmp-col-drag-over-right');
                dragSrcIdx = null;
            });
        })();

        $tbody.sortable({
            handle: '.tmp-drag-handle',
            tolerance: 'pointer',
            update: function () {
                syncRowsFromDOM();
                isDirty = true;
            }
        });

        updatePreview();
    }

    function getSelectedColKeys() {
        var keys = [];
        $('.tmp-col-header-cell.tmp-col-selected').each(function () {
            keys.push($(this).data('col-key') + '');
        });
        return keys;
    }

    function updateMergeToolbar() {
        var keys = getSelectedColKeys();
        hideMergeToolbar();
        if (keys.length < 2) return;

        var $sel = $('.tmp-col-header-cell.tmp-col-selected').first();
        var $last = $('.tmp-col-header-cell.tmp-col-selected').last();
        var topOff = $sel.offset();
        var leftOff = topOff.left;
        var rightOff = $last.offset().left + $last.outerWidth();
        var centerX = leftOff + (rightOff - leftOff) / 2;

        var $bar = $('<div class="tmp-merge-toolbar">' +
            '<button type="button" class="button button-primary tmp-merge-btn" title="Kolommen samenvoegen">' +
                '<span class="dashicons dashicons-columns" style="vertical-align:middle;margin-right:4px;"></span>Samenvoegen (' + keys.length + ')' +
            '</button>' +
            '<button type="button" class="button tmp-unmerge-btn" title="Groepering opheffen" style="margin-left:4px;">' +
                'Opheffen' +
            '</button>' +
        '</div>');

        $('body').append($bar);
        var barW = $bar.outerWidth();
        var barLeft = centerX - barW / 2;
        if (barLeft < 8) barLeft = 8;
        $bar.css({
            top: topOff.top - $bar.outerHeight() - 8,
            left: barLeft,
        });

        $bar.find('.tmp-merge-btn').on('click', function () {
            var groupName = prompt('Naam voor de samengevoegde header:', '');
            if (groupName === null) return;
            groupName = groupName.trim();
            if (!groupName) { alert('Voer een groepnaam in.'); return; }
            keys.forEach(function (k) {
                var col = columns.find(function(c) { return (c.temp_key || c.id) + '' === k; });
                if (col) col.settings.header_group1 = groupName;
            });
            isDirty = true;
            hideMergeToolbar();
            rebuildRowTable();
        });

        $bar.find('.tmp-unmerge-btn').on('click', function () {
            keys.forEach(function (k) {
                var col = columns.find(function(c) { return (c.temp_key || c.id) + '' === k; });
                if (col) {
                    col.settings.header_group1 = '';
                    col.settings.header_group2 = '';
                }
            });
            isDirty = true;
            hideMergeToolbar();
            rebuildRowTable();
        });
    }

    function hideMergeToolbar() {
        $('.tmp-merge-toolbar').remove();
    }

    var rowTypeOrder = ['data', 'group_1', 'group_2', 'group_3', 'footer'];
    var rowTypeLabels = { data: 'Data', group_1: 'G1', group_2: 'G2', group_3: 'G3', footer: 'Afsluit' };

    function buildRowTr(row) {
        var typeClass = 'tmp-admin-row tmp-admin-row-' + (row.row_type || 'data');
        var badgeClass= 'tmp-row-type-badge tmp-type-badge-' + (row.row_type || 'data');
        var badgeText = rowTypeLabels[row.row_type] || 'Data';
        var isGroup   = row.row_type && row.row_type !== 'data';

        var cellInputs = columns.map(function (col) {
            var key     = col.temp_key || col.id;
            var content = row.cells[key] !== undefined ? row.cells[key] : '';
            var placeholder = isGroup ? 'Leeg = samenvoegen \u2192' : '';
            var hasHtml = /<[a-z][\s\S]*>/i.test(content);
            return '<td><div class="tmp-cell-wrap">' +
                (hasHtml ? '<div class="tmp-cell-preview">' + content + '</div>' : '') +
                '<textarea class="tmp-cell-input' + (hasHtml ? ' tmp-cell-hidden' : '') + '" data-col-key="' + escAttr(key + '') + '" rows="1"' +
                (placeholder ? ' placeholder="' + escAttr(placeholder) + '"' : '') + '>' +
                escHtml(content) + '</textarea></div></td>';
        }).join('');

        var $tr = $('<tr class="' + escAttr(typeClass) + '" data-temp-id="' + escAttr(row.temp_id) + '">' +
            '<td><span class="tmp-drag-handle dashicons dashicons-menu"></span></td>' +
            '<td><span class="' + escAttr(badgeClass) + '" title="Klik om rijtype te wijzigen">' + escHtml(badgeText) + '</span></td>' +
            cellInputs +
            '<td class="tmp-row-actions">' +
                '<button type="button" class="tmp-row-duplicate dashicons dashicons-admin-page" title="Rij dupliceren"></button>' +
                '<button type="button" class="tmp-row-delete dashicons dashicons-trash" title="' + escAttr(i18n.delete_row) + '"></button>' +
            '</td>' +
        '</tr>');

        $tr.find('.tmp-row-type-badge').on('click', function () {
            var tempId = $tr.data('temp-id') + '';
            var rowObj = rows.find(function (r) { return r.temp_id + '' === tempId; });
            if (!rowObj) return;
            var curIdx = rowTypeOrder.indexOf(rowObj.row_type);
            var newIdx = (curIdx + 1) % rowTypeOrder.length;
            rowObj.row_type = rowTypeOrder[newIdx];
            isDirty = true;
            rebuildRowTable();
        });

        $tr.find('.tmp-cell-preview').on('click', function () {
            var $preview = $(this);
            var $wrap = $preview.closest('.tmp-cell-wrap');
            var $ta = $wrap.find('.tmp-cell-input');
            $preview.hide();
            $ta.removeClass('tmp-cell-hidden').focus();
            $ta[0].style.height = 'auto';
            $ta[0].style.height = ($ta[0].scrollHeight) + 'px';
        });

        $tr.find('.tmp-cell-input').on('input change', function () {
            var $area  = $(this);
            var colKey = $area.data('col-key') + '';
            var tempId = $tr.data('temp-id') + '';
            var rowObj = rows.find(function (r) { return r.temp_id + '' === tempId; });
            if (rowObj) {
                rowObj.cells[colKey] = $area.val();
                isDirty = true;
            }
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        $tr.find('.tmp-cell-input').on('focus', function () {
            var $area = $(this);
            var colKey = $area.data('col-key') + '';
            var tempId = $tr.data('temp-id') + '';
            var col = columns.find(function(c) { return (c.temp_key || c.id) + '' === colKey; });
            var colLabel = col ? col.label : '';
            var rowObj = rows.find(function (r) { return r.temp_id + '' === tempId; });
            var rowIdx = rowObj ? rows.indexOf(rowObj) + 1 : '';
            activeCell = { $area: $area, el: $area[0], $tr: $tr, colKey: colKey, tempId: tempId };
            $('#tmp-cell-toolbar').removeClass('tmp-toolbar-disabled');
            $('#tmp-tb-cell-ref').text(colLabel + ' · rij ' + rowIdx);
            $('.tmp-cell-input').closest('td').removeClass('tmp-cell-active');
            $area.closest('td').addClass('tmp-cell-active');
        });

        $tr.find('.tmp-cell-input').on('blur', function () {
            var $ta = $(this);
            var val = $ta.val();
            var $wrap = $ta.closest('.tmp-cell-wrap');
            var $preview = $wrap.find('.tmp-cell-preview');
            if (/<[a-z][\s\S]*>/i.test(val)) {
                if ($preview.length) {
                    $preview.html(val).show();
                } else {
                    $('<div class="tmp-cell-preview"></div>').html(val).insertBefore($ta);
                }
                $ta.addClass('tmp-cell-hidden');
            } else {
                $preview.remove();
                $ta.removeClass('tmp-cell-hidden');
            }
        });

        $tr.find('.tmp-row-duplicate').on('click', function () {
            var tempId = $tr.data('temp-id') + '';
            var rowObj = rows.find(function (r) { return r.temp_id + '' === tempId; });
            if (!rowObj) return;
            var newTempId = 'new_row_' + (++rowTempIdx);
            var newCells = {};
            for (var k in rowObj.cells) {
                newCells[k] = rowObj.cells[k];
            }
            var newRow = {
                temp_id:      newTempId,
                row_type:     rowObj.row_type,
                cells:        newCells,
                sort_order:   0,
                is_collapsed: false,
                parent_id:    rowObj.parent_id || null,
                parent_temp_id: rowObj.parent_temp_id || null,
            };
            var idx = rows.indexOf(rowObj);
            rows.splice(idx + 1, 0, newRow);
            isDirty = true;
            rebuildRowTable();
        });

        $tr.find('.tmp-row-delete').on('click', function () {
            var tempId = $tr.data('temp-id') + '';
            rows = rows.filter(function (r) { return r.temp_id + '' !== tempId; });
            $tr.remove();
            isDirty = true;
        });

        $tr.find('.tmp-cell-input').on('keydown', function (e) {
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                toolbarBold();
            }
            if (e.ctrlKey && e.key === 'i') {
                e.preventDefault();
                toolbarItalic();
            }
        });

        return $tr;
    }

    function syncRowsFromDOM() {
        var newOrder = [];
        $('#tmp-rows-wrapper .tmp-rows-sortable tr').each(function () {
            var tempId = $(this).data('temp-id') + '';
            var existing = rows.find(function (r) { return r.temp_id + '' === tempId; });
            if (existing) newOrder.push(existing);
        });
        rows = newOrder;
    }

    /* ===== TOOLBAR FUNCTIONS ===== */
    function ensureCellEditable() {
        if (!activeCell) return null;
        var $wrap = activeCell.$area.closest('.tmp-cell-wrap');
        $wrap.find('.tmp-cell-preview').hide();
        activeCell.$area.removeClass('tmp-cell-hidden');
        return activeCell;
    }

    function toolbarBold() {
        var cell = ensureCellEditable();
        if (!cell) return;
        var el = cell.el;
        var start = el.selectionStart;
        var end = el.selectionEnd;
        var val = el.value;
        var sel = val.substring(start, end) || 'tekst';
        el.value = val.substring(0, start) + '<strong>' + sel + '</strong>' + val.substring(end);
        cell.$area.trigger('input');
        el.focus();
        el.setSelectionRange(start + 8, start + 8 + sel.length);
    }

    function toolbarItalic() {
        var cell = ensureCellEditable();
        if (!cell) return;
        var el = cell.el;
        var start = el.selectionStart;
        var end = el.selectionEnd;
        var val = el.value;
        var sel = val.substring(start, end) || 'tekst';
        el.value = val.substring(0, start) + '<em>' + sel + '</em>' + val.substring(end);
        cell.$area.trigger('input');
        el.focus();
        el.setSelectionRange(start + 4, start + 4 + sel.length);
    }

    function toolbarLink() {
        var cell = ensureCellEditable();
        if (!cell) return;
        var el = cell.el;
        var start = el.selectionStart;
        var end = el.selectionEnd;
        var sel = el.value.substring(start, end);
        openLinkModal(cell.$area, el, start, end, sel);
    }

    function toolbarBullet() {
        var cell = ensureCellEditable();
        if (!cell) return;
        var el = cell.el;
        var start = el.selectionStart;
        var end = el.selectionEnd;
        var val = el.value;
        var sel = val.substring(start, end);

        var items;
        if (sel) {
            items = sel.split('\n').filter(function (l) { return l.trim() !== ''; });
        } else {
            items = ['Item 1'];
        }
        var html = '<ul>\n' + items.map(function (item) {
            return '  <li>' + item.trim() + '</li>';
        }).join('\n') + '\n</ul>';

        el.value = val.substring(0, start) + html + val.substring(end);
        cell.$area.trigger('input');
        el.focus();
    }

    function toolbarDeleteRow() {
        if (!activeCell) return;
        var tempId = activeCell.tempId;
        var rowObj = rows.find(function (r) { return r.temp_id + '' === tempId; });
        if (!rowObj) { activeCell = null; return; }
        rows = rows.filter(function (r) { return r.temp_id + '' !== tempId; });
        isDirty = true;
        rebuildRowTable();
    }

    function toolbarDeleteCol() {
        if (!activeCell) return;
        var colKey = activeCell.colKey;
        var col = columns.find(function(c) { return (c.temp_key || c.id) + '' === colKey; });
        if (!col) { activeCell = null; return; }
        if (!confirm('Kolom "' + (col.label || 'Kolom') + '" verwijderen?')) return;
        columns = columns.filter(function(c) { return (c.temp_key || c.id) + '' !== colKey; });
        isDirty = true;
        rebuildRowTable();
    }

    /* ===== LINK MODAL ===== */
    var siteDomains = cfg.site_domains || [];

    function isInternalUrl(url) {
        if (!url) return false;
        if (url.charAt(0) === '/' || url.charAt(0) === '#') return true;
        try {
            var parsed = new URL(url, window.location.origin);
            var host   = parsed.hostname.toLowerCase();
            for (var i = 0; i < siteDomains.length; i++) {
                if (host === siteDomains[i].toLowerCase()) return true;
            }
            return host === window.location.hostname.toLowerCase();
        } catch (e) {
            return false;
        }
    }

    var linkSearchTimer = null;

    function openLinkModal($area, el, start, end, selectedText) {
        closeLinkModal();

        var $overlay = $('<div class="tmp-link-overlay"></div>');
        var $modal = $(
            '<div class="tmp-link-modal">' +
                '<div class="tmp-link-modal-header">' +
                    '<span>Link invoegen</span>' +
                    '<button type="button" class="tmp-link-modal-close">&times;</button>' +
                '</div>' +
                '<div class="tmp-link-modal-tabs">' +
                    '<button type="button" class="tmp-link-tab active" data-tab="url">URL</button>' +
                    '<button type="button" class="tmp-link-tab" data-tab="search">Post / Pagina zoeken</button>' +
                '</div>' +
                '<div class="tmp-link-tab-content tmp-link-tab-url active">' +
                    '<div class="tmp-link-field">' +
                        '<label>URL</label>' +
                        '<input type="text" class="tmp-link-url" placeholder="https://example.com" value="">' +
                    '</div>' +
                '</div>' +
                '<div class="tmp-link-tab-content tmp-link-tab-search">' +
                    '<div class="tmp-link-field">' +
                        '<label>Zoek een post of pagina</label>' +
                        '<input type="text" class="tmp-link-search-input" placeholder="Typ om te zoeken...">' +
                    '</div>' +
                    '<div class="tmp-link-search-results"></div>' +
                '</div>' +
                '<div class="tmp-link-field">' +
                    '<label>Linktekst</label>' +
                    '<input type="text" class="tmp-link-text" placeholder="Weergavetekst" value="' + escAttr(selectedText) + '">' +
                '</div>' +
                '<div class="tmp-link-option">' +
                    '<label><input type="checkbox" class="tmp-link-newtab"> Open in nieuw tabblad</label>' +
                '</div>' +
                '<div class="tmp-link-modal-footer">' +
                    '<button type="button" class="button tmp-link-cancel">Annuleren</button>' +
                    '<button type="button" class="button button-primary tmp-link-insert">Link invoegen</button>' +
                '</div>' +
            '</div>'
        );

        $('body').append($overlay).append($modal);

        $modal.find('.tmp-link-tab').on('click', function () {
            var tab = $(this).data('tab');
            $modal.find('.tmp-link-tab').removeClass('active');
            $(this).addClass('active');
            $modal.find('.tmp-link-tab-content').removeClass('active');
            $modal.find('.tmp-link-tab-' + tab).addClass('active');
        });

        $modal.find('.tmp-link-url').on('input', function () {
            var url = $(this).val().trim();
            var internal = isInternalUrl(url);
            $modal.find('.tmp-link-newtab').prop('checked', !internal);
        });

        $modal.find('.tmp-link-search-input').on('input', function () {
            var q = $(this).val().trim();
            var $results = $modal.find('.tmp-link-search-results');
            if (linkSearchTimer) clearTimeout(linkSearchTimer);
            if (q.length < 2) {
                $results.empty();
                return;
            }
            linkSearchTimer = setTimeout(function () {
                $results.html('<div class="tmp-link-searching">Zoeken...</div>');
                $.post(ajaxurl, {
                    action: 'tablemaster_search_posts',
                    nonce:  nonce,
                    search: q,
                }, function (res) {
                    $results.empty();
                    if (!res.success || !res.data.results.length) {
                        $results.html('<div class="tmp-link-no-results">Geen resultaten gevonden.</div>');
                        return;
                    }
                    res.data.results.forEach(function (item) {
                        var typeLabel = item.type === 'page' ? 'Pagina' : 'Bericht';
                        var $item = $('<div class="tmp-link-result-item">' +
                            '<span class="tmp-link-result-title">' + escHtml(item.title) + '</span>' +
                            '<span class="tmp-link-result-type">' + escHtml(typeLabel) + '</span>' +
                        '</div>');
                        $item.on('click', function () {
                            $modal.find('.tmp-link-url').val(item.url);
                            if (!$modal.find('.tmp-link-text').val().trim()) {
                                $modal.find('.tmp-link-text').val(item.title);
                            }
                            $modal.find('.tmp-link-newtab').prop('checked', false);
                            $results.find('.tmp-link-result-item').removeClass('selected');
                            $item.addClass('selected');
                        });
                        $results.append($item);
                    });
                });
            }, 300);
        });

        function doInsert() {
            var url  = $modal.find('.tmp-link-url').val().trim();
            if (!url) return;
            var text    = $modal.find('.tmp-link-text').val().trim() || url;
            var newTab  = $modal.find('.tmp-link-newtab').is(':checked');
            var val     = el.value;
            var tag     = '<a href="' + url + '"' + (newTab ? ' target="_blank" rel="noopener"' : '') + '>' + text + '</a>';
            el.value    = val.substring(0, start) + tag + val.substring(end);
            $area.trigger('input');
            el.focus();
            closeLinkModal();
        }

        $modal.find('.tmp-link-insert').on('click', doInsert);
        $modal.find('.tmp-link-cancel, .tmp-link-modal-close').on('click', closeLinkModal);
        $overlay.on('click', closeLinkModal);

        $modal.find('.tmp-link-url').focus();
    }

    function closeLinkModal() {
        $('.tmp-link-overlay, .tmp-link-modal').remove();
        if (linkSearchTimer) clearTimeout(linkSearchTimer);
    }

    /* ===== CSV IMPORT ===== */
    function parseCSVWithDelimiter(text, delim) {
        var lines = [];
        var row = [];
        var field = '';
        var inQuote = false;
        var i = 0;
        var len = text.length;

        while (i < len) {
            var ch = text[i];
            if (inQuote) {
                if (ch === '"') {
                    if (i + 1 < len && text[i + 1] === '"') {
                        field += '"';
                        i += 2;
                    } else {
                        inQuote = false;
                        i++;
                    }
                } else {
                    field += ch;
                    i++;
                }
            } else {
                if (ch === '"') {
                    inQuote = true;
                    i++;
                } else if (ch === delim) {
                    row.push(field);
                    field = '';
                    i++;
                } else if (ch === '\r') {
                    row.push(field);
                    field = '';
                    lines.push(row);
                    row = [];
                    i++;
                    if (i < len && text[i] === '\n') i++;
                } else if (ch === '\n') {
                    row.push(field);
                    field = '';
                    lines.push(row);
                    row = [];
                    i++;
                } else {
                    field += ch;
                    i++;
                }
            }
        }
        if (field !== '' || row.length > 0) {
            row.push(field);
            lines.push(row);
        }
        return lines;
    }

    function detectDelimiter(text) {
        var delimiters = [',', ';', '\t'];
        var best = ',';
        var bestScore = 0;

        for (var d = 0; d < delimiters.length; d++) {
            var delim = delimiters[d];
            var testRows = parseCSVWithDelimiter(text, delim);
            if (testRows.length < 2) continue;

            var headerLen = testRows[0].length;
            if (headerLen < 2) continue;

            var consistent = 0;
            var total = Math.min(testRows.length, 10);
            for (var r = 0; r < total; r++) {
                if (testRows[r].length === headerLen) consistent++;
            }

            var score = (consistent / total) * headerLen;
            if (score > bestScore) {
                bestScore = score;
                best = delim;
            }
        }
        return best;
    }

    function handleCSVImport(file) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var text = e.target.result;
            if (!text || !text.trim()) {
                alert('Het CSV-bestand is leeg.');
                return;
            }

            if (text.charCodeAt(0) === 0xFEFF) text = text.substring(1);

            var delimiter = detectDelimiter(text);
            var parsed = parseCSVWithDelimiter(text, delimiter);

            parsed = parsed.filter(function (row) {
                return row.some(function (cell) { return cell.trim() !== ''; });
            });

            if (parsed.length < 2) {
                alert('Het CSV-bestand bevat te weinig data (minimaal een kopregel en één datarij).');
                return;
            }

            var headers = parsed[0];
            var dataRows = parsed.slice(1);

            var maxCols = headers.length;
            dataRows.forEach(function (r) {
                if (r.length > maxCols) maxCols = r.length;
            });
            while (headers.length < maxCols) {
                headers.push('Kolom ' + (headers.length + 1));
            }

            var colCount = headers.length;
            var rowCount = dataRows.length;

            var msg = 'CSV gelezen: ' + colCount + ' kolommen, ' + rowCount + ' rijen.\n\n';
            if (columns.length > 0 || rows.length > 0) {
                msg += 'Er zijn al gegevens in de tabel.\n';
                msg += 'Wilt u de bestaande data VERVANGEN?\n\n';
                msg += 'OK = Vervangen\nAnnuleren = Import annuleren';
                if (!confirm(msg)) return;
            }

            columns = [];
            rows = [];
            colTempIdx = 0;
            rowTempIdx = 0;

            headers.forEach(function (label, idx) {
                var tempKey = 'new_' + (++colTempIdx);
                columns.push({
                    id:       0,
                    temp_key: tempKey,
                    label:    label.trim() || ('Kolom ' + (idx + 1)),
                    type:     'text',
                    settings: { width: 'auto', align: 'left', sortable: true, filterable: true, header_group1: '', header_group2: '' }
                });
            });

            dataRows.forEach(function (csvRow) {
                var tempId = 'new_row_' + (++rowTempIdx);
                var cells = {};
                columns.forEach(function (col, ci) {
                    cells[col.temp_key] = (csvRow[ci] !== undefined ? csvRow[ci] : '').trim();
                });
                rows.push({
                    id:             0,
                    temp_id:        tempId,
                    row_type:       'data',
                    parent_id:      0,
                    parent_temp_id: '',
                    is_collapsed:   false,
                    cells:          cells,
                });
            });

            rebuildRowTable();
            isDirty = true;
        };

        reader.onerror = function () {
            alert('Fout bij het lezen van het bestand.');
        };

        reader.readAsText(file, 'UTF-8');
    }

    /* ===== LOAD STRUCTURE ===== */
    function loadStructure() {
        $.post(ajaxurl, {
            action:   'tablemaster_get_structure',
            nonce:    nonce,
            table_id: tableId,
            lang:     lang,
        }, function (res) {
            if (!res.success) return;
            var d = res.data;
            columns = [];

            // Build column temp_key map
            var colTempMap = {};
            (d.columns || []).forEach(function (col) {
                var colSettings = {};
                try { colSettings = JSON.parse(col.settings || '{}'); } catch(e) {}
                var tempKey = 'db_' + col.id;
                colTempMap[col.id] = tempKey;
                columns.push({
                    id:       parseInt(col.id),
                    temp_key: tempKey,
                    label:    col.label,
                    type:     col.type,
                    settings: {
                        width:         colSettings.width         || 'auto',
                        align:         colSettings.align         || 'left',
                        sortable:      colSettings.sortable      !== false,
                        filterable:    colSettings.filterable     !== false,
                        header_group1: colSettings.header_group1 || '',
                        header_group2: colSettings.header_group2 || '',
                    }
                });
            });

            // Columns are rendered inline in the row table headers

            rows = [];
            (d.rows || []).forEach(function (row) {
                var cells = {};
                var rowCells = row.cells || {};
                columns.forEach(function (col) {
                    // rowCells keys are string column IDs from PHP JSON
                    var dbColId = col.id + '';
                    cells[col.temp_key] = rowCells[dbColId] !== undefined ? rowCells[dbColId] : '';
                });

                rows.push({
                    id:             parseInt(row.id),
                    temp_id:        'db_' + row.id,
                    row_type:       row.row_type,
                    parent_id:      row.parent_id ? parseInt(row.parent_id) : 0,
                    parent_temp_id: row.parent_id ? 'db_' + row.parent_id : '',
                    is_collapsed:   row.is_collapsed === '1' || row.is_collapsed === 1,
                    cells:          cells,
                });
            });

            rebuildRowTable();
        });
    }

    /* ===== SAVE ALL ===== */
    function saveAll() {
        syncColumnsFromDOM();
        syncRowsFromDOM();

        var tableName = $('#tmp-table-name').val().trim();
        if (!tableName) {
            alert('Vul een tabelnaam in.');
            return;
        }

        var activeTheme = 'custom';
        var colors      = getColorValues();

        var tableSettings = {
            theme:              activeTheme,
            colors:             colors,
            caption:            ($('#tmp-caption').val() || '').trim(),
            search:             $('#tmp-search').is(':checked'),
            search_position:    $('#tmp-search-position').val(),
            pagination:         $('#tmp-pagination').is(':checked'),
            per_page:           parseInt($('#tmp-per-page').val(), 10),
            per_page_selector:  $('#tmp-per-page-selector').is(':checked'),
            collapsible_groups: $('#tmp-collapsible').is(':checked'),
            mobile_mode:        'scroll',
            sortable:           $('#tmp-sortable').is(':checked'),
            column_filters:     $('#tmp-column-filters').is(':checked'),
            enable_export:      $('#tmp-enable-export').is(':checked'),
            inline_html:        $('#tmp-inline-html').is(':checked'),
            sticky_first_col:   $('#tmp-sticky-first-col').is(':checked'),
            sticky_header:      $('#tmp-sticky-header').is(':checked'),
            default_sort_col:   $('#tmp-default-sort-col').val(),
            default_sort_dir:   $('#tmp-default-sort-dir').val(),
            default_col_width:  $('#tmp-default-col-width').val().trim(),
            max_width:          $('#tmp-max-width').val().trim(),
            fonts:              (function () {
                var f = {};
                $('.tmp-font-size').each(function () {
                    var k = $(this).data('font-key');
                    if (!f[k]) f[k] = {};
                    f[k].size = $(this).val();
                });
                $('.tmp-font-bold').each(function () {
                    var k = $(this).data('font-key');
                    if (!f[k]) f[k] = {};
                    f[k].bold = $(this).is(':checked');
                });
                $('.tmp-font-italic').each(function () {
                    var k = $(this).data('font-key');
                    if (!f[k]) f[k] = {};
                    f[k].italic = $(this).is(':checked');
                });
                return f;
            })(),
        };

        setStatus('loading');

        // Step 1: save table meta
        $.post(ajaxurl, {
            action:   'tablemaster_save_table',
            nonce:    nonce,
            id:       tableId,
            name:     tableName,
            settings: JSON.stringify(tableSettings),
        }, function (res) {
            if (!res.success) {
                setStatus('error', i18n.error);
                return;
            }

            var savedId = res.data.id;
            if (!tableId) {
                tableId = savedId;
            }

            // Step 2: save structure
            var colsPayload = columns.map(function (col, idx) {
                return {
                    id:       col.id || 0,
                    temp_key: col.temp_key,
                    label:    col.label,
                    type:     col.type,
                    settings: col.settings,
                };
            });

            var rowsPayload = rows.map(function (row) {
                return {
                    id:             row.id || 0,
                    temp_id:        row.temp_id,
                    row_type:       row.row_type,
                    parent_id:      row.parent_id || 0,
                    parent_temp_id: row.parent_temp_id || '',
                    is_collapsed:   row.is_collapsed ? 1 : 0,
                    cells:          row.cells,
                };
            });

            $.post(ajaxurl, {
                action:   'tablemaster_save_structure',
                nonce:    nonce,
                table_id: savedId,
                columns:  JSON.stringify(colsPayload),
                rows:     JSON.stringify(rowsPayload),
                lang:     lang,
            }, function (res2) {
                if (res2.success) {
                    setStatus('success', i18n.saved);
                    isDirty = false;
                    if (!cfg.table_id && savedId) {
                        // Redirect to edit page
                        setTimeout(function () {
                            window.location.href = cfg.edit_url + savedId;
                        }, 800);
                    }
                } else {
                    setStatus('error', i18n.error);
                }
            }).fail(function () {
                setStatus('error', i18n.error);
            });

        }).fail(function () {
            setStatus('error', i18n.error);
        });
    }

    function setStatus(type, msg) {
        var $s = $('#tmp-save-status');
        $s.removeClass('success error').text('');
        if (type === 'loading') {
            $s.html('<span class="tmp-spinner"></span>');
        } else if (type === 'success') {
            $s.addClass('success').text(msg || '✓ Opgeslagen');
        } else if (type === 'error') {
            $s.addClass('error').text(msg || 'Fout');
        }
    }

    /* ===== EVENTS ===== */
    function bindEvents() {
        // Add column
        $('#tmp-add-column').on('click', function () {
            addColumn();
        });

        // CSV Import
        $('#tmp-import-csv').on('click', function () {
            $('#tmp-csv-file').val('').trigger('click');
        });
        $('#tmp-csv-file').on('change', function () {
            var file = this.files[0];
            if (file) handleCSVImport(file);
            $(this).val('');
        });

        // Toolbar buttons
        $('#tmp-tb-bold').on('mousedown', function (e) { e.preventDefault(); toolbarBold(); });
        $('#tmp-tb-italic').on('mousedown', function (e) { e.preventDefault(); toolbarItalic(); });
        $('#tmp-tb-link').on('mousedown', function (e) { e.preventDefault(); toolbarLink(); });
        $('#tmp-tb-bullet').on('mousedown', function (e) { e.preventDefault(); toolbarBullet(); });
        $('#tmp-tb-delete-row').on('mousedown', function (e) { e.preventDefault(); toolbarDeleteRow(); });
        $('#tmp-tb-delete-col').on('mousedown', function (e) { e.preventDefault(); toolbarDeleteCol(); });

        $(document).on('mousedown', function (e) {
            if (!$(e.target).closest('.tmp-cell-input, .tmp-cell-toolbar, .tmp-link-modal, .tmp-link-overlay').length) {
                setTimeout(function () {
                    if (!$('.tmp-cell-input:focus').length) {
                        activeCell = null;
                        $('#tmp-cell-toolbar').addClass('tmp-toolbar-disabled');
                        $('#tmp-tb-cell-ref').text('');
                        $('.tmp-cell-input').closest('td').removeClass('tmp-cell-active');
                    }
                }, 100);
            }
        });

        // Add row / groups
        $('#tmp-add-row').on('click', function ()    { addRow('data'); });
        $('#tmp-add-group1').on('click', function () { addRow('group_1'); });
        $('#tmp-add-group2').on('click', function () { addRow('group_2'); });
        $('#tmp-add-group3').on('click', function () { addRow('group_3'); });
        $('#tmp-add-footer').on('click', function () { addRow('footer'); });

        // Save
        $('#tmp-save-all').on('click', function () {
            saveAll();
        });

        // Settings change -> dirty
        $('#tmp-caption, #tmp-search, #tmp-search-position, #tmp-pagination, #tmp-per-page, #tmp-per-page-selector, #tmp-collapsible, #tmp-sortable, #tmp-column-filters, #tmp-enable-export, #tmp-inline-html, #tmp-sticky-first-col, #tmp-sticky-header, #tmp-default-sort-col, #tmp-default-sort-dir, #tmp-default-col-width, #tmp-table-name, .tmp-font-size, .tmp-font-bold, .tmp-font-italic').on('change input', function () {
            isDirty = true;
        });

        // Search/pagination toggle show/hide
        $('#tmp-search').on('change', function () {
            $('#tmp-search-position-group').toggle($(this).is(':checked'));
        });
        $('#tmp-search-position-group').toggle($('#tmp-search').is(':checked'));

        $('#tmp-pagination').on('change', function () {
            $('#tmp-per-page-group').toggle($(this).is(':checked'));
        });
        $('#tmp-per-page-group').toggle($('#tmp-pagination').is(':checked'));

        // Shortcode copy button
        $(document).on('click', '.tmp-copy-btn', function () {
            var shortcode = $(this).data('shortcode') || $('#tmp-shortcode-value').text();
            copyToClipboard(shortcode);
            var $btn = $(this);
            var origText = $btn.text();
            $btn.text('✓ ' + (i18n.copy_shortcode || 'Gekopieerd!'));
            setTimeout(function () { $btn.text(origText); }, 2000);
        });

        // Delete on list page
        $(document).on('click', '.tmp-delete-btn', function () {
            if (!confirm(i18n.confirm_delete)) return;
            var id = $(this).data('id');
            var $row = $(this).closest('tr');
            $.post(ajaxurl, {
                action: 'tablemaster_delete_table',
                nonce:  nonce,
                id:     id,
            }, function (res) {
                if (res.success) {
                    $row.fadeOut(300, function () { $row.remove(); });
                } else {
                    alert(i18n.error);
                }
            });
        });

        // Duplicate on list page
        $(document).on('click', '.tmp-duplicate-btn', function () {
            var id = $(this).data('id');
            $.post(ajaxurl, {
                action: 'tablemaster_duplicate_table',
                nonce:  nonce,
                id:     id,
            }, function (res) {
                if (res.success) {
                    window.location.href = cfg.edit_url + res.data.id;
                } else {
                    alert(i18n.error);
                }
            });
        });

        // Unsaved changes warning
        window.addEventListener('beforeunload', function (e) {
            if (isDirty) {
                e.returnValue = i18n.unsaved_changes;
                return i18n.unsaved_changes;
            }
        });
    }

    /* ===== UTILS ===== */
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).catch(function () {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }

    function fallbackCopy(text) {
        var $ta = $('<textarea>').val(text).css({ position: 'fixed', top: -9999 }).appendTo('body');
        $ta[0].select();
        document.execCommand('copy');
        $ta.remove();
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escAttr(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

})(jQuery);
