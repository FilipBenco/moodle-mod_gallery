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

$PAGE->set_context(get_context_instance_by_id($contextid));
$PAGE->set_url(new moodle_url('/mod/gallery/ajax.php',array('image'=>$imageid,'cts'=>$contextid)));

$img = $DB->get_record('gallery_images',array('id'=>$imageid));

require_once($CFG->dirroot.'/mod/gallery/image.class.php');

$return = new stdClass;

$return->description = format_text($img->description, $img->descriptionformat);
$return->name = $img->name;

if($img->sourcetype == GALLERY_IMAGE_SOURCE_OWN) {
    $user = $DB->get_record('user',array('id'=>$img->source));
    $urlparams = array('id'=>$user->id);
    $return->source = '<strong>'.get_string('author','gallery') . ':</strong> ';
    $return->source .= $OUTPUT->action_link(new moodle_url('/user/profile.php',$urlparams), fullname($user));
}
if($img->sourcetype == GALLERY_IMAGE_SOURCE_TEXT) {
    $return->source = '<strong>'.get_string('source','gallery') . ':</strong> '.$img->source;
}

echo json_encode($urlparams);

header('Content-Length: ' . ob_get_length() );
ob_end_flush();