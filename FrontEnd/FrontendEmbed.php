<!doctype html>
<?php
/*
 * Frontend file html template
 */

global $path,$settings;
$v = 10;
if (!in_array($settings["interface"]["themecolor"], ["blue","sun","standard","copper","black"])) {
    $settings["interface"]["themecolor"] = "standard";
}
?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telemetry site app - <?php echo $route->controller.' '.$route->action.' '.$route->subaction; ?></title>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="apple-touch-startup-image" href="<?php echo $path; ?>Frontend/Telemetry_ios_load.png">
    <link rel="apple-touch-icon" href="<?php echo $path; ?>Frontend/Telemetry_logo_normal.png">

    <link href="<?php echo $path; ?>Libraries/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $path; ?>Frontend/css/css-base.css?v=<?php echo $v; ?>" rel="stylesheet">

    <script type="text/javascript" src="<?php echo $path; ?>Libraries/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="<?php echo $path; ?>Libraries/misc/gettext.js?v=<?php echo $v; ?>"></script>
    <script>
        var current_themecolor = "<?php echo $settings["interface"]["themecolor"]; ?>";
        var current_themesidebar = "dark";
    </script>
    <script src="<?php echo $path; ?>Libraries/TelemetryApp-helper.js?v=<?php echo $v; ?>"></script>
</head>
<body>
<div>
    <?php print $content; ?>
</div>
</body>
</html>
