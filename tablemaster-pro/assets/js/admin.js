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

    var columns    = [];   // [{id, label, type, settings:{width,align,sortable,filterable,hide_mobile}}, ...]
    var rows       = [];   // [{id, temp_id, row_type, parent_id, parent_temp_id, cells:{col_key: content}, is_collapsed}, ...]
    var colTempIdx = 0;
    var rowTempIdx = 0;
    var isDirty    = false;

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
        $('#tmp-columns-container').sortable({
            handle: '.tmp-column-drag',
            tolerance: 'pointer',
            update: function () {
                syncColumnsFromDOM();
                rebuildRowTable();
                updatePreview();
                isDirty = true;
            }
        });
    }

    function addColumn(colData) {
        var tempKey = 'new_' + (++colTempIdx);
        var col = $.extend({
            id:       0,
            temp_key: tempKey,
            label:    i18n.add_column + ' ' + (columns.length + 1),
            type:     'text',
            settings: { width: 'auto', align: 'left', sortable: true, filterable: true, hide_mobile: false, header_group1: '', header_group2: '' }
        }, colData || {});
        if (!col.settings.header_group1) col.settings.header_group1 = '';
        if (!col.settings.header_group2) col.settings.header_group2 = '';

        columns.push(col);
        renderColumnItem(col);
        rebuildRowTable();
        updatePreview();
        isDirty = true;
    }

    function renderColumnItem(col) {
        var $container = $('#tmp-columns-container');
        $container.find('.tmp-columns-empty').hide();

        var typeOptions = ['text','number','date','link','image','html'].map(function(t) {
            return '<option value="' + t + '"' + (col.type === t ? ' selected' : '') + '>' + t + '</option>';
        }).join('');

        var alignOptions = ['left','center','right'].map(function(a) {
            return '<option value="' + a + '"' + (col.settings.align === a ? ' selected' : '') + '>' + a + '</option>';
        }).join('');

        var hg1 = col.settings.header_group1 || '';
        var hg2 = col.settings.header_group2 || '';

        var $item = $('<div class="tmp-column-item" data-temp-key="' + escAttr(col.temp_key || col.id) + '">' +
            '<span class="tmp-column-drag dashicons dashicons-menu" title="Slepen"></span>' +
            '<input type="text" class="tmp-column-label-input" value="' + escAttr(col.label) + '" placeholder="Kolomnaam">' +
            '<select class="tmp-column-type-select">' + typeOptions + '</select>' +
            '<select class="tmp-column-align-select">' + alignOptions + '</select>' +
            '<input type="text" class="tmp-column-width-input" value="' + escAttr(col.settings.width || 'auto') + '" placeholder="breedte" style="width:70px;">' +
            '<div class="tmp-column-checkboxes">' +
                '<label><input type="checkbox" class="tmp-col-sortable" ' + (col.settings.sortable ? 'checked' : '') + '> Sorteer</label>' +
                '<label><input type="checkbox" class="tmp-col-filterable" ' + (col.settings.filterable ? 'checked' : '') + '> Filter</label>' +
                '<label><input type="checkbox" class="tmp-col-hidemobile" ' + (col.settings.hide_mobile ? 'checked' : '') + '> Verberg mob.</label>' +
            '</div>' +
            '<button type="button" class="tmp-col-delete dashicons dashicons-trash" title="' + escAttr(i18n.delete_col) + '"></button>' +
            '<div class="tmp-column-groups">' +
                '<input type="text" class="tmp-col-hg1" value="' + escAttr(hg1) + '" placeholder="Header groep 1 (bijv. E. coli)">' +
                '<input type="text" class="tmp-col-hg2" value="' + escAttr(hg2) + '" placeholder="Header groep 2 (bijv. Ambulant)">' +
            '</div>' +
        '</div>');

        $container.append($item);

        $item.find('input, select').on('change input', function () {
            syncColumnsFromDOM();
            rebuildRowTable();
            updatePreview();
            isDirty = true;
        });

        $item.find('.tmp-col-delete').on('click', function () {
            if (!confirm('Kolom verwijderen? Alle data in deze kolom gaat verloren.')) return;
            var tempKey = $item.data('temp-key') + '';
            columns = columns.filter(function (c) {
                return (c.temp_key || c.id) + '' !== tempKey;
            });
            $item.remove();
            rebuildRowTable();
            updatePreview();
            isDirty = true;
        });
    }

    function syncColumnsFromDOM() {
        var newCols = [];
        $('#tmp-columns-container .tmp-column-item').each(function () {
            var $item   = $(this);
            var tempKey = $item.data('temp-key') + '';
            var existing= columns.find(function(c) { return (c.temp_key || c.id) + '' === tempKey; });
            newCols.push({
                id:       existing ? existing.id : 0,
                temp_key: tempKey,
                label:    $item.find('.tmp-column-label-input').val().trim(),
                type:     $item.find('.tmp-column-type-select').val(),
                settings: {
                    width:         $item.find('.tmp-column-width-input').val().trim() || 'auto',
                    align:         $item.find('.tmp-column-align-select').val(),
                    sortable:      $item.find('.tmp-col-sortable').is(':checked'),
                    filterable:    $item.find('.tmp-col-filterable').is(':checked'),
                    hide_mobile:   $item.find('.tmp-col-hidemobile').is(':checked'),
                    header_group1: $item.find('.tmp-col-hg1').val().trim(),
                    header_group2: $item.find('.tmp-col-hg2').val().trim(),
                }
            });
        });
        columns = newCols;
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
        var $wrapper = $('#tmp-rows-wrapper');
        $wrapper.find('.tmp-rows-empty').hide();
        $wrapper.find('.tmp-admin-table-wrap').remove();

        if (rows.length === 0 || columns.length === 0) {
            if (rows.length > 0 && columns.length === 0) {
                $wrapper.find('.tmp-rows-empty').text(i18n.no_columns).show();
            } else {
                $wrapper.find('.tmp-rows-empty').show();
            }
            return;
        }

        var headerCols = columns.map(function (col) {
            return '<th>' + escHtml(col.label || col.temp_key) + '</th>';
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

        // Make rows sortable
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

    var rowTypeOrder = ['data', 'group_1', 'group_2', 'group_3'];
    var rowTypeLabels = { data: 'Data', group_1: 'G1', group_2: 'G2', group_3: 'G3' };

    function buildRowTr(row) {
        var typeClass = 'tmp-admin-row tmp-admin-row-' + (row.row_type || 'data');
        var badgeClass= 'tmp-row-type-badge tmp-type-badge-' + (row.row_type || 'data');
        var badgeText = rowTypeLabels[row.row_type] || 'Data';
        var isGroup   = row.row_type && row.row_type !== 'data';

        var cellInputs = columns.map(function (col) {
            var key     = col.temp_key || col.id;
            var content = row.cells[key] !== undefined ? row.cells[key] : '';
            var placeholder = isGroup ? 'Leeg = samenvoegen \u2192' : '';
            var fmtBtns = '<div class="tmp-cell-fmt">' +
                '<button type="button" class="tmp-fmt-btn tmp-fmt-bold" title="Vet (Ctrl+B)"><b>B</b></button>' +
                '<button type="button" class="tmp-fmt-btn tmp-fmt-link" title="Link invoegen">&#128279;</button>' +
                '</div>';
            return '<td><div class="tmp-cell-wrap">' + fmtBtns +
                '<textarea class="tmp-cell-input" data-col-key="' + escAttr(key + '') + '" rows="1"' +
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

        $tr.find('.tmp-cell-input').on('input change', function () {
            var $area  = $(this);
            var colKey = $area.data('col-key') + '';
            var tempId = $tr.data('temp-id') + '';
            var rowObj = rows.find(function (r) { return r.temp_id + '' === tempId; });
            if (rowObj) {
                rowObj.cells[colKey] = $area.val();
                isDirty = true;
            }
            // Auto-resize
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
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
            if (!confirm('Rij verwijderen?')) return;
            var tempId = $tr.data('temp-id') + '';
            rows = rows.filter(function (r) { return r.temp_id + '' !== tempId; });
            $tr.remove();
            isDirty = true;
        });

        // Bold formatting button
        $tr.find('.tmp-fmt-bold').on('click', function () {
            var $area = $(this).closest('.tmp-cell-wrap').find('.tmp-cell-input');
            var el    = $area[0];
            var start = el.selectionStart;
            var end   = el.selectionEnd;
            var val   = el.value;
            var sel   = val.substring(start, end) || 'tekst';
            el.value  = val.substring(0, start) + '<strong>' + sel + '</strong>' + val.substring(end);
            $area.trigger('input');
            el.focus();
            el.setSelectionRange(start + 8, start + 8 + sel.length);
        });

        // Ctrl+B shortcut in textarea
        $tr.find('.tmp-cell-input').on('keydown', function (e) {
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                $(this).closest('.tmp-cell-wrap').find('.tmp-fmt-bold').trigger('click');
            }
        });

        // Link formatting button
        $tr.find('.tmp-fmt-link').on('click', function () {
            var url = prompt('URL (bijv. https://example.com):');
            if (!url) return;
            var $area = $(this).closest('.tmp-cell-wrap').find('.tmp-cell-input');
            var el    = $area[0];
            var start = el.selectionStart;
            var end   = el.selectionEnd;
            var val   = el.value;
            var sel   = val.substring(start, end) || url;
            el.value  = val.substring(0, start) + '<a href="' + url + '">' + sel + '</a>' + val.substring(end);
            $area.trigger('input');
            el.focus();
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
                        width:       colSettings.width       || 'auto',
                        align:       colSettings.align       || 'left',
                        sortable:    colSettings.sortable    !== false,
                        filterable:  colSettings.filterable  !== false,
                        hide_mobile: !!colSettings.hide_mobile,
                    }
                });
            });

            $('#tmp-columns-container').empty().append('<div class="tmp-columns-empty tmp-hint" style="display:none;"></div>');
            columns.forEach(function (col) {
                renderColumnItem(col);
            });
            initColumnSortable();

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
            caption:            $('#tmp-caption').val(),
            search:             $('#tmp-search').is(':checked'),
            search_position:    $('#tmp-search-position').val(),
            pagination:         $('#tmp-pagination').is(':checked'),
            per_page:           parseInt($('#tmp-per-page').val(), 10),
            per_page_selector:  $('#tmp-per-page-selector').is(':checked'),
            collapsible_groups: $('#tmp-collapsible').is(':checked'),
            mobile_mode:        'scroll',
            column_filters:     $('#tmp-column-filters').is(':checked'),
            inline_html:        $('#tmp-inline-html').is(':checked'),
            sticky_first_col:   $('#tmp-sticky-first-col').is(':checked'),
            default_sort_col:   $('#tmp-default-sort-col').val(),
            default_sort_dir:   $('#tmp-default-sort-dir').val(),
            default_col_width:  $('#tmp-default-col-width').val().trim(),
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

        // Add row / groups
        $('#tmp-add-row').on('click', function ()    { addRow('data'); });
        $('#tmp-add-group1').on('click', function () { addRow('group_1'); });
        $('#tmp-add-group2').on('click', function () { addRow('group_2'); });
        $('#tmp-add-group3').on('click', function () { addRow('group_3'); });

        // Save
        $('#tmp-save-all').on('click', function () {
            saveAll();
        });

        // Settings change -> dirty
        $('#tmp-caption, #tmp-search, #tmp-search-position, #tmp-pagination, #tmp-per-page, #tmp-per-page-selector, #tmp-collapsible, #tmp-column-filters, #tmp-inline-html, #tmp-sticky-first-col, #tmp-default-sort-col, #tmp-default-sort-dir, #tmp-default-col-width, #tmp-table-name').on('change input', function () {
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
