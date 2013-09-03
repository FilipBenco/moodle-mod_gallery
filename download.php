<?php

require_once('../../config.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('gallery', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

if(confirm_sesskey()) {
    require_once($CFG->dirroot . '/mod/gallery/gallery.class.php');
    require_once($CFG->dirroot.'/mod/gallery/locallib.php');
    $gallery = new gallery($cm->instance);
    
    $packedPhotos = gallery_get_packed_images($gallery, $context);
    if($packedPhotos)
        send_stored_file ($packedPhotos);
}