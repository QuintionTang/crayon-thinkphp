function AjaxTable() {
    var This = this;
    var _errorHelper = new Error();
    this._page_size = 20;
    this._filterOptions = {};
    this._tableHelper;
    this._elemTable = false;
    this._apiEndpoint = "";
    this._total_rows = 0;
    this._elem_total = false;
    this._new_total_rows = 0;
    this._footer_operation = false;
    this._current_url = false;
    /**
     * 更新过滤器
     * @param {*} filedName
     * @param {*} fieldVal
     * @param {*} tableObject
     */
    function _changeFilter(filedName, fieldVal) {
        if (fieldVal != "") {
            This._filterOptions[filedName] = fieldVal;
        } else {
            if (This._filterOptions[filedName]) {
                delete This._filterOptions[filedName];
            }
        }
    }
    function _getUrl(ajaxUrl, params) {
        if (params) {
            var _url_info = ajaxUrl.split("?"),
                _url_params = [],
                _join_str = "?",
                _new_url,
                _new_params;
            if (_url_info.length > 1) {
                var _current_params = _url_info[1].split("&"),
                    _current_params_map = {};
                _.forEach(_current_params, function (val) {
                    var _tmp = val.split("=");
                    _current_params_map[_tmp[0]] = _tmp[1];
                });
                _new_params = _.assign(_current_params_map, params);
                _new_url = _url_info[0];
            } else {
                _new_params = params;
                _new_url = ajaxUrl;
            }
            for (var key in _new_params) {
                _url_params.push([key, _new_params[key]].join("="));
            }
            if (_url_params.length === 0) {
                _join_str = "";
            }
            return [_new_url, _url_params.join("&")].join(_join_str);
        } else {
            return ajaxUrl;
        }
    }

    /**
     * 发送最新的过滤请求
     * @param {*} filterOptions
     * @param {*} tableObject
     */
    function _doneFilter(filterOptions, tableHelper) {
        var _ajax_url = _getUrl(This._apiEndpoint, filterOptions);
        if (This._apiEndpoint != "") {
            This._current_url = _ajax_url;
        }

        tableHelper && tableHelper.ajax.url(_ajax_url).load();
    }
    function _refresh() {
        This._current_url = This._current_url || This._apiEndpoint;
        This._tableHelper.ajax.url(This._current_url).load();
    }
    function _isNumber(value) {
        var re = /^[\d|\.]*$/;
        if (!re.test(value)) {
            return false;
        }
        return true;
    }
    function _filterOr(defaultName, fieldsData, value) {
        if (fieldsData) {
            var _or_type = fieldsData.orType,
                _or_filed = fieldsData.orField;
            if (_or_type == "number" && _isNumber(value)) {
                delete This._filterOptions[defaultName];
                return _or_filed;
            } else {
                delete This._filterOptions[_or_filed];
                return defaultName;
            }
        } else {
            return defaultName;
        }
    }
    function _getTimestamp(date) {
        try {
            return new Date(date).getTime() / 1000;
        } catch (error) {
            return 0;
        }
    }
    function _updateFilters(elemFilterBox) {
        var _elem_filter_inputs = $(".filter-input", elemFilterBox);
        _elem_filter_inputs.each(function (index, elem) {
            var _elem_this = $(this),
                _elem_name = _elem_this.attr("name"),
                _elem_type = _elem_this.attr("type"),
                _elem_data = _elem_this.data(),
                _elem_value = $.trim($(elem).val());
            if (_elem_data.type == "range-datepicker") {
                var _data_names = _.split(_elem_name, "-"),
                    _data_values = _.split(_elem_value, " - ");
                if (_data_values.length > 1) {
                    This._filterOptions[_data_names[0]] = _getTimestamp(
                        [_data_values[0], "00:00:00"].join(" ")
                    );
                    This._filterOptions[_data_names[1]] = _getTimestamp(
                        [_data_values[1], "23:59:59"].join(" ")
                    );
                } else {
                    delete This._filterOptions[_data_names[0]];
                    delete This._filterOptions[_data_names[1]];
                }
            } else {
                switch (_elem_type) {
                    default:
                        _changeFilter(
                            _filterOr(_elem_name, _elem_data, _elem_value),
                            _elem_value
                        );
                        break;
                }
            }
        });
        _doneFilter(This._filterOptions, This._tableHelper);
    }
    function _addFilterEvents(elTarget) {
        var _elem_filter_box = $(elTarget || "#table_filter"),
            _elem_search_button = $("button.button-search", _elem_filter_box);
        var _set_selected = function (name, value) {
            _changeFilter(name, value);
            _doneFilter(This._filterOptions, This._tableHelper);
        };
        if (_elem_search_button.length > 0) {
            var _elem_auto_filter = $(".filter-auto-input", _elem_filter_box);
            _elem_search_button.bind("click", function () {
                _updateFilters(_elem_filter_box);
            });
            if (_elem_auto_filter.length > 0) {
                _elem_auto_filter.each(function (index, elem) {
                    $(elem).bind("change", function () {
                        var selVal = $(this).val();
                        _set_selected(elem.name, selVal);
                    });
                });
            }
        } else {
            var _elem_filter_inputs = $(".filter-input", _elem_filter_box);

            _elem_filter_inputs.each(function (index, elem) {
                var _elem = $(elem);
                _elem.bind("change", function () {
                    var selVal = $(this).val();
                    _set_selected(elem.name, selVal);
                });
                var _elem_selected = $("option:selected", _elem);
                if (_elem_selected.length > 0 && _elem_selected.val() != "") {
                    _set_selected(elem.name, _elem_selected.val());
                }
            });
        }

        var _elem_filter_tabs = $(".filter-tabs li a", _elem_filter_box);
        _elem_filter_tabs.bind("click", function () {
            var _elem = $(this),
                _data = _elem.data();
            _set_selected(_data.name, _data.id);
        });
        _elem_filter_tabs.eq(0).trigger("click");
    }
    /**
     * 格式化表格列信息
     * @param {*} fields
     */
    function _formatColumns(fields) {
        var _columns = [];
        fields.forEach(function (val) {
            _columns.push(
                (function () {
                    if (val && val.defaultContent == "") {
                        return val;
                    } else {
                        return {
                            data: val,
                        };
                    }
                })()
            );
        });
        return _columns;
    }
    function _formatResponseData(json) {
        if (json && json.code == 0 && json.data) {
            json.recordsTotal = json.total_count || 0;
            json.recordsFiltered = json.total_count || 0;
            json.pages = This._page_size;
        } else {
            json.recordsTotal = 0;
            json.recordsFiltered = 0;
            json.data = [];
        }
        if (This._footer_operation) {
            if (json.recordsTotal === 0) {
                This._footer_operation.setDisabled();
            } else {
                This._footer_operation.setEnable();
            }
        }
        if (This._elem_total !== false) {
            This._elem_total.text(json.recordsTotal);
        }
    }
    function _formatRequestData(req) {
        var orders = req.order;
        if (orders && orders.length > 0) {
            var order = orders[0],
                columns = req.columns,
                column_name = columns[order.column].data;

            return {
                orderby: column_name,
                sort: order.dir || false,
                offset: req.start,
                limit: req.length,
            };
        } else {
            return {
                orderby: "id",
                //sort: order.dir || false,
                offset: req.start,
                limit: req.length,
            };
        }
    }
    function _getCheckedValue(elemCheckeds) {
        var new_array = [];
        _.forEach(elemCheckeds, function (elem) {
            new_array.push(parseInt($(elem).val()));
        });
        return new_array;
    }
    var _getCheckedRows = function () {
        var _elem_checkeds = $("input[name='ids[]']:checked", This._elemTable),
            _array_values = _getCheckedValue(_elem_checkeds);
        return _array_values;
    };

    function _addTableOperationEvents() {
        var _elem_buttons = $(".table-operation-button", This._elemTable);
        if (_elem_buttons.length === 0) {
            return false;
        }
        var _showButtons = function (elemButtons, status) {
            elemButtons.hide();
            switch (status) {
                case 1: // 可编辑
                    elemButtons.eq(2).show();
                    elemButtons.eq(4).show();
                    break;
                case 2: //可删除
                    elemButtons.eq(3).show();
                    elemButtons.eq(4).show();
                    break;
                default:
                    elemButtons.eq(0).show();
                    elemButtons.eq(1).show();
                    break;
            }
        };

        var _resetCheckbox = function (elemCheckboxs) {
            elemCheckboxs.prop("checked", false);
        };
        _elem_buttons.bind("click", function () {
            var _elem_this = $(this),
                _elem_checkbox_controls = $(".custom-control", This._elemTable),
                _elem_checkboxs = $(
                    "input[name='ids[]']",
                    _elem_checkbox_controls
                ),
                _elem_general_id = $(".general", This._elemTable),
                _this_data = _elem_this.data(),
                _operation = _this_data.operation;
            _elem_general_id.hide();
            _elem_checkbox_controls.show();
            switch (_operation) {
                case "edit-rows":
                    _showButtons(_elem_buttons, 1);
                    break;
                case "delete-rows":
                    _showButtons(_elem_buttons, 2);
                    break;
                // case "delete":
                //     _getCheckedRows(function(selectedVals) {
                //         This.delete(selectedVals);
                //     });
                //     break;
                // case "edit":
                //     _getCheckedRows(function(selectedVals) {
                //         var _selected_data = _.filter(
                //             This._tableHelper.data(),
                //             function(val) {
                //                 return _.indexOf(selectedVals, val.id) >= 0;
                //             }
                //         );
                //         This.batchEdit(_selected_data);
                //     });
                //     break;
                case "cancel":
                    _elem_checkbox_controls.hide();
                    _elem_general_id.show();
                    _resetCheckbox(_elem_checkboxs);
                    _showButtons(_elem_buttons, 0);
                    break;
            }
        });
        _showButtons(_elem_buttons, 0);
        return {
            reset: function () {
                _elem_buttons.eq(4).trigger("click");
                //_showButtons(_elem_buttons, 0);
            },
            setDisabled: function () {
                _elem_buttons.addClass("disabled");
            },
            setEnable: function () {
                _elem_buttons.removeClass("disabled");
            },
        };
    }
    function _addCheckbox(elTds, data) {
        if (data.id) {
            var _primary_key = data.id,
                _elem_checkbox_id = "row_chk_" + _primary_key;

            elTds
                .eq(0)
                .html(
                    '<div class="custom-control custom-checkbox hidden-checkbox"><input type="checkbox" class="custom-control-input" name="ids[]" value="' +
                        _primary_key +
                        '" id="' +
                        _elem_checkbox_id +
                        '"><label class="custom-control-label" for="' +
                        _elem_checkbox_id +
                        '"><i class="number">&nbsp;' +
                        _primary_key +
                        "</i></label></div>" +
                        '<i class="number general">' +
                        _primary_key +
                        "</i>"
                );
        }
    }
    //aTargets: [1, 2, 3, 4, 5, 6],
    // sort: true,
    // order: [[0, "asc"]]
    function _aoColumnDefs(sort, order, columns, aTargets) {
        var _defs = { bSortable: false, aTargets: aTargets },
            _columns_index = [];
        if (aTargets && order) {
            var _aTargets = [];
            var _has_zero = false;

            if (sort && order) {
                var _start_index = 0;
                _.forEach(order, function (val) {
                    if (val[0] > 0) {
                        _has_zero = false;
                        _aTargets.push(val[0]);
                    } else {
                        _has_zero = true;
                    }
                });
                if (_has_zero) {
                    _start_index = 1;
                }
                for (var i = _start_index, len = columns.length; i < len; i++) {
                    _columns_index.push(i);
                }
            }

            var _new_targets = _.difference(_columns_index, aTargets);
            _defs = { bSortable: false, aTargets: _new_targets };
        }
        return [_defs];
    }
    /**
     * ajax分页表格
     * @param {*} objId
     * @param {*} serOpts
     * @param {*} opts
     * @param {*} formatMethod
     */
    function _loadData(objId, serOpts, opts, formatMethod) {
        This._elemTable = $(objId);
        if (opts.pageLength) {
            This._page_size = opts.pageLength;
        }
        var _ajax_url = serOpts.url;
        if (This._filterOptions) {
            _ajax_url = _getUrl(_ajax_url, This._filterOptions);
        }
        var _opts = $.extend(
                {
                    pageLength: This._page_size,
                    sort: false,
                    order: false,
                    aTargets: false,
                    dataSrc: [],
                    columns: [],
                    info: true,
                    scroll: false,
                    paging: true,
                },
                opts
            ),
            _datatableOpts = {
                lengthMenu: [
                    [10, 20, 50],
                    [10, 20, 50],
                ],
                pageLength: _opts.pageLength,
                processing: true,
                serverSide: true,
                deferRender: true,
                lengthChange: false,
                paging: _opts.paging,
                oLanguage: {
                    sLengthMenu: "每页显示 _MENU_ 条记录",
                    sZeroRecords: "暂无任何数据记录",
                    sInfo: "当前显示 _START_ 到 _END_ 条，共 _TOTAL_条记录",
                    sInfoEmtpy: "找不到相关数据",
                    sInfoFiltered: " 数据表中共为 _MAX_ 条记录",
                    sProcessing: "数据加载中...",
                    sSearch: "搜索",
                    oPaginate: {
                        sFirst: "第一页",
                        sPrevious: " 上一页 ",
                        sNext: " 下一页 ",
                        sLast: " 最后一页 ",
                    },
                },
                language: {
                    sInfoEmpty: "",
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>",
                    },
                },
                filter: false,
                info: _opts.info,
                sort: _opts.sort,
                order: _opts.order,
                aoColumnDefs: _aoColumnDefs(
                    _opts.sort,
                    _opts.order,
                    _opts.columns,
                    _opts.aTargets
                ),
                autoWidth: false,
                ajax: {
                    url: _ajax_url,
                    dataSrc: _opts.dataSrc,
                    data: function (req) {
                        return _formatRequestData(req);
                    },
                    error: function (error) {
                        switch (error.status) {
                            case 401:
                                window.location.href = "./login.html";
                                break;
                            case 403:
                                _errorHelper.noPermission(error);
                                This.drawCallback(false, false, {
                                    status: 403,
                                });
                                break;
                            default:
                                if (
                                    error.responseJSON &&
                                    error.responseJSON.code == "60000"
                                ) {
                                    _formatResponseData(error.responseJSON);
                                }
                                break;
                        }
                    },
                },
                columns: _formatColumns(_opts.columns),
                createdRow: function (row, data, index) {
                    var tds = $("td", row);
                    _addCheckbox(tds, data);
                    This.formatRow(tds, data, index, row);
                },
                drawCallback: function (e) {
                    var elem_paginate = $(e.aanFeatures.p),
                        table_dataset = e.json || {
                            recordsTotal: 0,
                            pages: This._page_size,
                        },
                        total = table_dataset.recordsTotal,
                        page_size = table_dataset.pages;
                    if (total === 0 || total <= page_size) {
                        elem_paginate.hide();
                    } else {
                        elem_paginate.show();
                    }
                    if (
                        $('[data-plugin="tippy"]', This._elemTable).length > 0
                    ) {
                        tippy('[data-plugin="tippy"]');
                    }

                    This.drawCallback(e, this.api());
                },
            };
        if (_opts.scroll) {
            var _scroll_opts = _opts.scroll;
            _datatableOpts.scrollY = _scroll_opts.scrollY || false;
            _datatableOpts.scrollCollapse =
                _scroll_opts.scrollCollapse || false;
        }

        return This._elemTable
            .on("xhr.dt", function (e, settings, json, xhr) {
                _formatResponseData(json);
            })
            .DataTable(_datatableOpts);
    }
    this.delete = function (ids) {};
    this.batchEdit = function (rowsData) {};
    this.refreshBefore = function () {};
    this.formatRow = function (tds, data, index, row) {};
    this.drawCallback = function (event, api, error) {};
    this.addFilter = function (elTarget) {
        _addFilterEvents(elTarget);
        return this;
    };
    this.init = function (objId, serOpts, opts, formatMethod) {
        This._apiEndpoint = serOpts.url;
        This._footer_operation = _addTableOperationEvents();
        This._tableHelper = _loadData(objId, serOpts, opts, formatMethod);
        $('.btn[data-plugin="refresh"]').bind("click", function () {
            This.refresh();
        });
        return this;
    };
    this.refresh = function (elemTarget) {
        This.refreshBefore();
        if (This._footer_operation && This._footer_operation.reset) {
            This._footer_operation.reset();
        }
        if (elemTarget) {
            This._elem_total = elemTarget;
        }
        _refresh();
    };
    this.footReset = function () {
        if (This._footer_operation && This._footer_operation.reset) {
            This._footer_operation.reset();
        }
    };
    this.changeFilter = function (filedName, fieldValue) {
        _changeFilter(filedName, fieldValue);
        _doneFilter(This._filterOptions, This._tableHelper);
    };
    this.getRows = function () {
        var _checked_ids = _getCheckedRows();
        var _selected_data = _.filter(This._tableHelper.data(), function (val) {
            return _.indexOf(_checked_ids, val.id) >= 0;
        });
        return _selected_data;
    };
    this.getRowsId = function () {
        return _getCheckedRows();
    };
    this.getRowData = function (id) {
        var _selected_data = _.find(This._tableHelper.data(), function (val) {
            return val.id == id;
        });
        return _selected_data;
    };
}
