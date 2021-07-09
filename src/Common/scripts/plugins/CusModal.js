function CusModal(formHelper) {
    var This = this;
    this._formHelper = formHelper;
    this._modal = false;
    this._primary = false;
    this._isShow = false;
    this._close_status = true;
    this._eBody = false;
    this._eModal = false;

    function _addModalEvents(elemSelector) {
        var _clickFunction = function (e) {
            e.preventDefault();

            var _elem_this = $(this),
                _this_data = _elem_this.data(),
                _target_id = _this_data.targetid || _elem_this.attr("href"),
                _target_modal = $(_target_id),
                _modal_title = $(".custom-modal-title", _target_modal),
                _elem_close_button = $(
                    ".modal-header button.close",
                    _target_modal
                );
            if (This._close_status) {
                _elem_close_button.show();
            } else {
                _elem_close_button.hide();
            }
            _this_data.eBody = $(".modal-body", _target_modal);
            This._eBody = _this_data.eBody;
            _this_data.eModal = _target_modal;
            This._eModal = _target_modal;
            _this_data.mybtn = _elem_this;
            _modal_title.text(_this_data.title);
            _this_data.primaryInfo = This._primary || _this_data.id;
            This.before(_this_data, function () {
                This._isShow = true;
                This._modal = new Custombox.modal({
                    content: {
                        target: _target_id,
                        effect: _this_data.animation,
                        onClose: function () {
                            This._eBody.html("");
                            This._isShow = false;
                        },
                    },
                    loader: {
                        color: "#0077ff",
                    },
                    overlay: {
                        color: _this_data.overlaycolor,
                        close: false,
                    },
                });
                This._modal.open();
            });
        };
        $('.events-button[data-plugin="custommodal"]', elemSelector || "body")
            .unbind("click")
            .bind("click", _clickFunction);
    }
    this.setPrimary = function (primary) {
        this._primary = primary;
    };
    this.close = function () {
        Custombox.modal.closeAll();
        This._isShow = false;
    };
    this.closethis = function () {
        Custombox.modal.close();
        This._isShow = false;
    };
    this.update = function (newHtml) {
        This._eBody.html(newHtml);

        This._formHelper.reset(This._eModal, This);
    };
    this.before = function (data, callback) {};
    this.canClose = function (status) {
        this._close_status = status;
    };
    this.init = function (elemSelector) {
        _addModalEvents(elemSelector);
    };
}
