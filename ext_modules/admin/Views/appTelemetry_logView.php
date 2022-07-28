<link rel="stylesheet" href="<?php echo $path?>Ext_Modules/admin/static/admin_styles.css?v=1">
<div class="admin-container">

    <?php

    if ($log_enabled) { ?>
    <section class="d-md-flex justify-content-between align-items-center pb-md-2 text-right px-1">
        <div class="text-left">
            <h3 class="mt-1 mb-0"><?php echo _('Emoncms Log'); ?></h3>
            <p><?php
            if(is_writable($app_logfile)) {
                echo sprintf("%s <code>%s</code>",_('View last entries on the logfile:'),$app_logfile);
            } else {
                echo '<div class="alert alert-warn">';
                echo "The log file has no write permissions or does not exists. To fix, log-on on shell and do:<br><pre>touch $app_logfile<br>chmod 666 $app_logfile</pre>";
                echo '<small></div>';
            } ?></p>
        </div>
        <div>
            <?php if(is_writable($app_logfile)) { ?>
                <button id="getlog" type="button" class="btn btn-info mb-1" data-toggle="button" aria-pressed="false" autocomplete="off">
                    <?php echo _('Auto refresh'); ?>
                </button>
                <a href="<?php echo $path; ?>admin/downloadlog" class="btn btn-info mb-1"><?php echo _('Download Log'); ?></a>
                <button class="btn btn-info mb-1" id="copylogfile" type="button"><?php echo _('Copy Log to clipboard'); ?></button>
            <?php } ?>
        </div>
    </section>
    
    <!--
    <section>
        <pre id="logreply-bound" class="log" style="min-height:320px; height:calc(100vh - 280px);"><div id="logreply"></div></pre>
        <div class="text-right"> 
            <div class="btn-group">
                <button class="btn btn-inverse mb-1">
                    <?php echo sprintf('Log Level: %s', $log_level_label) ?>
                </button>
            </div>
        </div>
    </section>
    bellow is original code that overlaps footer, comment here is my proposal using correct bootstrap syntax
    -->
    
    <section>
        <pre id="logreply-bound" class="log" style="min-height:320px; height:calc(100vh - 220px); display:none;"><div id="logreply"></div></pre>
        <span id="log-level" class="btn-small dropdown-toggle btn-inverse text-uppercase" title="Can be changed in settings file" style="cursor:pointer">
            <?php echo sprintf('Log Level: %s', $log_level_label) ?>
        </span>
    </section>
    
    <?php 
        } else {
            echo _('Logging is disabled in settings.');
        }
    ?>
    
    
</div>
<div id="snackbar" class=""></div>
<script>

$("#logreply-bound").slideDown();

var logFileDetails;
$("#copylogfile").on('click', function(event) {
    logFileDetails = $("#logreply").text();
    if ( event.ctrlKey ) {
        copyTextToClipboard('ULIMAS ENTRADAS EN EL ARCHIVO\n'+logFileDetails,
        event.target.dataset.success);
    } else {
        copyTextToClipboard('<details><summary>LAST ENTRIES ON THE LOG FILE</summary><br />\n'+ logFileDetails.replace(/\n/g,'<br />\n').replace(/API key '[\s\S]*?'/g,'API key \'xxxxxxxxx\'') + '</details><br />\n',
        event.target.dataset.success);
    }
} );

var logrunning = false;

var appTelemetry_logInterval;

function refresherStart(func, interval){
    if (interval > 0) return setInterval(func, interval);
}

// push value to emoncms logfile viewer
function refresh_log(result){
    var isjson = true;
    try {
        data = JSON.parse(result);
        if (data.reauth == true) { window.location.reload(true); }
        if (data.success != undefined)  { 
            clearInterval(appTelemetry_logInterval);
            output_logfile("<text style='color:red;'>"+ data.message+"</text>", $("#logreply"));
        }
    } catch (e) {
        isjson = false;
    }
    if (isjson == false )     {
        output_logfile(result, $("#logreply"));
    }

}
function output_logfile(result, $container){
    $container.html(result);
    scrollable = $container.parent('pre')[0];
    if(scrollable) scrollable.scrollTop = scrollable.scrollHeight;
}

getLog();
function getLog() {
  $.ajax({ url: path+"admin/getlog", async: true, dataType: "text", success: refresh_log });
}

$("#getlog").click(function() {
    $this = $(this)
    if ($this.is('.active')) {
        clearInterval(appTelemetry_logInterval);
    } else {
        appTelemetry_logInterval = refresherStart(getLog, 1000); 
    }
});
function copyTextToClipboard(text, message) {
  var textArea = document.createElement("textarea");
  textArea.style.position = 'fixed';
  textArea.style.top = 0;
  textArea.style.left = 0;
  textArea.style.width = '2em';
  textArea.style.height = '2em';
  textArea.style.padding = 0;
  textArea.style.border = 'none';
  textArea.style.outline = 'none';
  textArea.style.boxShadow = 'none';
  textArea.style.background = 'transparent';
  textArea.value = text;
  document.body.appendChild(textArea);
  textArea.select();
  try {
    var successful = document.execCommand('copy');
    var msg = successful ? 'successful' : 'unsuccessful';
    // console.log('Copiar commando en text =  ' + msg);
    snackbar(message || 'Copied to clipboard');
  } 
  catch(err) {
    window.prompt("<?php echo _('copiar: Ctrl+C'); ?>", text);
  }
  document.body.removeChild(textArea);
}
function snackbar(text) {
    var snackbar = document.getElementById("snackbar");
    snackbar.innerHTML = text;
    snackbar.className = "show";
    setTimeout(function () {
        snackbar.className = snackbar.className.replace("show", "");
    }, 3000);
}
</script>
