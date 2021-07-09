function CustomTable(elemSelector) {
    var This = this;
    this._selector = elemSelector;
    this._data_attrs = _getDataAttrs(elemSelector);
    this._options = _getOptions(this._data_attrs);

    function _addTreeEvents(elem,expandStatus){
        var _expandStatus = "expandAll";
        console.log("dddd")
        elem.treetable({ expandable: true }).treetable(_expandStatus);
        elem.on("click", ".TreeCheckbox", function() {
            var chk = $(".TreeCheckbox",elem),
                count = chk.length,
                num = chk.index($(this)),
                level_top = chk.eq(num).attr("level"),
                level_bottom = chk.eq(num).attr("level");
            for (var i = num; i >= 0; i--) {
                var le = chk.eq(i).attr("level");
                if (parseInt(le) < parseInt(level_top)) {
                    chk.eq(i).prop("checked", true);
                    var level_top = level_top - 1;
                }
            }
            for (var j = num + 1; j < count; j++) {
                var be = chk.eq(j).attr("level");
                if (chk.eq(num).prop("checked")) {
                    if (parseInt(be) > parseInt(level_bottom)) {
                        chk.eq(j).prop("checked", true);
                    } else if (parseInt(be) == parseInt(level_bottom)) break;
                } else {
                    if (parseInt(be) > parseInt(level_bottom)) chk.eq(j).prop("checked", false);
                    else if (parseInt(be) == parseInt(level_bottom)) break;
                }
            }
        });
    }

    /**
     * 获取table 对象data属性
     * @param {*} elem 
     */
    function _getDataAttrs(elem){
        var _data_attrs = elem.data() || {};

        var _unorder = _.map(_.split(_data_attrs.unorder,","),function(val){
            return _.toNumber(val);
        }),
            _sort = _data_attrs.csort,
            _order = _data_attrs.corder || "asc",
            _table_options = {};
        if (_.isNumber(_sort)){
            _table_options= {
                aoColumnDefs: [{ bSortable: false, aTargets: _unorder }],
                sort: true,
                order: [[_sort, _order]],
            }
            
        }
        return _table_options;
    }
    function _getOptions(newOpts){
        return _.assign({
            processing: true,
            lengthChange: false,
            aTargets: [],
            order: false,
            sort: false,
            scroll: false,
            info:false,
            pageLength: 20,
            paging:false,
            filter: false,
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
        },newOpts);
    }
    this.init = function(){
        var _table_options = _.assign({},This._options);
        this._selector.DataTable(_table_options);
        this._selector.editTable();
        if (this._selector.data().istree){
            _addTreeEvents(this._selector)
        }
        
    }
}
