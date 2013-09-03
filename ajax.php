<?php
define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

if (!confirm_sesskey()) {
	throw new moodle_exception('invalidsesskey', 'error');
}

$imageid = required_param('image', PARAM_INT);
$contextid = required_param('ctx', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);

$context = get_context_instance_by_id($contextid);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/mod/gallery/ajax.php',array('image'=>$imageid,'cts'=>$contextid)));

require_once($CFG->dirroot.'/mod/gallery/imagemanager.class.php');

$img = gallery_imagemanager::get_image($imageid);

if($action == 'display') {
    ob_start();
    header('Expires: Sun, 28 Dec 1997 09:32:45 GMT');
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Content-Type: text/html; charset=utf-8');
    require_once($CFG->dirroot.'/mod/gallery/image.class.php');
    require_once($CFG->dirroot.'/mod/gallery/locallib.php');

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

    $return->canedit = has_capability('mod/gallery:editimages', $context) || (has_capability('mod/gallery:editownimages', $context) && $img->user == $USER->id);
    $return->candelete = has_capability('mod/gallery:deleteimages', $context) || (has_capability('mod/gallery:deleteownimages', $context) && $img->user == $USER->id);
    
    $image = new gallery_image($img, null, $context, false, true);
    $a = '';
    foreach($image->attachments() as $att) {
        if($att->is_directory())
                continue;
        $ico = $OUTPUT->pix_icon(file_file_icon($att),$att->get_filename(),'moodle',array('class'=>'icon'));
        $a .= $OUTPUT->box_start();
        $attUrl = moodle_url::make_pluginfile_url($att->get_contextid(), $att->get_component(), 
                $att->get_filearea(), $att->get_itemid(), 
                $att->get_filepath(), $att->get_filename());
        $a .= $OUTPUT->action_link($attUrl, $ico.$att->get_filename());
        $a .= $OUTPUT->box_end();
    }
    
    $return->attachments = $a;
    
    echo json_encode($return);

    header('Content-Length: ' . ob_get_length() );
    ob_end_flush();
} 

if($action == 'move') {
    if(has_capability('mod/gallery:manage', $context) || has_capability('mod/gallery:editallimages', $context)) {
        $beforeId = required_param('beforeImage', PARAM_INT);
        $courseId = $DB->get_record('gallery',array('id'=>$img->gallery))->course;
        $ord = $img->ordering;
        $bOrd = 0;
        if($beforeId != 0) {
            $bImg = gallery_imagemanager::get_image($beforeId);
            $bOrd = $bImg->ordering;
        }
        
        if($bOrd < $ord) {
            $DB->execute(
                    'UPDATE {gallery_images} SET ordering = ordering+1 WHERE gallery = ? AND ordering < ? AND ordering > ?',
                    array($img->gallery, $ord, $bOrd)
                );
            $img->ordering = $bOrd+1;
            gallery_imagemanager::update_image($img);
            rebuild_course_cache($courseId,true);
            return;
        }
        if($bOrd > $ord) {
            $DB->execute(
                    'UPDATE {gallery_images} SET ordering = ordering-1 WHERE gallery = ? AND ordering <= ? AND ordering > ?',
                    array($img->gallery, $bOrd, $ord)
                );
            $img->ordering = $bOrd+1;
            gallery_imagemanager::update_image($img);
            rebuild_course_cache($courseId,true);
            return;
        }
    }
}