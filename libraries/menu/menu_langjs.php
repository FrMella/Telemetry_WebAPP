
<?php bindtextdomain("lib_messages",__DIR__ . "/../../Libraries/locale"); ?>

<script type="text/javascript">
var LANG_JS_MENU = new Array();
LANG_JS_MENU["Expand sidebar"] = '<?php echo addslashes(dgettext('lib_messages','Expand sidebar')); ?>';
LANG_JS_MENU["Minimise sidebar"] = '<?php echo addslashes(dgettext('lib_messages','Minimise sidebar')); ?>';
function _Tr_Menu(key)
{
// todo: implmentacion futura de cambio de idiomas del menu
<?php // ?>
    return LANG_JS_MENU[key] || key;
}
</script>