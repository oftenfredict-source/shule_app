<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let availableStockList = @json($availableStockList);
    const reportItemsList = @json($reportItemsList);
    const portalStoreUrl = '{{ $portalStoreRoute }}';

    function initIssueSelect2() {
        $('#requestItemModal .issue-form-select').each(function () {
            const $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: $select.find('option:first').text() || 'Select...',
                allowClear: true,
                minimumResultsForSearch: 0,
                dropdownParent: $('#requestItemModal'),
            });
        });
    }

    function showAlert(message, type) {
        const isSuccess = type === 'success';
        const isWarning = type === 'warning';
        Swal.fire({
            icon: isSuccess ? 'success' : (isWarning ? 'warning' : 'error'),
            title: isSuccess ? 'Success' : (isWarning ? 'Notice' : 'Error'),
            text: message || (isSuccess ? 'Done successfully.' : 'Something went wrong.'),
            confirmButtonColor: '#940000',
            confirmButtonText: 'OK',
            customClass: {
                popup: 'store-swal-popup',
                confirmButton: 'store-swal-confirm',
            },
        });
    }

    async function storeConfirm(message, title) {
        const result = await Swal.fire({
            icon: 'warning',
            title: title || 'Are you sure?',
            text: message,
            showCancelButton: true,
            confirmButtonColor: '#940000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            customClass: {
                popup: 'store-swal-popup',
                confirmButton: 'store-swal-confirm',
            },
        });
        return result.isConfirmed;
    }

    let storeAjaxActive = 0;
    let storeAjaxFinishTimer = null;

    function storeLoadingMessage(url, method) {
        const u = String(url || '').toLowerCase();
        if (u.includes('/delete') || u.includes('delete')) return 'Deleting...';
        if (u.includes('/update') || u.includes('update')) return 'Updating...';
        if (u.includes('/return')) return 'Processing return...';
        if (u.includes('requisition')) return 'Saving requisition...';
        if (u.includes('/stock')) return 'Saving stock...';
        if (u.includes('/items')) return method === 'GET' ? 'Loading items...' : 'Saving item...';
        if (u.includes('report')) return 'Generating report...';
        if (u.includes('package')) return 'Saving package...';
        if (u.includes('categor')) return 'Saving category...';
        if (u.includes('measurement') || u.includes('/units') || u.includes('/unit')) return 'Saving...';
        return method === 'GET' ? 'Loading...' : 'Saving...';
    }

    function storeLoadingStart(message) {
        storeAjaxActive += 1;
        clearTimeout(storeAjaxFinishTimer);
        const $bar = $('#storeAjaxBar');
        const $overlay = $('#storeAjaxOverlay');
        $bar.removeClass('is-finishing').addClass('is-active').attr('aria-hidden', 'false');
        $('#storeAjaxText').text(message || 'Working...');
        $overlay.addClass('is-active').attr('aria-hidden', 'false');
        $('body').addClass('store-ajax-busy');
    }

    function storeLoadingStop() {
        storeAjaxActive = Math.max(0, storeAjaxActive - 1);
        if (storeAjaxActive > 0) return;

        const $bar = $('#storeAjaxBar');
        const $overlay = $('#storeAjaxOverlay');
        $bar.addClass('is-finishing');
        $overlay.removeClass('is-active').attr('aria-hidden', 'true');
        $('body').removeClass('store-ajax-busy');

        storeAjaxFinishTimer = setTimeout(function () {
            $bar.removeClass('is-active is-finishing').attr('aria-hidden', 'true');
        }, 220);
    }

    async function postJson(url, data, loadingMessage) {
        storeLoadingStart(loadingMessage || storeLoadingMessage(url, 'POST'));
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Request failed');
            return json;
        } finally {
            storeLoadingStop();
        }
    }

    async function getJson(url, loadingMessage) {
        storeLoadingStart(loadingMessage || storeLoadingMessage(url, 'GET'));
        try {
            const res = await fetch(url, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Request failed');
            return json;
        } finally {
            storeLoadingStop();
        }
    }

    const ADD_CATEGORY_OPT = '__add_category__';
    const ADD_TYPE_OPT = '__add_type__';
    const ADD_UNIT_OPT = '__add_unit__';
    let returnToAddItemAfter = null;

    function setStoreCheckboxGroupChecked(targetSelector, checked) {
        $(targetSelector).find('input[type="checkbox"]:not(:disabled)').each(function () {
            $(this).prop('checked', checked).trigger('change');
        });
    }

    $(document).on('click', '.store-check-all-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const target = $(this).data('target');
        if (target) setStoreCheckboxGroupChecked(target, true);
    });

    $(document).on('click', '.store-check-none-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const target = $(this).data('target');
        if (target) setStoreCheckboxGroupChecked(target, false);
    });

    function isAddOption(value) {
        return value === ADD_CATEGORY_OPT || value === ADD_TYPE_OPT || value === ADD_UNIT_OPT;
    }

    function select2AddOptionTemplate(data) {
        if (!data.id) return data.text;
        if (isAddOption(data.id)) {
            return $('<span class="text-primary-custom font-weight-bold">' + data.text + '</span>');
        }
        return data.text;
    }

    function ensureAddOptionLast($select, value, label) {
        $select.find(`option[value="${value}"]`).remove();
        $select.append(new Option(label, value, false, false));
    }

    function initItemFormSelect2($el) {
        $el.each(function () {
            const $select = $(this);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            const $parent = $select.closest('.modal');
            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: $select.find('option:first').text() || 'Select...',
                allowClear: true,
                minimumResultsForSearch: 0,
                dropdownAutoWidth: false,
                dropdownParent: $parent.length ? $parent : $(document.body),
                templateResult: select2AddOptionTemplate,
            });
        });
    }

    function initUnitMultiSelect($unitSelect, selectedUnitIds) {
        if ($unitSelect.hasClass('select2-hidden-accessible')) {
            $unitSelect.select2('destroy');
        }
        const $parent = $unitSelect.closest('.modal');
        $unitSelect.select2({
            theme: 'bootstrap-5',
            width: '100%',
            multiple: true,
            placeholder: 'Select one or more units...',
            allowClear: true,
            minimumResultsForSearch: 0,
            closeOnSelect: false,
            dropdownParent: $parent.length ? $parent : $(document.body),
            templateResult: select2AddOptionTemplate,
        });
        const ids = (selectedUnitIds || []).map(String).filter(id => !isAddOption(id));
        if (ids.length) {
            $unitSelect.val(ids).trigger('change');
        }
    }

    function openAddUnitFromItemForm() {
        const typeId = $('#itemMeasurementTypeSelect').val();
        const typeName = $('#itemMeasurementTypeSelect option:selected').text();
        if (!typeId || isAddOption(typeId)) return;
        returnToAddItemAfter = 'unit';
        $('#addItemModal').modal('hide');
        $('#addUnitTypeId').val(typeId);
        $('#addUnitTypeName').text(typeName);
        $('#addUnitModal').modal('show');
    }

    $(document).on('select2:open', '#addItemModal .item-form-select, #editItemModal .item-form-select, #addItemModal .item-unit-select, #editItemModal .item-unit-select', function () {
        setTimeout(function () {
            const $open = $('.select2-container--open');
            const searchField = $open.find('.select2-search__field')[0];
            if (searchField) {
                searchField.focus();
            }
        }, 0);
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }


    function getItemCategoryId(itemID) {
        const fromStock = availableStockList.find(u => String(u.itemID) === String(itemID));
        if (fromStock && fromStock.categoryID) return fromStock.categoryID;
        const fromReport = reportItemsList.find(it => String(it.itemID) === String(itemID));
        return fromReport?.categoryID ?? null;
    }

    function resetIssueForm() {
        $('#issueCategoryFilter').val('');
        $('#issueItemSelect').empty().append('<option value="">Search or select item in stock...</option>');
        $('#issueStockInfo, #issueLabelsSection, #issueTakeSection, #issueExpectedReturnSection').hide();
        $('#issueLabelsList').empty();
        $('#issueTakeMethod').empty().append('<option value="">Choose request method...</option>');
        $('#issueTakeTarget').empty().append('<option value="">Choose...</option>').hide();
        $('#issueTakeTargetList').empty().hide();
        $('#issueTakeTargetToolbar').hide();
        $('#issueTakeChainInfo').hide().empty();
        $('#issueTakeMethodWrap, #issueTakeTargetWrap').hide();
        $('#issueTakeTargetDetail').text('');
        $('#issueTakeQtyWrap').show();
        $('#issueTakeQty').val(1).prop('readonly', false);
        $('#issueTakeQtyHint').text('');
        $('#issueNotes').val('');
        $('#issueExpectedReturnDate').val('');
        $('#btnIssueItem').prop('disabled', true);
        populateIssueItemSelect();
    }

    function populateIssueItemSelect() {
        const cat = $('#issueCategoryFilter').val();
        const itemMap = {};
        availableStockList.forEach(u => {
            if (cat) {
                const itemCat = u.categoryID ?? getItemCategoryId(u.itemID);
                if (String(itemCat) !== String(cat)) return;
            }
            if (!itemMap[u.itemID]) {
                itemMap[u.itemID] = u.item_name;
            }
        });
        const $sel = $('#issueItemSelect');
        const prev = $sel.val();
        $sel.find('option:not(:first)').remove();
        Object.keys(itemMap).sort((a, b) => itemMap[a].localeCompare(itemMap[b])).forEach(itemID => {
            $sel.append(new Option(itemMap[itemID], itemID, false, String(itemID) === String(prev)));
        });
        if (!itemMap[prev]) {
            $sel.val('');
        }
        if ($sel.hasClass('select2-hidden-accessible')) {
            $sel.trigger('change.select2');
        }
    }


    function renderIssueLabels(itemID) {
        const units = availableStockList.filter(u => String(u.itemID) === String(itemID));
        const $list = $('#issueLabelsList').empty();
        $('#issueTakeSection').hide();
        resetIssueTakeFields();

        if (!units.length) {
            $('#issueLabelsSection, #issueStockInfo').hide();
            updateIssueButton();
            return;
        }

        const pkgUnits = [...new Set(units.map(u => u.package_unit_display))];
        const remainingTotal = units.reduce((sum, u) => sum + (parseFloat(u.remaining_items) || 0), 0);
        $('#issuePackageUnit').text(pkgUnits.join(', '));
        $('#issueAvailableCount').text(units.length + ' label(s), ' + remainingTotal + ' item(s) left');
        $('#issueStockInfo, #issueLabelsSection').show();

        units.forEach(u => {
            const returnBadge = u.requisition_status === 'can_return'
                ? '<span class="store-badge-pill store-badge-return-yes ml-1">Can Return</span>'
                : '<span class="store-badge-pill store-badge-return-no ml-1">Can\'t Return</span>';
            const remainText = u.store_method === 'package'
                ? ` â€” ${escapeHtml(u.package_unit_display)} Â· left ${u.remaining_items}/${u.initial_items} ${escapeHtml(u.leaf_unit_name || 'item')}`
                : ` â€” ${escapeHtml(u.package_unit_display)}`;
            $list.append(`
                <div class="custom-control custom-checkbox mb-2">
                    <input type="checkbox" class="custom-control-input issue-label-checkbox"
                        id="issueLabel${u.stock_unitID}" value="${u.stock_unitID}">
                    <label class="custom-control-label" for="issueLabel${u.stock_unitID}">
                        <strong>${escapeHtml(u.label)}</strong>
                        <span class="text-muted small">${remainText}</span>
                        ${returnBadge}
                    </label>
                </div>
            `);
        });
        updateIssueButton();
    }

    function getSelectedIssueUnits() {
        const ids = [];
        $('.issue-label-checkbox:checked').each(function () {
            ids.push($(this).val());
        });
        return ids
            .map(id => availableStockList.find(x => String(x.stock_unitID) === String(id)))
            .filter(Boolean);
    }

    function getSelectedIssueUnit() {
        const units = getSelectedIssueUnits();
        return units.length === 1 ? units[0] : null;
    }

    function resolveMainTakeForUnit(unit) {
        if (!unit) return null;
        const structure = getIssueTakeStructure(unit);
        if (!structure || !(structure.take_options || []).length) return null;

        if (unit.store_method !== 'package') {
            const opt = structure.take_options[0];
            return opt ? { take_key: opt.key, quantity: 1, opt, unit, max_qty: parseFloat(opt.max_qty) || 1 } : null;
        }

        const mainMethod = (structure.methods || []).find(m => m.key === 'main');
        if (mainMethod && mainMethod.options.length) {
            const opt = mainMethod.options[0];
            return { take_key: opt.key, quantity: 1, opt, unit, max_qty: parseFloat(opt.max_qty) || 1 };
        }

        const pkgOpt = structure.take_options.find(o => o.level === 'package');
        if (pkgOpt) {
            return { take_key: pkgOpt.key, quantity: 1, opt: pkgOpt, unit, max_qty: parseFloat(pkgOpt.max_qty) || 1 };
        }

        return null;
    }

    function buildMultiLabelMainSelections(units) {
        return (units || []).map(u => resolveMainTakeForUnit(u)).filter(Boolean);
    }

    function renderIssueTakeForLabels() {
        const units = getSelectedIssueUnits();
        resetIssueTakeFields();

        if (!units.length) {
            $('#issueTakeSection').hide();
            updateIssueButton();
            return;
        }

        if (units.length > 1) {
            $('#issueTakeSection').show();
            $('#issueTakeChainInfo')
                .html(
                    '<strong>Selected labels:</strong> ' + escapeHtml(units.map(u => u.label).join(', ')) +
                    '<br><span class="text-muted">By Main Package â€” quantity auto = 1 kwa kila label iliyochaguliwa.</span>'
                )
                .show();
            $('#issueTakeMethodWrap, #issueTakeTargetWrap, #issueTakeQtyWrap').hide();
            $('#issueTakeMethod').val('main');
            updateTakeLivePreview('issue', null);
            updateIssueButton();
            return;
        }

        renderIssueTakeOptions(units[0]);
        updateIssueButton();
    }

    function resetIssueTakeFields() {
        $('#issueTakeMethod').empty().append('<option value="">Choose request method...</option>');
        $('#issueTakeTarget').empty().append('<option value="">Choose...</option>').hide();
        $('#issueTakeTargetList').empty().hide();
        $('#issueTakeTargetToolbar').hide();
        $('#issueTakeChainInfo').hide().empty();
        $('#issueTakeMethodWrap, #issueTakeTargetWrap').hide();
        $('#issueTakeTargetDetail').text('');
        $('#issueTakeQtyWrap').show();
        $('#issueTakeQty').val(1).prop('readonly', false);
        $('#issueTakeQtyHint').text('');
        $('#issueTakeLivePreview').hide().empty();
        const activeUnit = getSelectedIssueUnit();
        if (activeUnit) activeUnit._subHierarchy = null;
    }

    function getIssueTakeStructure(unit) {
        if (!unit) return null;
        if (unit.take_structure) return unit.take_structure;
        if (unit.take_options && unit.take_options.length) {
            return {
                show_method_select: false,
                methods: [],
                take_options: unit.take_options,
                chain_summary: null,
                contains_line: null,
            };
        }
        return null;
    }

    function renderIssueTakeOptions(unit) {
        resetIssueTakeFields();
        if (!unit) {
            $('#issueTakeSection').hide();
            return;
        }

        const structure = getIssueTakeStructure(unit);
        if (!structure || !(structure.take_options || []).length) {
            $('#issueTakeSection').hide();
            return;
        }

        $('#issueTakeSection').show();

        if (structure.contains_line || structure.chain_summary) {
            $('#issueTakeChainInfo')
                .html('<strong>Package chain:</strong> ' + escapeHtml(structure.contains_line || structure.chain_summary))
                .show();
        }

        if (!structure.show_method_select) {
            const opt = structure.take_options[0];
            $('#issueTakeMethodWrap').hide();
            $('#issueTakeTargetWrap').hide();
            $('#issueTakeQtyWrap').hide();
            $('#issueTakeQty').val(1).prop('readonly', true);
            unit._activeTakeOptions = structure.take_options;
            unit._selectedTakeKey = opt.key;
            updateIssueTakeQtyLimits();
            return;
        }

        $('#issueTakeMethodWrap').show();
        const $method = $('#issueTakeMethod').empty().append('<option value="">Choose request method...</option>');
        (structure.methods || []).forEach(m => {
            $method.append(`<option value="${escapeHtml(m.key)}">${escapeHtml(m.label)}</option>`);
        });

        unit._takeStructure = structure;
        $('#issueTakeTargetWrap').hide();
    }

    function buildHierarchyUnitHtml(unit, hierarchy, prefix) {
        const unitCheckClass = prefix === 'issue' ? 'issue-hier-unit-check' : 'incident-hier-unit-check';
        const modeRadioClass = prefix === 'issue' ? 'issue-hier-mode-radio' : 'incident-hier-mode-radio';
        const partialQtyClass = prefix === 'issue' ? 'issue-hier-partial-qty' : 'incident-hier-partial-qty';
        const radioName = prefix + 'HierMode' + unit.index;
        const unitId = prefix + 'HierUnit' + unit.index;
        const isPartial = !!unit.is_partial;
        const child = unit.child;
        const childInfo = child
            ? `<span class="issue-hier-child-info">Ina <strong>${child.capacity_in_parent}</strong> ${escapeHtml(child.package_name)}(s)</span>`
            : '';

        let bodyHtml = '';
        if (isPartial) {
            if (child) {
                bodyHtml = `
                    <div class="issue-hier-child-info">${escapeHtml(unit.label)} â€” chagua ${escapeHtml(child.package_name)} ngapi:</div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Idadi ya ${escapeHtml(child.package_name)}(s)</label>
                        <input type="number" class="form-control form-control-sm ${partialQtyClass}"
                            min="1" max="${child.partial_take.max_qty}" step="1" value="1"
                            data-take-key="${escapeHtml(child.partial_take.key)}"
                            data-max-qty="${child.partial_take.max_qty}"
                            data-items-each="${child.partial_take.items_each}">
                        <small class="text-muted d-block mt-1">Max ${child.partial_take.max_qty} ${escapeHtml(child.package_name)}(s)</small>
                        <div class="text-danger issue-hier-qty-error" style="display:none;"></div>
                    </div>`;
            } else if (unit.loose_take) {
                bodyHtml = `
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Idadi ya ${escapeHtml(unit.loose_take.label)}(s)</label>
                        <input type="number" class="form-control form-control-sm ${partialQtyClass}"
                            min="1" max="${unit.loose_take.max_qty}" step="1" value="1"
                            data-take-key="${escapeHtml(unit.loose_take.key)}"
                            data-max-qty="${unit.loose_take.max_qty}"
                            data-items-each="1">
                        <small class="text-muted d-block mt-1">Max ${unit.loose_take.max_qty}</small>
                        <div class="text-danger issue-hier-qty-error" style="display:none;"></div>
                    </div>`;
            }
        } else {
            let modeHtml = '';
            if (unit.can_take_full && unit.full_take) {
                modeHtml += `
                    <div class="custom-control custom-radio issue-hier-mode-row">
                        <input type="radio" class="custom-control-input ${modeRadioClass}" name="${radioName}"
                            id="${unitId}_full" value="full" data-take-key="${escapeHtml(unit.full_take.key)}" checked>
                        <label class="custom-control-label" for="${unitId}_full">
                            <strong>Full ${escapeHtml(hierarchy.first_sub_name)}</strong>
                        </label>
                    </div>`;
            }
            if (child) {
                modeHtml += `
                    <div class="custom-control custom-radio issue-hier-mode-row">
                        <input type="radio" class="custom-control-input ${modeRadioClass}" name="${radioName}"
                            id="${unitId}_partial" value="partial">
                        <label class="custom-control-label" for="${unitId}_partial">
                            <strong>Chukua ${escapeHtml(child.package_name)}(s) tu</strong> â€” si ${escapeHtml(hierarchy.first_sub_name)} nzima
                        </label>
                    </div>
                    <div class="issue-hier-partial-wrap mt-2" style="display:none;">
                        ${childInfo}
                        <label class="small font-weight-bold">${escapeHtml(unit.label)} ina ${escapeHtml(child.package_name)} ngapi?</label>
                        <input type="number" class="form-control form-control-sm ${partialQtyClass}"
                            min="1" max="${child.partial_take.max_qty}" step="1" value="1"
                            data-take-key="${escapeHtml(child.partial_take.key)}"
                            data-max-qty="${child.partial_take.max_qty}"
                            data-items-each="${child.partial_take.items_each}"
                            disabled>
                        <small class="text-muted d-block mt-1">Max ${child.partial_take.max_qty} Â· kila ${escapeHtml(child.package_name)} = ${child.partial_take.items_each} item(s)</small>
                        <div class="text-danger issue-hier-qty-error" style="display:none;"></div>
                    </div>`;
            }
            bodyHtml = modeHtml;
        }

        return `
            <div class="issue-hier-unit" data-unit-index="${unit.index}" data-is-partial="${isPartial ? '1' : '0'}">
                <div class="issue-hier-unit-head custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input ${unitCheckClass}" id="${unitId}" value="${unit.index}">
                    <label class="custom-control-label" for="${unitId}">
                        <strong>${escapeHtml(unit.label)}</strong>
                        ${child ? `<span class="issue-take-avail-badge">Ina ${child.capacity_in_parent} ${escapeHtml(child.package_name)}(s)</span>` : ''}
                    </label>
                </div>
                <div class="issue-hier-unit-body" style="display:none;">${bodyHtml}</div>
            </div>`;
    }

    function renderSubPackageHierarchy(hierarchy, unit, prefix) {
        const $list = prefix === 'issue' ? $('#issueTakeTargetList') : $('#incidentTakeTargetList');
        const $target = prefix === 'issue' ? $('#issueTakeTarget') : $('#incidentTakeTarget');
        const $detail = prefix === 'issue' ? $('#issueTakeTargetDetail') : $('#incidentTakeTargetDetail');

        $list.empty().show();
        $('#' + (prefix === 'issue' ? 'issueTakeTargetToolbar' : 'incidentTakeTargetToolbar')).show();
        $target.hide();
        unit._subHierarchy = hierarchy;
        unit._activeTakeOptions = hierarchy.take_options || [];

        const summary = hierarchy.available_summary || '';
        const contains = hierarchy.contains_line ? `<div class="text-muted small mb-2">${escapeHtml(hierarchy.contains_line)}</div>` : '';
        $list.append(
            `<div class="issue-hier-summary-bar">
                <strong>1 ${escapeHtml(hierarchy.main_package_name)}</strong> ina hadi ${hierarchy.capacity_per_main} ${escapeHtml(hierarchy.first_sub_name)}(s).
                ${summary ? ' Â· <strong>' + escapeHtml(summary) + '</strong>' : ''}
            </div>${contains}`
        );

        (hierarchy.units || []).forEach(u => {
            $list.append(buildHierarchyUnitHtml(u, hierarchy, prefix));
        });

        $detail.text('Chagua ' + (hierarchy.first_sub_name || 'sub-package') + '(s), kisha Full au ' + (hierarchy.child_sub_name || 'vitengo') + ' ndani yake.');
        updateTakeLivePreview(prefix, unit);
    }

    function collectHierarchyTakeSelections(prefix, unit) {
        const unitCheckClass = prefix === 'issue' ? '.issue-hier-unit-check' : '.incident-hier-unit-check';
        const optionMap = Object.fromEntries((unit._activeTakeOptions || []).map(o => [o.key, o]));
        const selections = [];

        $(unitCheckClass + ':checked').each(function () {
            const $block = $(this).closest('.issue-hier-unit');
            const isPartial = String($block.data('is-partial')) === '1';

            if (isPartial) {
                const $qty = $block.find(prefix === 'issue' ? '.issue-hier-partial-qty' : '.incident-hier-partial-qty');
                const key = $qty.data('take-key');
                const qty = parseFloat($qty.val());
                const opt = optionMap[key];
                if (opt && qty > 0) {
                    selections.push({ take_key: key, quantity: qty, max_qty: parseFloat(opt.max_qty) || 0, opt });
                }
                return;
            }

            const mode = $block.find(prefix === 'issue' ? '.issue-hier-mode-radio:checked' : '.incident-hier-mode-radio:checked').val();
            if (mode === 'full') {
                const key = $block.find('[value="full"]').data('take-key');
                const opt = optionMap[key];
                if (opt) {
                    selections.push({ take_key: key, quantity: 1, max_qty: 1, opt });
                }
            } else if (mode === 'partial') {
                const $qty = $block.find(prefix === 'issue' ? '.issue-hier-partial-qty' : '.incident-hier-partial-qty');
                const key = $qty.data('take-key');
                const qty = parseFloat($qty.val());
                const opt = optionMap[key];
                if (opt && qty > 0) {
                    selections.push({ take_key: key, quantity: qty, max_qty: parseFloat(opt.max_qty) || 0, opt });
                }
            }
        });

        return selections;
    }

    function getQtyErrorEl($input) {
        let $err = $input.siblings('.store-qty-error, .issue-hier-qty-error').first();
        if (!$err.length) {
            $err = $input.parent().children('.store-qty-error, .issue-hier-qty-error').first();
        }
        if (!$err.length) {
            $err = $('<div class="text-danger small store-qty-error mt-1"></div>');
            $input.after($err);
        }
        return $err;
    }

    function markQtyInputInvalid($input, message) {
        getQtyErrorEl($input).text(message || 'Idadi si sahihi.').show();
        $input.addClass('is-invalid');
    }

    function clearQtyInputInvalid($input) {
        $input.removeClass('is-invalid');
        $input.siblings('.store-qty-error, .issue-hier-qty-error').hide();
        $input.parent().children('.store-qty-error, .issue-hier-qty-error').hide();
    }

    function validateTakeQuantityInputs(prefix, unit) {
        const qtyInputSel = prefix === 'issue' ? '#issueTakeQty' : '#incidentTakeQty';
        const rowQtyClass = prefix === 'issue' ? '.issue-take-row-qty-input' : '.incident-take-row-qty-input';
        const hierQtyClass = prefix === 'issue' ? '.issue-hier-partial-qty' : '.incident-hier-partial-qty';
        const unitCheckClass = prefix === 'issue' ? '.issue-hier-unit-check' : '.incident-hier-unit-check';
        const modeRadioClass = prefix === 'issue' ? '.issue-hier-mode-radio' : '.incident-hier-mode-radio';
        const checkboxClass = prefix === 'issue' ? '.issue-take-target-checkbox' : '.incident-take-target-checkbox';

        $(qtyInputSel + ',' + rowQtyClass + ',' + hierQtyClass).each(function () {
            clearQtyInputInvalid($(this));
        });

        let valid = true;

        $(checkboxClass + ':checked').each(function () {
            const $cb = $(this);
            const $input = $cb.closest('.issue-take-target-option').find(rowQtyClass);
            if (!$input.length || $input.prop('disabled')) return;
            const max = parseFloat($cb.data('max-qty')) || parseFloat($input.attr('max')) || 0;
            const val = parseFloat($input.val()) || 0;
            if (val > max) {
                valid = false;
                markQtyInputInvalid($input, 'Haiwezi zidi ' + max + '!');
            } else if (val <= 0) {
                valid = false;
                markQtyInputInvalid($input, 'Weka idadi sahihi (angalau 1).');
            }
        });

        $(unitCheckClass + ':checked').each(function () {
            const $block = $(this).closest('.issue-hier-unit');
            const isPartialUnit = String($block.data('is-partial')) === '1';
            const mode = isPartialUnit ? 'partial' : $block.find(modeRadioClass + ':checked').val();

            if (mode !== 'partial' && !isPartialUnit) return;

            $block.find(hierQtyClass).each(function () {
                const $input = $(this);
                if ($input.prop('disabled')) return;
                const max = parseFloat($input.data('max-qty')) || parseFloat($input.attr('max')) || 0;
                const val = parseFloat($input.val()) || 0;
                if (val > max) {
                    valid = false;
                    markQtyInputInvalid($input, 'Haiwezi zidi ' + max + '!');
                } else if (val <= 0) {
                    valid = false;
                    markQtyInputInvalid($input, 'Weka idadi sahihi (angalau 1).');
                }
            });
        });

        const $mainQty = $(qtyInputSel);
        if ($mainQty.is(':visible') && !$mainQty.prop('readonly')) {
            const opt = prefix === 'issue' ? getSelectedTakeOption() : getSelectedIncidentTakeOption();
            if (opt) {
                const max = parseFloat(opt.max_qty) || 1;
                const val = parseFloat($mainQty.val()) || 0;
                if (val > max) {
                    valid = false;
                    markQtyInputInvalid($mainQty, 'Haiwezi zidi ' + max + '!');
                } else if (val <= 0) {
                    valid = false;
                    markQtyInputInvalid($mainQty, 'Weka idadi sahihi (angalau 1).');
                }
            }
        }

        if (unit) {
            const selections = prefix === 'issue'
                ? collectIssueTakeSelections()
                : collectIncidentTakeSelections();
            const remaining = parseFloat(unit.remaining_items) || 0;
            const totalItems = selections.reduce((sum, s) =>
                sum + (s.quantity * (parseFloat(s.opt?.items_each) || 1)), 0);
            if (totalItems > remaining + 0.00001) {
                valid = false;
                const $preview = prefix === 'issue' ? $('#issueTakeLivePreview') : $('#incidentTakeLivePreview');
                $preview.addClass('alert-danger').removeClass('alert-info');
            } else {
                const $preview = prefix === 'issue' ? $('#issueTakeLivePreview') : $('#incidentTakeLivePreview');
                $preview.removeClass('alert-danger').addClass('alert-info');
            }
        }

        return valid;
    }

    function assertTakeOperationValid(prefix) {
        const units = prefix === 'issue' ? getSelectedIssueUnits() : getSelectedIncidentUnits();
        const unit = units.length === 1 ? units[0] : null;
        const selections = prefix === 'issue' ? collectIssueTakeSelections() : collectIncidentTakeSelections();

        if (!units.length) {
            return { valid: false, message: 'Chagua label moja au zaidi.' };
        }
        if (!selections.length) {
            return { valid: false, message: 'Kamilisha maelezo ya kuchukua (method, package, idadi).' };
        }
        if (!validateTakeQuantityInputs(prefix, unit)) {
            return { valid: false, message: 'Sahihisha idadi â€” haiwezi zidi kiwango cha juu (max) kilichoonyeshwa.' };
        }
        if (!selections.every(s => s.quantity > 0 && s.quantity <= s.max_qty)) {
            return { valid: false, message: 'Idadi ya kuchukua ni kubwa kuliko inavyoruhusiwa kwa kiwango hicho.' };
        }
        if (units.length > 1 && selections.length !== units.length) {
            return { valid: false, message: 'Kila label iliyochaguliwa lazima iwe na chaguo la kuchukua.' };
        }

        const withinStock = selections.every(s => {
            const u = s.unit || units.find(x => String(x.stock_unitID) === String(s.stock_unitID));
            if (!u) return false;
            const items = s.quantity * (parseFloat(s.opt?.items_each) || 1);
            return items <= (parseFloat(u.remaining_items) || 0) + 0.00001;
        });
        if (!withinStock) {
            return { valid: false, message: 'Jumla ya vitu unavyochukua inazidi stock iliyobaki kwenye label.' };
        }

        return { valid: true };
    }

    function validateHierarchyQuantities(prefix, unit) {
        return validateTakeQuantityInputs(prefix, unit)
            && (prefix === 'issue' ? collectIssueTakeSelections() : collectIncidentTakeSelections())
                .every(s => s.quantity > 0 && s.quantity <= s.max_qty);
    }

    function buildTakeCheckboxRow(opt, idx, prefix) {
        const leafName = opt.leaf_unit_name || 'item';
        const indent = opt.chain_position > 0
            ? '<span class="issue-take-chain-indent">' + 'â€” '.repeat(Math.max(0, opt.chain_position - 1)) + '</span>'
            : '';
        const availBadge = opt.available_qty != null
            ? `<span class="issue-take-avail-badge">${opt.available_qty} ${escapeHtml(opt.label)}(s) available</span>`
            : '';
        const capacityLine = opt.capacity_per_main > 0
            ? `<div class="issue-take-capacity-line mt-1">1 ${escapeHtml(opt.main_package_name || 'Set')} has ${opt.capacity_per_main} ${escapeHtml(opt.label)}(s)</div>`
            : '';
        const checkboxClass = prefix === 'incident' ? 'incident-take-target-checkbox' : 'issue-take-target-checkbox';
        const qtyInputClass = prefix === 'incident' ? 'incident-take-row-qty-input' : 'issue-take-row-qty-input';
        const qtyWrapClass = prefix === 'incident' ? 'incident-take-row-qty' : 'issue-take-row-qty';
        const inputId = prefix + 'TakeTargetOpt' + idx;

        return `
            <div class="issue-take-target-option mb-3 pb-2 border-bottom">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input ${checkboxClass}"
                        id="${inputId}" value="${escapeHtml(opt.key)}"
                        data-max-qty="${opt.max_qty}" data-items-each="${opt.items_each}"
                        data-level="${escapeHtml(opt.level)}" data-label="${escapeHtml(opt.label)}">
                    <label class="custom-control-label" for="${inputId}">
                        ${indent}<strong>${escapeHtml(opt.label)}</strong>${availBadge}
                        ${capacityLine}
                        <div class="text-muted small mt-1">${escapeHtml(opt.detail || '')}</div>
                    </label>
                </div>
                <div class="${qtyWrapClass} mt-2" style="display:none;">
                    <label class="small font-weight-bold mb-1">How many ${escapeHtml(opt.label)}(s)?</label>
                    <input type="number" class="form-control form-control-sm ${qtyInputClass}"
                        min="1" max="${opt.max_qty}" step="1" value="1"
                        data-key="${escapeHtml(opt.key)}" data-max-qty="${opt.max_qty}" disabled>
                    <small class="text-muted d-block mt-1 issue-take-row-hint"
                        data-items-each="${opt.items_each}" data-label="${escapeHtml(opt.label)}">
                        Max ${opt.max_qty} Â· each = ${opt.items_each} ${escapeHtml(leafName)}
                    </small>
                    <div class="text-danger small store-qty-error mt-1" style="display:none;"></div>
                </div>
            </div>
        `;
    }

    function collectTakeSelectionsFromDom(prefix, unit) {
        const methodKey = prefix === 'incident' ? $('#incidentTakeMethod').val() : $('#issueTakeMethod').val();
        const options = unit._activeTakeOptions || unit.take_options || [];
        const optionMap = Object.fromEntries(options.map(o => [o.key, o]));
        const checkboxClass = prefix === 'incident' ? '.incident-take-target-checkbox' : '.issue-take-target-checkbox';
        const qtyClass = prefix === 'incident' ? '.incident-take-row-qty-input' : '.issue-take-row-qty-input';

        if (methodKey === 'sub') {
            if (unit._subHierarchy) {
                return collectHierarchyTakeSelections(prefix, unit);
            }
            const selections = [];
            $(checkboxClass + ':checked').each(function () {
                const key = $(this).val();
                const opt = optionMap[key];
                const qty = parseFloat($(this).closest('.issue-take-target-option').find(qtyClass).val());
                if (!opt || !(qty > 0)) return;
                selections.push({
                    take_key: key,
                    quantity: qty,
                    max_qty: parseFloat(opt.max_qty) || 0,
                    opt,
                });
            });
            return selections;
        }

        const opt = prefix === 'incident' ? getSelectedIncidentTakeOption() : getSelectedTakeOption();
        const qtyInput = prefix === 'incident' ? '#incidentTakeQty' : '#issueTakeQty';
        const qty = parseFloat($(qtyInput).val());
        if (!opt || !(qty > 0)) return [];
        return [{
            take_key: opt.key,
            quantity: qty,
            max_qty: parseFloat(opt.max_qty) || 0,
            opt,
        }];
    }

    function updateTakeLivePreview(prefix, unit) {
        const $preview = prefix === 'incident' ? $('#incidentTakeLivePreview') : $('#issueTakeLivePreview');
        const units = prefix === 'incident' ? getSelectedIncidentUnits() : getSelectedIssueUnits();

        if (!units.length) {
            $preview.hide().empty();
            return;
        }

        if (units.length > 1) {
            const selections = buildMultiLabelMainSelections(units);
            if (!selections.length) {
                $preview.hide().empty();
                return;
            }
            const lines = selections.map(s => {
                const items = s.quantity * (parseFloat(s.opt.items_each) || 1);
                const leafName = s.unit.leaf_unit_name || 'item';
                return `<li><strong>${escapeHtml(s.unit.label)}</strong> â€” 1 Ã— ${escapeHtml(s.opt.label)} = ${items} ${escapeHtml(leafName)}</li>`;
            }).join('');
            const totalItems = selections.reduce((sum, s) =>
                sum + (s.quantity * (parseFloat(s.opt.items_each) || 1)), 0);
            $preview.html(
                `<strong>Main package from ${selections.length} label(s):</strong><ul class="mb-1 pl-3">${lines}</ul>` +
                `<span>Total deducting: <strong>${totalItems}</strong> item(s) across selected labels.</span>`
            ).show();
            return;
        }

        unit = unit || units[0];
        if (!unit) {
            $preview.hide().empty();
            return;
        }
        const methodKey = prefix === 'incident' ? $('#incidentTakeMethod').val() : $('#issueTakeMethod').val();
        const selections = prefix === 'incident' ? collectIncidentTakeSelections() : collectIssueTakeSelections();
        const leafName = unit.leaf_unit_name || 'item';

        if (!selections.length) {
            $preview.hide().empty();
            return;
        }

        const totalItems = selections.reduce((sum, s) => sum + (s.quantity * (parseFloat(s.opt.items_each) || 1)), 0);
        const remainingAfter = Math.max(0, (parseFloat(unit.remaining_items) || 0) - totalItems);
        const lines = selections.map(s => {
            const items = s.quantity * (parseFloat(s.opt.items_each) || 1);
            return `<li><strong>${s.quantity} Ã— ${escapeHtml(s.opt.label)}</strong> = ${items} ${escapeHtml(leafName)}</li>`;
        }).join('');

        $preview.html(
            `<strong>From label "${escapeHtml(unit.label)}":</strong><ul class="mb-1 pl-3">${lines}</ul>` +
            `<span>Total deducting: <strong>${totalItems}</strong> ${escapeHtml(leafName)} Â· ` +
            `Remaining after issue: <strong>${remainingAfter}</strong> ${escapeHtml(leafName)}</span>`
        ).toggle(methodKey === 'sub' || methodKey === 'item' || selections.length > 0);
    }

    function renderIssueTakeTargetCheckboxes(method, unit) {
        $('#issueTakeTarget').hide();
        unit._selectedTakeKey = null;

        if (method.hierarchy && method.hierarchy.units && method.hierarchy.units.length) {
            renderSubPackageHierarchy(method.hierarchy, unit, 'issue');
            return;
        }

        const $list = $('#issueTakeTargetList');
        $list.empty().show();
        $('#issueTakeTargetToolbar').show();
        const leafName = unit?.leaf_unit_name || 'item';
        unit._subHierarchy = null;
        method.options.forEach((opt, idx) => {
            opt.leaf_unit_name = leafName;
            $list.append(buildTakeCheckboxRow(opt, idx, 'issue'));
        });
        $('#issueTakeTargetDetail').text('Check a sub-package level (Carton, Box, â€¦) and enter how many you are taking from this label.');
        updateTakeLivePreview('issue', unit);
    }

    function collectIssueTakeSelections() {
        const units = getSelectedIssueUnits();
        if (!units.length) return [];

        if (units.length > 1) {
            return buildMultiLabelMainSelections(units).map(s => ({
                stock_unitID: s.unit.stock_unitID,
                take_key: s.take_key,
                quantity: 1,
                max_qty: s.max_qty,
                opt: s.opt,
                unit: s.unit,
            }));
        }

        const unit = units[0];
        const structure = getIssueTakeStructure(unit);
        const methodKey = $('#issueTakeMethod').val();

        if (!structure?.show_method_select) {
            const opt = structure.take_options[0];
            return opt ? [{
                stock_unitID: unit.stock_unitID,
                take_key: opt.key,
                quantity: 1,
                max_qty: parseFloat(opt.max_qty) || 1,
                opt,
                unit,
            }] : [];
        }

        if (methodKey === 'main') {
            const opt = getSelectedTakeOption()
                || (structure.methods || []).find(m => m.key === 'main')?.options?.[0];
            if (!opt) return [];
            return [{
                stock_unitID: unit.stock_unitID,
                take_key: opt.key,
                quantity: 1,
                max_qty: parseFloat(opt.max_qty) || 1,
                opt,
                unit,
            }];
        }

        return collectTakeSelectionsFromDom('issue', unit).map(s => ({
            ...s,
            stock_unitID: unit.stock_unitID,
            unit,
        }));
    }

    function renderIssueTakeTargets() {
        const unit = getSelectedIssueUnit();
        const methodKey = $('#issueTakeMethod').val();
        const $target = $('#issueTakeTarget').empty().append('<option value="">Choose...</option>').hide();
        const $list = $('#issueTakeTargetList').empty().hide();
        $('#issueTakeTargetToolbar').hide();
        $('#issueTakeTargetDetail').text('');
        $('#issueTakeTargetWrap').hide();
        if (unit) unit._selectedTakeKey = null;

        if (!unit || !methodKey) {
            updateIssueTakeQtyLimits();
            updateTakeLivePreview('issue', unit);
            return;
        }

        const structure = unit._takeStructure || getIssueTakeStructure(unit);
        const method = (structure?.methods || []).find(m => m.key === methodKey);
        if (!method) {
            updateIssueTakeQtyLimits();
            return;
        }
        if (methodKey === 'sub') {
            if (method.hierarchy?.units?.length || (method.options || []).length) {
                renderIssueTakeTargetCheckboxes(method, unit);
                unit._subHierarchy = method.hierarchy || null;
                $('#issueTakeQtyWrap').hide();
                $('#issueTakeTargetWrap').show();
                updateIssueButton();
                return;
            }
            updateIssueTakeQtyLimits();
            return;
        }
        if (!(method.options || []).length) {
            updateIssueTakeQtyLimits();
            return;
        }

        unit._activeTakeOptions = method.options;

        const targetLabels = {
            main: 'Main Package',
            sub: 'Chagua Sub-Package',
            item: 'Unit Measure',
        };
        $('#issueTakeTargetLabel').html(
            (targetLabels[methodKey] || 'Package / Unit') + ' <span class="text-danger">*</span>'
        );

        if (methodKey === 'main') {
            $('#issueTakeQtyWrap').hide();
            $('#issueTakeQty').val(1).prop('readonly', true);
        } else {
            $('#issueTakeQtyWrap').show();
            $('#issueTakeQty').prop('readonly', false);
        }

        method.options.forEach(opt => {
            $target.append(`<option value="${escapeHtml(opt.key)}">${escapeHtml(opt.label)}</option>`);
        });

        if (method.options.length === 1) {
            $target.val(method.options[0].key);
            unit._selectedTakeKey = method.options[0].key;
            $('#issueTakeTargetDetail').text(method.options[0].detail || '');
            $('#issueTakeTargetWrap').show();
        } else {
            $target.show();
            $('#issueTakeTargetWrap').show();
        }

        updateIssueTakeQtyLimits();
    }

    function getSelectedTakeOption() {
        const unit = getSelectedIssueUnit();
        if (!unit) return null;

        const options = unit._activeTakeOptions || unit.take_options || [];
        const methodKey = $('#issueTakeMethod').val();
        let key = unit._selectedTakeKey;
        if (!key) {
            key = $('#issueTakeTarget').val();
        }
        if (!key && options.length === 1) {
            key = options[0].key;
        }
        return options.find(o => o.key === key) || null;
    }

    function updateIssueTakeQtyLimits() {
        const opt = getSelectedTakeOption();
        const unit = getSelectedIssueUnit();
        const methodKey = $('#issueTakeMethod').val();
        const $qty = $('#issueTakeQty');
        if (!opt) {
            $qty.val(1).prop('readonly', false);
            $('#issueTakeQtyHint').text('');
            return;
        }
        const maxQty = parseFloat(opt.max_qty) || 1;
        const itemsEach = parseFloat(opt.items_each) || 1;
        const leafName = unit?.leaf_unit_name || 'item';

        if (methodKey === 'main' || opt.level === 'package' || opt.level === 'full') {
            $('#issueTakeQtyWrap').hide();
            $qty.val(1).prop('readonly', true);
            $('#issueTakeQtyHint').text('Quantity auto = 1 (whole ' + opt.label + ' from selected label).');
            return;
        }

        $('#issueTakeQtyWrap').show();
        $qty.attr('min', 1);
        $qty.attr('max', maxQty);
        if ((parseFloat($qty.val()) || 0) <= 0) {
            $qty.val(1);
        }
        $qty.prop('readonly', false);
        if (opt.level === 'item') {
            $('#issueTakeQtyHint').text(
                'Max ' + maxQty + ' ' + leafName
                + (opt.detail ? ' Â· ' + opt.detail.replace(/^Request by unit measure\s*/i, '') : '')
            );
        } else {
            $('#issueTakeQtyHint').text(
                'Max ' + maxQty + ' Â· each = ' + itemsEach + ' ' + leafName
                + ' (total items = quantity Ã— ' + itemsEach + ')'
            );
        }
    }

    function hasCanReturnSelected() {
        return getSelectedIssueUnits().some(u => u.requisition_status === 'can_return');
    }

    function toggleExpectedReturnField() {
        if (hasCanReturnSelected()) {
            $('#issueExpectedReturnSection').show();
        } else {
            $('#issueExpectedReturnSection').hide();
            $('#issueExpectedReturnDate').val('');
        }
    }

    function updateIssueButton() {
        const itemId = $('#issueItemSelect').val();
        const units = getSelectedIssueUnits();
        const unit = units.length === 1 ? units[0] : null;
        const structure = unit ? getIssueTakeStructure(unit) : null;
        const needsMethod = units.length === 1 && structure && structure.show_method_select;
        const methodOk = units.length > 1 || !needsMethod || !!$('#issueTakeMethod').val();
        const selections = collectIssueTakeSelections();
        const needsReturnDate = hasCanReturnSelected();
        const hasReturnDate = !!$('#issueExpectedReturnDate').val();
        const returnDateOk = !needsReturnDate || hasReturnDate;
        const methodKey = $('#issueTakeMethod').val();

        let withinStock = true;
        if (unit && selections.length) {
            const totalItems = selections.reduce((sum, s) =>
                sum + (s.quantity * (parseFloat(s.opt?.items_each) || 1)), 0);
            withinStock = totalItems <= (parseFloat(unit.remaining_items) || 0) + 0.00001;
        } else if (selections.length) {
            withinStock = selections.every(s => {
                const u = s.unit || units.find(x => String(x.stock_unitID) === String(s.stock_unitID));
                if (!u) return false;
                const items = s.quantity * (parseFloat(s.opt?.items_each) || 1);
                return items <= (parseFloat(u.remaining_items) || 0) + 0.00001;
            });
        }

        let takeOk = units.length > 0 && methodOk && selections.length > 0
            && selections.every(s => s.quantity > 0 && s.quantity <= s.max_qty)
            && withinStock;
        if (units.length > 1 && selections.length !== units.length) {
            takeOk = false;
        }
        if (units.length && $('#issueTakeSection').is(':visible')) {
            takeOk = takeOk && validateTakeQuantityInputs('issue', unit);
        }
        if (methodKey === 'sub' && unit && unit._subHierarchy) {
            takeOk = takeOk && validateHierarchyQuantities('issue', unit);
        }
        $('#btnIssueItem').prop('disabled', !(itemId && takeOk && returnDateOk));
        toggleExpectedReturnField();
        updateTakeLivePreview('issue', unit);
    }

    $('#issueCategoryFilter').on('change', function () {
        populateIssueItemSelect();
        $('#issueItemSelect').val('').trigger('change');
        $('#issueStockInfo, #issueLabelsSection, #issueTakeSection, #issueExpectedReturnSection').hide();
        $('#issueLabelsList').empty();
        resetIssueTakeFields();
        updateIssueButton();
    });
    $('#issueExpectedReturnDate').on('change', updateIssueButton);
    $('#issueItemSelect').on('change', function () {
        renderIssueLabels($(this).val());
    });
    $(document).on('change', '.issue-label-checkbox', function () {
        renderIssueTakeForLabels();
    });
    $('#issueTakeMethod').on('change', function () {
        renderIssueTakeTargets();
        updateIssueButton();
    });
    $('#issueTakeTarget').on('change', function () {
        const unit = getSelectedIssueUnit();
        if (unit) unit._selectedTakeKey = $(this).val();
        const opt = getSelectedTakeOption();
        $('#issueTakeTargetDetail').text(opt?.detail || '');
        updateIssueTakeQtyLimits();
        updateIssueButton();
    });
    $(document).on('change', '.issue-take-target-checkbox', function () {
        const $row = $(this).closest('.issue-take-target-option');
        const $qtyWrap = $row.find('.issue-take-row-qty');
        const $qtyInput = $row.find('.issue-take-row-qty-input');
        if ($(this).is(':checked')) {
            $qtyWrap.show();
            $qtyInput.prop('disabled', false);
        } else {
            $qtyWrap.hide();
            $qtyInput.prop('disabled', true).val(1);
        }
        updateIssueButton();
    });
    $(document).on('input change', '.issue-take-row-qty-input', updateIssueButton);
    $(document).on('input change', '#issueTakeQty', updateIssueButton);

    $(document).on('change', '.issue-hier-unit-check', function () {
        const $block = $(this).closest('.issue-hier-unit');
        const $body = $block.find('.issue-hier-unit-body');
        if ($(this).is(':checked')) {
            $block.addClass('is-checked');
            $body.slideDown(150);
            if ($block.find('.issue-hier-mode-radio[value="full"]').length) {
                $block.find('.issue-hier-mode-radio[value="full"]').prop('checked', true).trigger('change');
            }
        } else {
            $block.removeClass('is-checked');
            $body.slideUp(150);
        }
        updateIssueButton();
    });
    $(document).on('change', '.issue-hier-mode-radio', function () {
        const $block = $(this).closest('.issue-hier-unit');
        const isPartial = $(this).val() === 'partial';
        const $wrap = $block.find('.issue-hier-partial-wrap');
        const $qty = $wrap.find('.issue-hier-partial-qty');
        if (isPartial) {
            $wrap.show();
            $qty.prop('disabled', false);
        } else {
            $wrap.hide();
            $qty.prop('disabled', true).val(1);
        }
        updateIssueButton();
    });
    $(document).on('input change', '.issue-hier-partial-qty', updateIssueButton);
    $('#issueItemForm').on('submit', async function (e) {
        e.preventDefault();
        const takeCheck = assertTakeOperationValid('issue');
        if (!takeCheck.valid) {
            showAlert(takeCheck.message, 'danger');
            return;
        }
        const selections = collectIssueTakeSelections();

        try {
            const payload = {
                issues: selections.map(s => ({
                    stock_unitID: s.stock_unitID,
                    take_key: s.take_key,
                    quantity: s.quantity,
                })),
                notes: $('#issueNotes').val() || null,
            };
            if (hasCanReturnSelected()) {
                payload.expected_return_date = $('#issueExpectedReturnDate').val();
            }
            const json = await postJson(portalStoreUrl, payload);
            resetIssueForm();
            $('#requestItemModal').modal('hide');
            if (typeof window.onPortalRequestSubmitted === 'function') {
                window.onPortalRequestSubmitted(json);
            }
            showAlert(json.message, 'success');
        } catch (err) {
            showAlert(err.message, 'danger');
        }
    });

    $(function () {
        resetIssueForm();
        $('#requestItemModal').on('show.bs.modal', function () {
            resetIssueForm();
            initIssueSelect2();
        });
        $('#requestItemModal').on('hidden.bs.modal', function () {
            resetIssueForm();
        });
    });
})();
</script>
