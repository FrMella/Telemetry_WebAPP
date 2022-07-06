/*
   ****
 */
function setExpandButtonState(state){
    if(typeof state == 'undefined') return
    $container = $('#table')
    $btn_expand = $('#expand-collapse-all')
    $icon_expand = $btn_expand.find('.icon')
    $icon_expand
      .toggleClass('icon-resize-small', state)
      .toggleClass('icon-resize-full', !state)
    if(!$btn_expand.data('original-title')) $btn_expand.data('original-title', $btn_expand.attr('title'))
    $btn_expand.attr('title', !state ? $btn_expand.data('alt-title') : $btn_expand.data('original-title'))
}
function expandAllNodes(state){
    $container = $('#table')
    if (typeof state == 'undefined') state = $container.find('.collapsed').length == 0
    $container.find('.collapse').collapse(state ? 'hide':'show')
}

$(function() {
    $container = $('#table')
    $btn_expand = $('#expand-collapse-all')
    $icon_expand = $btn_expand.find('.icon')

    $btn_expand.on('click', function(){
        expandAllNodes()
    })
    save_node_state_timeout = null

    $(document).on("shown hidden", '#table .tbody.collapse', function(e){
    	$header = $('[data-target="#'+e.target.id+'"]')
        if (e.type == 'hidden'){
            $header.addClass('collapsed')
            $header.find('.icon-indicator').removeClass('icon-chevron-down').addClass('icon-chevron-right')
        } else {
            $header.removeClass('collapsed')
            $header.find('.icon-indicator').removeClass('icon-chevron-right').addClass('icon-chevron-down')
        }
        state = $container.find('.collapsed').length == 0
        setExpandButtonState(state)

        clearTimeout(save_node_state_timeout)
        save_node_state_timeout = setTimeout(function(){
        },100)
    })

    $(document).on('hide show', '#table .tbody.collapse', function(event){
        nodes_display[event.target.dataset.node] = event.type == 'show'
    })
    $('#select-all').on('click',function(){
        $this = $(this)
        state = $this.data('state') != false
        $this.data('title-original', $this.data('title-original') ? $this.data('title-original') : $this.attr('title'))
        $this.find('.icon').toggleClass('icon-ban-circle', state)
        $this.find('.icon').toggleClass('icon-check', !state)
        $("#table .select input[type='checkbox']").prop('checked', state).trigger('select').trigger('change')
        title = state ? $this.data('alt-title') : $this.data('title-original')
        $this.attr('title', title)
        $this.data('state',!state)
        if (state===true) expandAllNodes(false)
    })
    
    $('#table').change('.feed-select', function(e) {
        let $parent = $(e.target).parents('.collapse')
        let id = $parent.attr('id')
        let $collapse = $('.accordion-toggle[data-target="#'+id+'"]')
        checked_checkboxes = $parent.find(':checkbox:checked').length;
        if(checked_checkboxes>0){
            $collapse.attr('data-toggle',false)
        } else {
            $collapse.attr('data-toggle','collapse')
        }
    })

        function selectAllInNode(e){
            e.preventDefault()
            e.stopPropagation()
            $container = $(e.target).parents('.accordion').first()
            $container.find('.collapse').collapse('show')
            $inputs = $container.find(':checkbox')
            $selected = $container.find(':checkbox:checked')
            $inputs.prop('checked', $inputs.length != $selected.length).trigger('select')
        }
        // $(document).on('click','.input-list .has-indicator', selectAllInNode)
        
        // $(document).on('mouseup','.feed-list .has-indicator', selectAllInNode)
});


function list_format_updated(time) {
    var fv = list_format_updated_obj(time);
    return "<span class='last-update' style='color:" + fv.color + ";'>" + fv.value + "</span>";
}

function list_format_updated_obj(time) {
    time = time * 1000;
    var servertime = new Date().getTime(); // - table.timeServerLocalOffset;
    var update = new Date(time).getTime();
    
    var delta = servertime - update;
    var secs = Math.abs(delta) / 1000;
    var mins = secs / 60;
    var hour = secs / 3600;
    var day = hour / 24;
    
    var updated = secs.toFixed(0) + "s";
    if ((update == 0) || (!$.isNumeric(secs))) updated = "n/a";
    else if (secs.toFixed(0) == 0) updated = "now";
    else if (day > 7 && delta > 0) updated = "inactive";
    else if (day > 2) updated = day.toFixed(1) + " days";
    else if (hour > 2) updated = hour.toFixed(0) + " hrs";
    else if (secs > 180) updated = mins.toFixed(0) + " mins";
    
    secs = Math.abs(secs);
    var color = "rgb(255,0,0)";
    if (delta < 0) color = "rgb(60,135,170)"
    else if (secs < 25) color = "rgb(50,200,50)"
    else if (secs < 60) color = "rgb(240,180,20)"; 
    else if (secs < (3600*2)) color = "rgb(255,125,20)"
    
    return {color:color,value:updated};
}

// Format value dynamically
function list_format_value(value) {
    if (value == null) return "NULL";
    value = parseFloat(value);
    if (value >= 1000) value = parseFloat(value.toFixed(0));
    else if (value >= 100) value = parseFloat(value.toFixed(1));
    else if (value >= 10) value = parseFloat(value.toFixed(2));
    else if (value <= -1000) value = parseFloat(value.toFixed(0));
    else if (value <= -100) value = parseFloat(value.toFixed(1));
    else if (value < 10) value = parseFloat(value.toFixed(2));
    return value;
}


function autowidth($container) {
    let widths = {},
        default_padding = 20;
    $container.find("[data-col]").each(function () {
        let $this = $(this),
            padding = $this.data("col-padding") || default_padding,
            width = $this.width() + padding,
            col = $this.data("col");

        widths[col] = widths[col] || 0;

        if (width > widths[col] || width == "auto") {
            widths[col] = width;
        }
    });

    $container.find(".thead [data-col]").each(function () {
        let $this = $(this),
            col = $this.data("col");
        // @see: onResize()
        if ($this.data("col-width")) {
            widths[col] = $this.data("col-width");
        }
    });

    for (col in widths) {
        if (widths[col] != "auto") {
            $('[data-col="' + col + '"]').width(widths[col]);
        }
    }
    onResize();

    function onResize() {
        let $container = $("#table"),
            $row = $container.find(".thead:first"),
            rowWidth = $row.width(),
            columnsWidth = 0,
            hidden = {},
            cols = {},
            min_auto_width = 200

        $row.find("[data-col]").each(function () {
            let $col = $(this);
            cols[$col.data("col")] = $col;
        });
        keys = Object.keys(cols).sort();

        for (k in keys) {
            // let $col = order[keys[s]]
            let $col = cols[keys[k]];
            if ($col.data("col-width") != "auto") {
                columnsWidth += $col.outerWidth(); // includes padding
            }
            if (keys[k] !== keys[0]) hidden[keys[k]] = columnsWidth > rowWidth;
        }
        var remainder = rowWidth - columnsWidth,
            numberOfNodes = $container.find(".node").length,
            numberOfAutocolumns = parseInt(
                $('.thead [data-col-width="auto"]').length / numberOfNodes
            ),
            r = parseInt(remainder / numberOfAutocolumns);

        $container.find('.thead [data-col-width="auto"]').each(function () {
            let col = $(this).data("col")
            $container.find('[data-col="' + col + '"]').width(r - 10);
            hidden[col] = remainder < min_auto_width;
        });

        $("[data-col]").show();
        for (key in hidden) {
            if (hidden[key]) $('[data-col="' + key + '"]').hide();
        }
    }

    function watchResize(callback, timeout) {
        if (typeof callback == "undefined" || !(callback instanceof Function)) return;
        timeout = timeout || 50; // en milisegundos

        var resizeTimer;
        $(window).on("resize", function (e) {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                callback();
            });
        });
    }

    function list_format_size(bytes) {
        if (!$.isNumeric(bytes)) {
            return "n/a";
        } else if (bytes < 1024) {
            return bytes + "B";
        } else if (bytes < 1024 * 100) {
            return (bytes / 1024).toFixed(1) + "KB";
        } else if (bytes < 1024 * 1024) {
            return Math.round(bytes / 1024) + "KB";
        } else if (bytes <= 1024 * 1024 * 1024) {
            return Math.round(bytes / (1024 * 1024)) + "MB";
        } else {
            return (bytes / (1024 * 1024 * 1024)).toFixed(1) + "GB";
        }
    }

    Number.prototype.pad = function (size) {
        var s = String(this);
        while (s.length < (size || 2)) {
            s = "0" + s;
        }
        return s;
    };
}
