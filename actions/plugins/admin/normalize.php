<?php
/**
 * Finds daily download total outliers and replaces them with the median
 */

$guid = get_input('guid');
if (!$guid) {
	register_error(elgg_echo('plugins:action:normalize:invalid_guid'));
	forward(REFERER);
}

$preview = get_input('preview', FALSE);

$downloads = plugins_get_downloads_histogram($guid, 0);

$mean = array_sum($downloads) / count($downloads);

$std_dev = 0;
foreach ($downloads as $count) {
	$std_dev += ($count - $mean) * ($count - $mean);
}
$std_dev /= count($downloads);
$std_dev = sqrt($std_dev);

// calculate cutoff - 95% assuming Gaussian distribution
$cutoff = $mean + 2 * $std_dev;

// calculate median
sort($downloads);
$median = $downloads[(int)round(.5 * count($downloads))];

// delete annotations beyond the daily cutoff - there must be a better way to do this
// This does not process the last day
$downloads = get_annotations($guid, 'object', 'plugin_project', 'download', '', 0, 9999999, 0, 'asc', 0);
$start_date = $downloads[0]->time_created;
$current_day = 0;
$count = 0;
$annotations_removed = 0;
$annotations_stack = array();
foreach ($downloads as $download) {
	$day = (int)floor(($download->time_created - $start_date) / (3600 * 24));
	if ($current_day == $day) {
		$count++;
		$annotation_stack[] = $download;
	} else {
		// if this day is out of the ordinary, reduce to median
		if ($count > $cutoff) {
			if (!$preview) {
				while (count($annotation_stack) > $median) {
					$annotation = array_pop($annotation_stack);
					delete_annotation($annotation->id);
				}
			}
			$annotations_removed += $count - $median;
		}
		// reset to handle the next day
		$count = 1;
		$annotation_stack = array($download);
		$current_day = $day;
	}
}


if (!$preview) {
	system_message(elgg_echo('plugins:action:normalize:notpreview'));
} else {
	system_message(elgg_echo('plugins:action:normalize:preview'));
}
forward(REFERER);
