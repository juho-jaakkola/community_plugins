<?php

echo elgg_view('output/longtext', array('value' => elgg_echo('plugins:admin:trends:help')));


echo elgg_view_form('plugins/admin/plugin_select', array('action' => current_page_url(), 'method' => 'get'));

// default parameters for downloads plot
$num_days = 30;
$guid = (int)get_input('guid', 0);
if ($guid != 0) {
	$num_days = 0;
}

if ($guid) {
	$project = get_entity($guid);
	echo "<h4>" . elgg_echo('plugins:admin:trends:single', array($plugin->title)) . "</h4>";
} else {
	echo "<h4>" . elgg_echo('plugins:admin:trends:all') . "</h4>";
}

$histogram = plugins_get_downloads_histogram($guid, $num_days);

// create string for flot
$plot_string = '';
foreach ($histogram as $k=>$v) {
	$plot_string .= "[$k, $v],";
}
$plot_string = rtrim($plot_string, ',');

?>
<div id="plugins_download_plot"></div>

<?php elgg_load_js('jquery.flot'); ?>
<script>
$(function () {
    $.plot($("#plugins_download_plot"), [<?php echo $plot_string; ?>]);
});
</script>

<?php

if ($guid) {
	echo elgg_view_form('plugins/admin/normalize', array(), array('guid' => $guid));
}
