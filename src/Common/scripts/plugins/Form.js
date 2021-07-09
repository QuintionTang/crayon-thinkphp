function Form(submitHelper) {
    var This = this;
    this._modalHelper = false;
    this._elem_form = false;
    this._submit_ladda = false;
    /**
     * form提示
     * @param {*} elTarget
     */
    function FormAlert(elTarget) {
        var elText = $(".alert-text", elTarget);
        this.show = function (msg) {
            elText.html(msg);
            elTarget.slideDown();
        };
        this.hide = function () {
            elTarget.hide();
            elText.html("");
        };
    }
    function strip(num, precision = 12) {
        num = parseFloat(num);
        return +parseFloat(num.toPrecision(precision));
    }
    function _addDateSelectEvents(elemTarget) {
        var elem_daterangepicker = $(
                '[data-plugin="daterangepicker"]',
                elemTarget
            ),
            elem_daterangepicker_time = $(
                '[data-plugin="daterangepickertime"]',
                elemTarget
            );
        var monthNames = [
                "一月",
                "二月",
                "三月",
                "四月",
                "五月",
                "六月",
                "七月",
                "八月",
                "九月",
                "十月",
                "十一月",
                "十二月",
            ],
            daysOfWeek = [
                "周日",
                "周一",
                "周二",
                "周三",
                "周四",
                "周五",
                "周六",
            ];
        if (elem_daterangepicker.length > 0) {
            elem_daterangepicker.each(function (item) {
                var _elem_this = $(this);
                _elem_this.daterangepicker(
                    {
                        parentEl: elemTarget,
                        singleDatePicker: true,
                        showDropdowns: true,
                        locale: {
                            format: "YYYY-MM-DD",
                            applyLabel: "确定",
                            cancelLabel: "取消",
                            monthNames: monthNames,
                            daysOfWeek: daysOfWeek,
                        },
                        minYear: parseInt(
                            moment().add(-1, "years").format("YYYY"),
                            10
                        ),
                        maxYear: parseInt(
                            moment().add(2, "years").format("YYYY"),
                            10
                        ),
                        autoApply: true,
                        opens: "left",
                    },
                    function (start, end, label) {}
                );
            });
        }
        if (elem_daterangepicker_time.length > 0) {
            elem_daterangepicker_time.each(function () {
                var _elem_this = $(this);
                _elem_this.daterangepicker(
                    {
                        parentEl: elemTarget,
                        singleDatePicker: true,
                        showDropdowns: true,
                        timePicker: true,
                        minYear: 2021,
                        timePicker24Hour: true,
                        locale: {
                            format: "YYYY-MM-DD hh:mm",
                            applyLabel: "确定",
                            cancelLabel: "取消",
                            monthNames: monthNames,
                            daysOfWeek: daysOfWeek,
                        },
                        autoApply: true,
                        opens: "left",
                    },
                    function (start, end, label) {}
                );
            });
        }
    }
    function _addUploadEvents(elemTarget) {
        var _elem_upload = $(".dropify", elemTarget),
            _upload_devents = false;
        if (_elem_upload.length > 0) {
            var _elem_loader = $(".dropify-loader", elemTarget);
            _upload_devents = _elem_upload.dropify({
                tpl: {
                    loader: '<div class="dropify-loader"></div>',
                },
                messages: {
                    default: "点击或拖拽文件到这里",
                    replace: "点击或拖拽文件到这里来替换文件",
                    remove: "移除文件",
                    error: "对不起，你上传的文件太大了",
                },
            });
            _elem_loader.hide();
            _elem_upload.change(function () {
                var _form_data = new FormData();
                var _upload_config_data = _elem_upload.data();
                var _upload_success = _upload_config_data.successMessage;
                var _files = _elem_upload[0].files[0];
                _form_data.append("file", _files);
                _elem_loader.show();
                _uploadData(
                    _form_data,
                    _upload_config_data.uploadUrl,
                    _upload_config_data.targetid,
                    _upload_success
                );
            });
        }
    }
    function _uploadData(formdata, uploadUrl, elemId, successMessage) {
        var _elem_file_id_input = $(elemId);
        $.ajax({
            url: uploadUrl,
            type: "post",
            data: formdata,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (response) {
                if (response) {
                    if (response.success > 0) {
                        if (response.count) {
                            _closeModal();
                            var _success_message =
                                successMessage || "成功导入工人信息";
                            _success(
                                _success_message + "：" + response.count + "！",
                                "",
                                function () {
                                    window.location.reload();
                                }
                            );
                        } else {
                            if (
                                _elem_file_id_input &&
                                _elem_file_id_input.length > 0
                            ) {
                                _elem_file_id_input.val(response.attach_id);
                            }
                        }
                    } else {
                        _formatErrorMsg(response.message || "服务器异常！");
                    }
                } else {
                    _formatErrorMsg(response.message || "服务器异常！");
                }
                $(".dropify-loader").hide();
            },
        });
    }
    function _formatData(val, valType) {
        var newVal = val;
        switch (valType) {
            case "number":
                if (_.isArray(newVal)) {
                    newVal = _.map(newVal, function (v) {
                        return _.toNumber(v);
                    });
                } else {
                    newVal = _.toNumber(newVal);
                }

                break;
            default:
                newVal = val;
                break;
        }
        return newVal;
    }
    function _getFormData(fields, elemForm) {
        var fromData = {};
        fields.forEach(function (val) {
            var elemField = $(val.element),
                attrData = elemField.data(),
                dataType = attrData.type || "string",
                _name = val.element.name,
                _isGroup = attrData.group,
                _dataVal = val.value,
                _formatVal = _formatData(_dataVal, dataType);
            if (_isGroup) {
                var _data_name = attrData.name;
                if (_.isArray(_formatVal)) {
                    if (fromData[_data_name]) {
                        fromData[_data_name] = _.concat(
                            fromData[_data_name],
                            _formatVal
                        );
                        // fromData[_data_name].push(_formatVal);
                    } else {
                        fromData[_data_name] = _formatVal;
                    }
                } else {
                    if (fromData[_data_name]) {
                        fromData[_data_name].push(_formatVal);
                    } else {
                        fromData[_data_name] = [_formatVal];
                    }
                }
            } else {
                if (_name != "" && dataType != "array") {
                    fromData[_name] = _formatVal;
                }
            }
        });
        var elemRepeater = $(".repeater-params", elemForm);
        if (elemRepeater && elemRepeater.length > 0) {
            var repeaterFieldName = elemRepeater.data("name"),
                repeaterTarget = elemRepeater.data("target"),
                repeaterType = elemRepeater.data("type"),
                repeaterVal = elemRepeater.repeaterVal();
            if (fromData && repeaterVal && repeaterVal[repeaterTarget]) {
                if (repeaterType == "string") {
                    fromData[repeaterFieldName] = JSON.stringify(
                        repeaterVal[repeaterTarget]
                    );
                } else {
                    fromData[repeaterFieldName] = repeaterVal[repeaterTarget];
                }
            }
        }
        return fromData;
    }
    function _ajaxSelect(elSelector, selectorClass) {
        var _el_select_box = elSelector.parent(),
            _el_select_value = $("input." + selectorClass, _el_select_box);
        elSelector.ajax_select({
            field: _el_select_value,
        });
    }

    function _addAjaxSelectEvents(elemForm) {
        var _el_ajax_select = $(".ajax-select", elemForm);
        if (_el_ajax_select.length > 0) {
            _ajaxSelect(_el_ajax_select, "ajax-select-val");
        }
    }
    function _addEvents(elemTarget) {
        var elemForms = elemTarget
            ? $(".form-parsley", elemTarget)
            : $(".form-parsley");
        elemForms.each(function (index, elem) {
            var thisForm = $(elem),
                btnLadda = $(".ladda-button", thisForm),
                formElId = ["#", thisForm.attr("id")].join(""),
                submitLadda = false,
                formAlert = new FormAlert($(".alert-form-box", thisForm)),
                formName = thisForm.attr("name");
            if (btnLadda.length > 0) {
                submitLadda = Ladda.create(
                    document.querySelector(formElId + " .ladda-button")
                );
            }
            This._submit_ladda = submitLadda;
            thisForm
                .parsley()
                .on("form:submit", function (evt, elem) {
                    return false;
                })
                .subscribe("form:submit", function (evtName, args) {
                    submitLadda && submitLadda.toggle();
                    formAlert.hide();
                    if (typeof submitHelper[formName] === "function") {
                        submitHelper
                            .init(formAlert, submitLadda, thisForm)
                            [formName](_getFormData(args.fields, thisForm));
                    }
                    return false;
                });
            _addAjaxSelectEvents(thisForm);
            _unionSelect(thisForm);
        });
    }

    function _unionSelect(elemTarget) {
        var _elem_selects = $(".unionSelect", elemTarget);
        if (_elem_selects.length > 0) {
            var _select_data = _elem_selects.data(),
                _elem_target_box = $(_select_data.target),
                _uri = _select_data.uri;
            _elem_selects.unbind("change").bind("change", function () {
                var selVal = $(this).val();
                if (selVal != "0" || selVal != "") {
                    $.getJSON(
                        _uri,
                        {
                            id: selVal,
                        },
                        function (result) {
                            if (result.status) {
                                var data = result.data,
                                    html = [];
                                html.push(
                                    '<option value="0">-- 请选择石头尺寸 --</option>'
                                );
                                for (
                                    var i = 0, len = data.length;
                                    i < len;
                                    i++
                                ) {
                                    var _meter_info = data[i];
                                    html.push(
                                        '<option title="' +
                                            _meter_info.size_weight +
                                            '" value="' +
                                            _meter_info.id +
                                            '">' +
                                            _meter_info.size_value +
                                            "（mm）：" +
                                            _meter_info.size_weight +
                                            "（ct）</option>"
                                    );
                                }
                                _elem_target_box.html(html.join("")).show();
                            }
                        }
                    );
                }
            });
        }

        var _elem_size_select = $("#jewel_size_box");
        if (_elem_size_select.length > 0) {
            var _weight_unit = 0,
                _actual_count = 0;
            var _elem_actual = $("#actual_count"),
                _elem_actual_weight = $("#actual_weight");
            _elem_size_select.on("select2:select", function (e) {
                var data = e.params.data,
                    title = data.title;
                _weight_unit = strip(title, 3);
                _elem_actual_weight.val(strip(_actual_count * _weight_unit));
            });

            _elem_actual
                .unbind("input propertychange")
                .bind("input propertychange", function () {
                    _actual_count = strip($(this).val());
                    _elem_actual_weight.val(
                        strip(_actual_count * _weight_unit)
                    );
                });
        }
    }

    function _ajaxData(url, data, method) {
        var strArrayData = [];
        for (var key in data) {
            strArrayData.push(
                [key, "=", encodeURIComponent(data[key])].join("")
            );
        }
        var deferred = $.Deferred(),
            ajaxOpts = {
                type: method || "POST",
                url: url,
                contentType: "application/x-www-form-urlencoded",
                success: function (response) {
                    deferred.resolve(response, false);
                },
                error: function (res) {
                    deferred.resolve(false, {
                        status: res.status || 0,
                        code: -1,
                        message: _formatErrorMsg(res),
                    });
                },
            };
        if (data) {
            ajaxOpts["data"] = strArrayData.join("&");
        }
        $.ajax(ajaxOpts);
        return deferred.promise();
    }
    function _toastr(
        heading,
        body,
        position,
        loaderBgColor,
        icon,
        hideAfter,
        stack,
        showHideTransition,
        beforeHidden
    ) {
        // default
        if (!hideAfter) hideAfter = 3000;
        if (!stack) stack = 1;

        var options = {
            heading: heading,
            text: body,
            position: position,
            loaderBg: loaderBgColor,
            icon: icon,
            hideAfter: hideAfter,
            stack: stack,
            beforeHidden: beforeHidden,
        };

        if (showHideTransition) options.showHideTransition = showHideTransition;
        $.toast().reset("all");
        return $.toast(options);
    }
    function _success(title, msg, callback) {
        var _success_timeout = false,
            _timeout = 2,
            _auto_tip =
                '<strong><i class="number">' +
                _timeout +
                "秒</i>后将自动关闭！</strong>";
        if (msg !== "") {
            msg += "<br/>" + _auto_tip;
        } else {
            msg = _auto_tip;
        }

        var _result_notify = _toastr(
            title,
            msg,
            "top-right",
            "#5ba035",
            "success",
            2000,
            1,
            "slide",
            function () {
                if (_success_timeout) {
                    clearTimeout(_success_timeout);
                    _success_timeout = false;
                }
                _result_notify.close();
                callback.call();
            }
        );

        // swal.fire({
        //     title: title,
        //     html: msg,
        //     type: 'success',
        //     showCancelButton: false,
        //     confirmButtonColor: '#5c90d2',
        //     confirmButtonText: '确定'
        // }, function () {
        //     if (_success_timeout){
        //         clearTimeout(_success_timeout);
        //         _success_timeout = false;
        //     }
        //     swal.close();
        //     callback.call();
        // });

        setTimeout(function () {
            _result_notify.close();
            callback.call();
        }, 1000);
    }
    function _closeModal() {
        if (This._modalHelper && This._modalHelper.closethis) {
            This._modalHelper.closethis();
        }
    }
    function _ajaxSuccess(result) {
        if (result.status == 1) {
            _closeModal();
            var _go_url = "",
                _data = result.data;
            if (_data !== "" && _data.url) {
                _go_url = _data.url;
            }
            _success("操作成功！", result.msg, function () {
                if (_go_url !== "") {
                    window.location.href = _go_url;
                } else {
                    window.location.reload();
                }
                This._submit_ladda && This._submit_ladda.stop();
            });
        } else {
            if (result.status == 2) {
                This._modalHelper.update(result.data);
            } else {
                _formatErrorMsg(result.msg);
            }
            This._submit_ladda && This._submit_ladda.stop();
        }
    }
    function _newFormEvents(elemTarget) {
        This._elem_form = elemTarget;
        var elemForms = elemTarget;
        if ($("form", elemForms).length > 0) {
            elemForms.each(function (index, elem) {
                var thisForm = $("form", elem),
                    btnLadda = $(".ladda-button", thisForm),
                    elemSubmitButtonId = ["#", elem.id + " .ladda-button"].join(
                        ""
                    ),
                    submitLadda = false,
                    formAlert = new FormAlert($(".alert-form-box", thisForm)),
                    elemIsAjax = $("input[name='ajax']", thisForm),
                    isAjax = elemIsAjax.val() != "1",
                    formName = thisForm.attr("data-action");
                if (btnLadda.length > 0) {
                    submitLadda = Ladda.create(
                        document.querySelector(elemSubmitButtonId)
                    );
                }
                This._submit_ladda = submitLadda;
                thisForm
                    .parsley({
                        trigger: "focusout",
                    })
                    .on("form:submit", function (evt, elem) {
                        return isAjax;
                    })
                    .subscribe("form:submit", function (evtName, args) {
                        submitLadda && submitLadda.toggle();
                        formAlert.hide();
                        var formData = _getFormData(args.fields, thisForm);
                        _ajaxData(thisForm.attr("action"), formData).then(
                            function (res, error) {
                                if (error) {
                                    console.log(error);
                                } else {
                                    _ajaxSuccess(res);
                                }
                            }
                        );
                        // if (typeof submitHelper[formName] === "function") {
                        //     submitHelper
                        //         .init(formAlert, submitLadda, thisForm)
                        //         [formName](_getFormData(args.fields, thisForm));
                        // }
                        return false;
                    });
                $(elemSubmitButtonId)
                    .unbind("click")
                    .bind("click", function () {
                        thisForm.submit();
                    });
                _addAjaxSelectEvents(thisForm);
            });
        }

        if ($('[data-plugin="multiselect"]').length > 0)
            $('[data-plugin="multiselect"]').multiSelect($(this).data());
        return elemForms;
    }
    function _formatErrorMsg(errorMsg) {
        if (This._elem_form !== false) {
            This.error(errorMsg, This._elem_form);
        } else {
            console.error(errorMsg);
        }
        This._submit_ladda && This._submit_ladda.toggle();
    }
    function _diyFormEvents(elemTarget, submitCallback) {
        This._elem_form = elemTarget;
        var elemForms = elemTarget;
        if ($("form", elemForms).length > 0) {
            elemForms.each(function (index, elem) {
                var thisForm = $("form", elem),
                    btnLadda = $(".ladda-button", thisForm),
                    elemSubmitButtonId = ["#", elem.id + " .ladda-button"].join(
                        ""
                    ),
                    submitLadda = false,
                    formAlert = new FormAlert($(".alert-form-box", thisForm)),
                    elemIsAjax = $("input[name='ajax']", thisForm),
                    isAjax = elemIsAjax.val() != "1",
                    formName = thisForm.attr("data-action");
                if (btnLadda.length > 0) {
                    submitLadda = Ladda.create(
                        document.querySelector(elemSubmitButtonId)
                    );
                }
                This._submit_ladda = submitLadda;
                thisForm
                    .parsley({
                        trigger: "focusout",
                    })
                    .on("form:submit", function (evt, elem) {
                        return isAjax;
                    })
                    .subscribe("form:submit", function (evtName, args) {
                        submitLadda && submitLadda.toggle();
                        formAlert.hide();
                        var formData = _getFormData(args.fields, thisForm);

                        submitCallback(
                            thisForm.attr("action"),
                            formData,
                            formAlert,
                            This._submit_ladda
                        );
                        return false;
                    });
                $(elemSubmitButtonId)
                    .unbind("click")
                    .bind("click", function () {
                        thisForm.submit();
                    });
                _addAjaxSelectEvents(thisForm);
            });
        }

        if ($('[data-plugin="multiselect"]').length > 0)
            $('[data-plugin="multiselect"]').multiSelect($(this).data());
        return elemForms;
    }
    this.init = function () {
        _addEvents(false);
    };
    this.reset = function (elemTarget, modalHelper) {
        this._modalHelper = modalHelper;
        this._elem_form = elemTarget;
        _addUploadEvents(elemTarget);
        _addDateSelectEvents($(".modal-body", elemTarget));
        _newFormEvents(elemTarget);
        _unionSelect(elemTarget);
        $(".select2", elemTarget).select2({
            dropdownAutoWidth: true,
            language: {
                errorLoading: function () {
                    return "无法载入结果。";
                },
                inputTooLong: function (n) {
                    return "请删除" + (n.input.length - n.maximum) + "个字符";
                },
                inputTooShort: function (n) {
                    return (
                        "请再输入至少" + (n.minimum - n.input.length) + "个字符"
                    );
                },
                loadingMore: function () {
                    return "载入更多结果…";
                },
                maximumSelected: function (n) {
                    return "最多只能选择" + n.maximum + "个项目";
                },
                noResults: function () {
                    return "未找到相应的选项";
                },
                searching: function () {
                    return "搜索中…";
                },
                removeAllItems: function () {
                    return "删除所有项";
                },
            },
        });
    };
    this.error = function (errorMsg, elemForm) {
        // submitHelper.cusError(errorMsg);
        var _form_alert = new FormAlert($(".alert-form-box", elemForm));
        _form_alert.show(errorMsg);
    };
    this.hideError = function (elemForm) {
        // submitHelper.cusError(errorMsg);
        var _form_alert = new FormAlert($(".alert-form-box", elemForm));
        _form_alert.hide();
    };
    this.success = function (title, msg, callback) {
        _success(title, msg, callback);
    };
    this.ajax = function (url, data) {
        _ajaxData(url, data).then(function (res, error) {
            if (error) {
                console.log(error);
            } else {
                _ajaxSuccess(res);
            }
        });
    };
    this.submitForm = function (formAction, formData, callback) {
        _ajaxData(formAction, formData).then(function (res, error) {
            callback(res, error);
        });
    };
}
