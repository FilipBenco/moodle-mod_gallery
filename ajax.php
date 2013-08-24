<?php
define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

if (!confirm_sesskey()) {
	throw new moodle_exception('invalidsesskey', 'error');
}

ob_start();
header('Expires: Sun, 28 Dec 1997 09:32:45 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Type: text/html; charset=utf-8');

$imageid = required_param('image', PARAM_INT);
$contextid = required_param('ctx', PARAM_INT);

$context = get_context_instance_by_id($contextid);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/mod/gallery/ajax.php',array('image'=>$imageid,'cts'=>$contextid)));

$img = $DB->get_record('gallery_images',array('id'=>$imageid));
echo format_text($img->description, $img->descriptionformat);

header('Content-Length: ' . ob_get_length() );
ob_end_flush();