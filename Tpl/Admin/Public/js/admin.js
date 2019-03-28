// To make Pace works on Ajax calls
$(document).ajaxStart(function() {
    Pace.restart();
});

// form表单赋值
function dataToForm(obj, data) {
    $('#' + obj + ' [name]').each(function(i, element) {
        var elementName = element.name,
        dataValue = data[elementName],
        $this = $(this);
        switch (element.type) {
            case 'text':
            case 'number':
            case 'hidden':
            case 'textarea':
            $this.val(dataValue);
            break;
            case 'radio':
            if ($this.val() == dataValue) $this.prop('checked', true);
            break;
            case 'checkbox':
                var checkboxName = elementName.substring(0, elementName.length - 2); // 去掉name后面的[]
                if (data[checkboxName]) {
                    if ($.inArray($this.val(), data[checkboxName].split(',')) != '-1') $this.prop('checked', true);
                }
                break;
                default:
                $this.find('option[value="' + dataValue + '"]').prop('selected', true);
                break;
            }
        });
}

// toastr["success"]("content", "title");
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": false,
    "progressBar": false,
    "rtl": false,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": 300,
    "hideDuration": 1000,
    "timeOut": 2000,
    "extendedTimeOut": 1000,
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
}

// dataTables
var DT = {
    DTLang: {
        "sProcessing": "处理中...",
        "sLengthMenu": "显示 _MENU_ 项结果",
        "sZeroRecords": "没有匹配结果",
        "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
        "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
        "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
        "sInfoPostFix": "",
        "sSearch": "搜索:",
        "sUrl": "",
        "sEmptyTable": "没有数据",
        "sLoadingRecords": "载入中...",
        "sInfoThousands": ",",
        "oPaginate": {
            "sFirst": "首页",
            "sPrevious": "上页",
            "sNext": "下页",
            "sLast": "末页"
        },
        "oAria": {
            "sSortAscending": ": 以升序排列此列",
            "sSortDescending": ": 以降序排列此列"
        }
    },
    COLUMN: {
        STATUS: {
            "data": "status",
            "class": "text-center",
            "render":function (data,type,full,meta) {
                return data == STATUS_Y ? '<small class="label label-success">启用</small>' : '<small class="label label-danger">禁用</small>';
            }
        }
    },
    RENDER: {
        ELLIPSIS: function (data,type,full,meta) {
            return '<span class="dt-ellipsis" title="'+data+'">'+data+'</span>';
        },
        IMG: function (data,type,full,meta) {
            if (data) {
                return '<a href="'+data+'" class="img-box" data-lightbox="'+full.id+'"><img class="img-thumbnail" src="'+data+'"></a>';
            } else {
                return '';
            }
        },
        ORDER: function (data,type,full,meta) {
            switch (parseInt(data)) {
                case ORDER_S_W:
                    return '<span class="text-warning">待支付</span>'
                    break;
                case ORDER_S_P:
                    return '<span class="text-success">已支付</span>'
                    break;
                case ORDER_S_C:
                    return '<span class="text-danger">已取消</span>'
                    break;
                default:
                    break;
            }
        },
        HEAD: function (data,type,full,meta) {
            return '<a href="'+data+'" data-lightbox="'+full.id+'"><img class="img-thumbnail img-circle" src="'+data+'"></a>';
        },
        FA: function (data,type,full,meta) {
            return '<i class="fa fa-'+data+'"></i>';
        },
        TIME: function (data,type,full,meta) {
            return data.substr(0, 10);
        },
        INPUT: function(data,type,full,meta) {
            var data = data || '';
            return '<input class="dt-input" type="text" value="'+data+'">'
        }
    }
};

var DTSearchGroup =
'<div class="input-group">' +
'<input type="text" class="form-control pull-left fuzzy-search" placeholder="模糊查询">' +
'<span class="input-group-btn"><button type="button" class="btn waves-effect waves-light btn-info" id="fuzzySearch"><i class="fa fa-search"></i></button></span>'+
'<span class="input-group-btn"><button type="button" class="btn btn-default" title="高级查询" data-toggle="collapse" href="#searchCollapse"><i class="fa fa-angle-double-down"></i></button></span>' +
'</div>';

// default setting
// var height = document.body.clientHeight - $('.box-body').offset().top - 270 + 'px';
$.extend($.fn.dataTable.defaults, {
    dom:
    "<'row'<'col-sm-6'l><'search-item col-sm-6'>>" +
    "<'row'<'col-sm-12'<'#searchCollapse.collapse'>>>" +
    "<'row'<'col-sm-12'tr>>" +
    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    language: DT.DTLang, // 提示信息
    pageLength: 20, // 初始化页长度
    lengthMenu: [ [10, 20, 50, 100, -1], [10, 20, 50, 100, "所有"] ], // 页面显示条数的下拉框选项
    // scrollY: '500px', // 垂直滚动
    autoWidth: false, // 自动调整列宽
    processing: true, // 加载提示
    serverSide: true, // 服务器端分页
    searching: false, // 原生搜索
    orderMulti: false, // 多列排序
    order: [], // 默认排序查询
    pagingType: "simple_numbers", // 分页样式：simple,simple_numbers,full,full_numbers
    columnDefs: [
    {
        "targets": -1,
        "searchable": false
    }
    ]
});

// 单选
$('.table-single tbody').on( 'click', 'tr', function () {
    // if ( $(this).hasClass('selected') ) {
    //     $(this).removeClass('selected');
    // }
    // else {
    //     oTable.$('tr.selected').removeClass('selected');
    //     $(this).addClass('selected');
    // }

    oTable.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
});
// 获取单行选中数据
function getSelectedData(dtObj, callback){
    var data = dtObj.row('.selected').data();
    if (data)
        return callback(data);
    else
        toastr["error"]("请先选择需要操作对象", "");
}

// 多选
$('.table-multiple tbody').on( 'click', 'tr', function () {
    $(this).toggleClass('selected');
});
// 获取多行选中数据
function getMultipleSelectedData(dtObj, callback){
    var data = dtObj.rows('.selected').data();
    if (data.length)
        return callback(data);
    else
        toastr["error"]("请先选择需要操作对象", "");
}


// 获取当前行数据
function getCurRowData(obj, that) {
    return obj.row(that.closest('tr')).data();
}

// 刷新DT
function DTReload(obj) {
    obj.ajax.reload();
}
function DTdraw(obj) {
    obj.ajax.reload(null, false);
}

// 模糊搜索
$(document).on('click', '#fuzzySearch', function() {
    DTReload(oTable);
});
// 高级搜索
$(document).on('click', '#advancedSearch', function() {
    DTReload(oTable);
});

// error
$.fn.dataTable.ext.errMode = function( settings, tn, msg ) {
    console.log(msg);
}

// 弹窗
function layui_form(msg,callback=function(){},area='500px',contentId='actionModal'){
    layer.open({
        type: 1,
        shadeClose: true,
        title: msg,
        content: $('#'+contentId).html(),
        area: area,
        maxHeight: 800,
        success: function(layero, index){
            return callback();
        }
    });
}

function layui_big_form(msg,callback=function(){},width='600px',contentId='actionModal'){
    layer.open({
        type: 1,
        shadeClose: true,
        title: msg,
        content: $('#'+contentId).html(),
        offset: '10vh',
        area: [width, '80vh'],
        success: function(layero, index){
            return callback();
        }
    });
}

// 详情
function layui_detail(msg,url,width='600px',height='80vh'){
    layer.open({
        type: 2,
        shade: 0.8,
        maxmin: true,
        anim: 5,
        shadeClose: true,
        title: msg,
        content: url,
        offset: '10vh',
        area: [width, height],
        success: function(layero, index){
            // layer.iframeAuto(index)
        }
    });
}

// jquery.form
var formOptions = {
    type: 'post',
    dataType: 'json',
    timeout: 3000,
}
function submit_form(url,formId='actionForm'){
    formOptions.url = url;
    formOptions.success = function(responseText, statusText) {
        if (responseText.status == 1) {
            toastr["success"]("操作成功", "");
            DTdraw(oTable);
            layer.closeAll();
        }
    }
    $('#'+formId).ajaxSubmit(formOptions);
    return false;
}



/***************************** sweetalert2 ***********************************/

// 带输入框 input : text, email, password, number, tel, range, textarea, select, radio, checkbox, file and url
/*
var inputOptions = new Promise((resolve) => {
  setTimeout(() => {
    resolve({
      '#ff0000': 'Red',
      '#00ff00': 'Green',
      '#0000ff': 'Blue'
    })
  }, 2000)
})
*/
function swal_input(title,name,callback=function(){},input='text',inputOptions=''){
    swal({
        title: title,
        input: input,
        showCancelButton: true,
        inputOptions: inputOptions,
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        inputPlaceholder: '请输入'+name,
        showLoaderOnConfirm: true,
        preConfirm: (value) => {
            return new Promise((resolve) => {
                if (!value) {
                    swal.showValidationError(
                        name+'必填！'
                        )
                }
                resolve()
            })
        },
        allowOutsideClick: () => !swal.isLoading(),
    }).then((result) => {
        if (result.value) {
            return callback(result.value);
        }
    });
}

// 询问操作
function swal_action(title,text,callback=function(){},type='warning'){
    swal({
        title: title,
        text: text,
        type: type,
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !swal.isLoading(),
    }).then((result) => {
        if (result.value) {
            return callback(result.value);
        }
    });
}

// 提示 type:success,error,warning,info,question
function swal_notice(title,type='success'){
    swal({
      position: 'top-end',
      type: type,
      title: title,
      showConfirmButton: false,
      allowOutsideClick: false,
      timer: 1500
  })
}

// FileInput初始化
var FileInput = function () {
    var oFile = new Object();
    oFile.Init = function(ctrlName, uploadUrl){
        var control = $('#' + ctrlName);
        // 初始化上传控件的样式
        control.fileinput({
            language: 'zh',
            uploadUrl: uploadUrl,                                 // 上传的地址
            showUpload: false,                                    // 是否显示上传按钮
            showCaption: false,                                   // 是否显示标题
            showRemove: false,                                    // 是否显示删除按钮
            uploadAsync: false,                                   // 是否异步
            maxFileSize : 10240,                                  // 最大尺寸
            maxFileCount: 10,                                     // 最大上传数量
            overwriteInitial: true,                               // 重载初始预览
            allowedFileExtensions: ['jpg', 'gif', 'png', 'jpeg'], // 接收的文件后缀
            browseClass: "btn btn-default btn-block",             // 按钮样式
            dropZoneEnabled: false,                               // 是否显示拖拽区域
            enctype: 'multipart/form-data',
            browseLabel: '上传图片',
            previewClass:'previewPanel',
            layoutTemplates:{
                actionUpload: ''
            }
        })
    }

    oFile.Preview = function(ctrlName, uploadUrl, initialPreview, initialPreviewConfig){
        var control = $('#' + ctrlName);
        // 初始化上传控件的样式
        control.fileinput({
            language: 'zh',
            uploadUrl: uploadUrl,                                 // 上传的地址
            showUpload: false,                                    // 是否显示上传按钮
            showCaption: false,                                   // 是否显示标题
            showRemove: false,                                    // 是否显示删除按钮
            uploadAsync: false,                                   // 是否异步
            maxFileSize : 10240,                                  // 最大尺寸
            maxFileCount: 10,                                     // 最大上传数量
            overwriteInitial: true,                               // 重载初始预览
            allowedFileExtensions: ['jpg', 'gif', 'png', 'jpeg'], // 接收的文件后缀
            browseClass: "btn btn-default btn-block",             // 按钮样式
            dropZoneEnabled: false,                               // 是否显示拖拽区域
            enctype: 'multipart/form-data',
            browseLabel: '上传图片',
            previewClass:'previewPanel',
            initialPreviewAsData: true,
            initialPreview: initialPreview,
            initialPreviewConfig: initialPreviewConfig,
            layoutTemplates:{
                actionUpload: ''
            }
        })
    }
    return oFile;
};

// 修改状态
function set_status(title, url, data){
    swal.queue([{
        title: title,
        type: 'question',
        confirmButtonText: '确定',
        showLoaderOnConfirm: true,
        preConfirm: function () {
            return new Promise(function (resolve) {
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    dataType:"json",
                    success: function(result) {
                        if (result.status == 1) {
                            toastr["success"]("操作成功", "");
                            swal.close();
                            DTdraw(oTable);
                        }
                    }
                });
            })
        }
    }]);
}

// multiselect
$('body').on('click', '#selectAll', function(event) {
    $('#multiSelect').multiSelect('select_all');
});
$('body').on('click', '#unSlectAll', function(event) {
    $('#multiSelect').multiSelect('deselect_all');
});

// 水印
function watermark(settings) {
    //默认设置
    var defaultSettings={
        watermark_txt:"text",
        watermark_x:250,//水印起始位置x轴坐标
        watermark_y:60,//水印起始位置Y轴坐标
        watermark_rows:20,//水印行数
        watermark_cols:20,//水印列数
        watermark_x_space:100,//水印x轴间隔
        watermark_y_space:100,//水印y轴间隔
        watermark_color:'#000',//水印字体颜色
        watermark_alpha:0.2,//水印透明度
        watermark_fontsize:'14px',//水印字体大小
        watermark_font:'微软雅黑',//水印字体
        watermark_width:150,//水印宽度
        watermark_height:80,//水印长度
        watermark_angle:15//水印倾斜度数
    };
    //采用配置项替换默认值，作用类似jquery.extend
    if(arguments.length===1&&typeof arguments[0] ==="object" ) {
        var src=arguments[0]||{};
        for(key in src) {
            if(src[key]&&defaultSettings[key]&&src[key]===defaultSettings[key])
                continue;
            else if(src[key])
                defaultSettings[key]=src[key];
        }
    }

    var oTemp = document.createDocumentFragment();

    //获取页面最大宽度
    var page_width = Math.max(document.body.scrollWidth,document.body.clientWidth);
    //获取页面最大长度
    var page_height = Math.max(document.body.scrollHeight,document.body.clientHeight);

    //如果将水印列数设置为0，或水印列数设置过大，超过页面最大宽度，则重新计算水印列数和水印x轴间隔
    if (defaultSettings.watermark_cols == 0 ||
        (parseInt(defaultSettings.watermark_x
            　　　　+ defaultSettings.watermark_width *defaultSettings.watermark_cols
            　　　　+ defaultSettings.watermark_x_space * (defaultSettings.watermark_cols - 1))
        　　　　> page_width)) {
        defaultSettings.watermark_cols =
    　　　　　　parseInt((page_width
        　　　　　　　　　　-defaultSettings.watermark_x
        　　　　　　　　　　+defaultSettings.watermark_x_space)
    　　　　　　　　　　/ (defaultSettings.watermark_width
        　　　　　　　　　　+ defaultSettings.watermark_x_space));
    defaultSettings.watermark_x_space =
    　　　　　　parseInt((page_width
        　　　　　　　　　　- defaultSettings.watermark_x
        　　　　　　　　　　- defaultSettings.watermark_width
        　　　　　　　　　　* defaultSettings.watermark_cols)
    　　　　　　　　　　/ (defaultSettings.watermark_cols - 1));
}
    //如果将水印行数设置为0，或水印行数设置过大，超过页面最大长度，则重新计算水印行数和水印y轴间隔
    if (defaultSettings.watermark_rows == 0 ||
        (parseInt(defaultSettings.watermark_y
            　　　　+ defaultSettings.watermark_height * defaultSettings.watermark_rows
            　　　　+ defaultSettings.watermark_y_space * (defaultSettings.watermark_rows - 1))
        　　　　> page_height)) {
        defaultSettings.watermark_rows =
    　　　　　　parseInt((defaultSettings.watermark_y_space
        　　　　　　　　　　　+ page_height - defaultSettings.watermark_y)
    　　　　　　　　　　　/ (defaultSettings.watermark_height + defaultSettings.watermark_y_space));
    defaultSettings.watermark_y_space =
    　　　　　　parseInt((page_height
        　　　　　　　　　　- defaultSettings.watermark_y
        　　　　　　　　　　- defaultSettings.watermark_height
        　　　　　　　　　　* defaultSettings.watermark_rows)
    　　　　　　　　　/ (defaultSettings.watermark_rows - 1));
}
var x;
var y;
for (var i = 0; i < defaultSettings.watermark_rows; i++) {
    y = defaultSettings.watermark_y + (defaultSettings.watermark_y_space + defaultSettings.watermark_height) * i;
    for (var j = 0; j < defaultSettings.watermark_cols; j++) {
        x = defaultSettings.watermark_x + (defaultSettings.watermark_width + defaultSettings.watermark_x_space) * j;

        var mask_div = document.createElement('div');
        mask_div.id = 'mask_div' + i + j;
        mask_div.appendChild(document.createTextNode(defaultSettings.watermark_txt));
            //设置水印div倾斜显示
            mask_div.style.webkitTransform = "rotate(-" + defaultSettings.watermark_angle + "deg)";
            mask_div.style.MozTransform = "rotate(-" + defaultSettings.watermark_angle + "deg)";
            mask_div.style.msTransform = "rotate(-" + defaultSettings.watermark_angle + "deg)";
            mask_div.style.OTransform = "rotate(-" + defaultSettings.watermark_angle + "deg)";
            mask_div.style.transform = "rotate(-" + defaultSettings.watermark_angle + "deg)";
            mask_div.style.visibility = "";
            mask_div.style.position = "fixed";
            mask_div.style.left = x + 'px';
            mask_div.style.top = y + 'px';
            mask_div.style.overflow = "hidden";
            mask_div.style.zIndex = "9999";
            mask_div.style.opacity = defaultSettings.watermark_alpha;
            mask_div.style.fontSize = defaultSettings.watermark_fontsize;
            mask_div.style.fontFamily = defaultSettings.watermark_font;
            mask_div.style.color = defaultSettings.watermark_color;
            mask_div.style.textAlign = "center";
            mask_div.style.width = defaultSettings.watermark_width + 'px';
            mask_div.style.height = defaultSettings.watermark_height + 'px';
            mask_div.style.display = "block";
            mask_div.style.pointerEvents = "none";
            oTemp.appendChild(mask_div);
        };
    };
    document.body.appendChild(oTemp);
}
