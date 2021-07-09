/* ========================================================================
 * ajax_select: AJAX获取数据联动SELECT
 * ======================================================================== 
 */
$.fn.ajax_select = function (options) {
    var _This = this,
        _ajax_sel = ".ajax-select", 
        _settings = $.extend({
            field: $('#OD_ajax_select'), 
            disabled: false, 
            top_option_id: 0, 
            top_option: "请选择"
        }, options || {});
        _self = $(this),
        _pid = _self.attr('data-pid'),
        _top_option = _self.attr('data-top-option') || _settings.top_option,
        _ajax_uri = _self.attr('data-uri'), 
        _selected = _self.attr('data-selected'), 
        _selected_arr = [];
    this._self = _self;
    this._ajax_sel = _ajax_sel;
    this._ajax_uri = _ajax_uri;
    if (_selected != undefined && _selected != '0') {
        if (_selected.indexOf('|')) {
            _selected_arr = _selected.split('|');
        } else {
            _selected_arr = [];
            _selected_arr[0] = _selected;
        }
    }
    this._self.nextAll(_This._ajax_sel).remove();
    $('<option value="' + _settings.top_option_id + '">--' + _top_option + '--</option>').appendTo(this._self);
    $.ajax({
        url:_This._ajax_uri,
        data:{
            id: _pid
        },
        success:function(result){
            if (result.status == '1') {
                for (var i = 0; i < result.data.length; i++) {
                    var _remark = result.data[i].remark;
                    if (_remark && _remark!=""){
                        _remark = '【'+_remark+'】';
                    } else {
                        _remark = "";
                    }
                    $('<option value="' + result.data[i].id + '">--' + _remark + result.data[i].name + '--</option>').appendTo(_This._self);
                }
            }
            if (_selected_arr.length > 0) {
                //IE6 BUG
                setTimeout(function () {
                    _This._self.find('option[value="' + _selected_arr[0] + '"]').attr("selected", true);
                    _This._self.trigger('change');
                }, 1);
            }
        }
    })

    
    var j = 1, 
        _disabled = "";

    if (_settings.disabled) {
        _disabled = ' _disabled="true"';
    }

    if (_pid) {
        $(document).off("change",".ajax-select");
        $(document).on("change",".ajax-select",function(){
            var _this = $(this);
            _pid = _this.val();
            _this.nextAll(_This._ajax_sel).remove();
            if (_pid > 0) {
                $.getJSON(_This._ajax_uri, {
                    id: _pid
                }, function (result) {
                    if (result.status == '1') {
                        var _childs = $('<select ' + _disabled + ' class="' + _This._ajax_sel.substr(1) + ' ajax-select custom-select form-control mr-2" data-pid="' + _pid + '"><option value="0">--' + _top_option + '--</option></select>');
                        for (var i = 0; i < result.data.length; i++) {
                            $('<option value="' + result.data[i].id + '">--' + result.data[i].name + '--</option>').appendTo(_childs);
                        }
                        _childs.insertAfter(_this);
                        if (_selected_arr[j] != undefined) {
                            _childs.find('option[value="' + _selected_arr[j] + '"]').attr("selected", true);
                            _childs.trigger('change');
                        }
                        j++;
                    }
                });
                _settings.field.val(_pid);
            } else {
                _settings.field.val(_this.attr('data-pid'));
            }
        });
    }
};