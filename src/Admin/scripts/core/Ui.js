function Ui() {
    var This = this;

    function _addSearchEvents(){
        var _elem_search_form = $("form.search-form");
        if (_elem_search_form.length>0){
            var _elem_selects = $("select",_elem_search_form);
            _elem_selects.each(function (index, elem) {
                $(elem).bind("change", function () {
                    var selVal = $(this).val();
                    if (selVal!="0" || selVal!=""){
                        _elem_search_form.submit();
                    }
                });
            });
        }
    }
    function _addEvents() {
        var _form_helper = new Form(),
            _event_buttons = $("a.events-button");
        if (_event_buttons.length > 0) {
            var _customModal = new CusModal(_form_helper);

            _customModal.before = function (data, callback) {
                if (data.acttype === "ajax") {
                    var _url = data.uri;
                    if (data.confirm && data.confirm == "confirm") {
                        swal.fire({
                            title: data.msg,
                            html: "删除后内容无法恢复！",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#5c90d2",
                            confirmButtonText: "确定删除",
                            cancelButtonColor: "#f5f5f5",
                            cancelButtonText: "取消",
                            onClose: false,
                        }).then(function (result) {
                            if (result.value) {
                                $.getJSON(_url, function (result) {
                                    if (result.status == 1) {
                                        _form_helper.success(
                                            "操作成功！",
                                            result.msg,
                                            function () {
                                                window.location.reload();
                                            }
                                        );
                                    } else {
                                        swal.fire(
                                            "操作失败",
                                            result.msg,
                                            "error"
                                        );
                                        //_that.error(result.msg);
                                    }
                                });
                            }
                        });
                    } else {
                        $.getJSON(_url, function (result) {
                            if (result.status == 1) {
                                data.eBody.html(result.data);

                                _form_helper.reset(data.eModal, _customModal);
                                _initTable(data.eBody);
                                callback.call();
                            } else {
                                console.log("error");
                            }
                        });
                    }
                }
            };
            _customModal.init();
        }
        
        var _elem_ajax_content = $(".ajax-post-body");
        if (_elem_ajax_content.length > 0) {
            _form_helper.reset(_elem_ajax_content);
        }
    }
    function _initTable(elemBody) {
        var _elem_tables = $(".custom-table", elemBody);
        _.forEach(_elem_tables, function (elem) {
            var _table_methods = new CustomTable($(elem));
            _table_methods.init();
        });
    }
    this.init = function (action, options) {
        _addEvents();
        _addSearchEvents();
        _initTable($(".page-content"));
    };
}
