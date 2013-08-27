<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/gallery/gallery.class.php');
require_once($CFG->dirroot . '/mod/gallery/renderable.php');
require_once($CFG->dirroot . '/mod/gallery/locallib.php');

$id = required_param('id', PARAM_INT);
$edit = optional_param('edit', -1, PARAM_BOOL);
$action = optional_param('action', 'gallery', PARAM_ALPHA);
$iid = optional_param('image', 0, PARAM_INT);

$cm = get_coursemodule_from_id('gallery', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

$urlparams = array('id' => $id, 'action' => $action);
$url = new moodle_url('/mod/gallery/view.php', $urlparams);
require_login($course, true, $cm);
$PAGE->set_url($url);

$context = context_module::instance($cm->id);

gallery_process_editing($edit, $context);

require_capability('mod/gallery:view', $context);

$renderer = $PAGE->get_renderer('mod_gallery');
$gallery = new gallery($cm->instance);

//preprocess actions

$mform = null;
$images = null;
if($action == 'addimages') {
    require_once($CFG->dirroot.'/mod/gallery/image_upload_form.php');
    $mform = new mod_gallery_image_upload_form(null,array('id'=>$cm->id));
    if ($mform->is_cancelled()) 
        redirect($CFG->wwwroot.'/mod/gallery/view.php?id='.$cm->id);
    if (($formdata = $mform->get_data()) && confirm_sesskey()) {
        $images = gallery_process_drafts($context,$gallery);
        $action = 'addimagedesc';
    }
}
if($action == 'addimagedesc') {
    require_once($CFG->dirroot.'/mod/gallery/image_edit_form.php');
    $images = gallery_get_draft_images ($context, $gallery);
    $mform = new mod_gallery_image_edit_form(null,array('action'=>'addimagedesc','gallery'=>$gallery,'images'=>$images,'id'=>$cm->id,'contextid'=>$context->id));
    if ($mform->is_cancelled()) 
        redirect($CFG->wwwroot.'/mod/gallery/view.php?id='.$cm->id);
    if (($formdata = $mform->get_data()) && confirm_sesskey()) {
        $images = gallery_process_image_drats_save($formdata, $context, $gallery, $images);
        redirect($CFG->wwwroot.'/mod/gallery/view.php?id='.$cm->id);
    }
}
if($action == 'imagemoveright') {
    gallery_process_move_image('right', $iid);
    $action = 'gallery';
}
if($action == 'imagemoveleft') {
    gallery_process_move_image('left', $iid);
    $action = 'gallery';
}
if($action == 'imagedelete') {
    gallery_process_delete_image($iid, $context, $gallery);
    redirect($CFG->wwwroot.'/mod/gallery/view.php?id='.$cm->id);
}
if($action == 'rotateleftg') {
    gallery_process_rotate_image('left',$iid,$context);
    redirect($CFG->wwwroot.'/mod/gallery/view.php?id='.$cm->id);
}
if($action == 'rotaterightg') {
    gallery_process_rotate_image('right',$iid,$context);
    redirect($CFG->wwwroot.'/mod/gallery/view.php?id='.$cm->id);
}
if($action == 'rotatelefti') {
    gallery_process_rotate_image('left',$iid,$context);
    redirect($CFG->wwwroot.'/mod/gallery/view.php?id='.$cm->id.'&action='.$iid.'&image='.$iid);
}
if($action == 'rotaterighti') {
    gallery_process_rotate_image('right',$iid,$context);
    redirect($CFG->wwwroot.'/mod/gallery/view.php?id='.$cm->id.'&action='.$iid.'&image='.$iid);
}
if($action == 'editimages') {
    require_once($CFG->dirroot.'/mod/gallery/image_edit_form.php');
    $images = gallery_load_images ($gallery, $context);
    $mform = new mod_gallery_image_edit_form(null,array('action'=>'editimages','gallery'=>$gallery,'images'=>$images,'id'=>$cm->id,'contextid'=>$context->id));
    if ($mform->is_cancelled()) 
        redirect($CFG->wwwroot.'/mod/gallery/view.php?id='.$cm->id);
    if (($formdata = $mform->get_data()) && confirm_sesskey()) {
         gallery_process_images_save($formdata, $images);
        redirect($CFG->wwwroot.'/mod/gallery/view.php?id='.$cm->id);
    }
}
if($action == 'editimage') {
    require_once($CFG->dirroot.'/mod/gallery/image_edit_form.php');
    $images = array(gallery_load_image($context, $iid));
    $mform = new mod_gallery_image_edit_form(null,array('action'=>'editimage','gallery'=>$gallery,'images'=>$images,'id'=>$cm->id,'contextid'=>$context->id, 'image'=>$iid));
    if ($mform->is_cancelled()) 
        redirect($CFG->wwwroot.'/mod/gallery/view.php?id='.$cm->id.'&action=image&image='.$iid);
    if (($formdata = $mform->get_data()) && confirm_sesskey()) {
        gallery_process_images_save($formdata, $images);
        redirect($CFG->wwwroot.'/mod/gallery/view.php?id='.$cm->id.'&action=image&image='.$iid);
    }
}



switch($action) {
    case 'gallery':
        echo $renderer->render(new gallery_header($gallery->name(),$context));
        $images = gallery_load_images($gallery, $context);
        echo $renderer->render(new gallery_view_gallery($gallery, $images, $cm, $USER->editing));     
        break;
    case 'image':
        $PAGE->requires->css('/mod/gallery/css/lightbox.css');
        $PAGE->requires->js('/mod/gallery/js/jquery-1.10.2.min.js');
        $PAGE->requires->js('/mod/gallery/js/lightbox-2.6.min.js');        
        $PAGE->requires->js('/mod/gallery/module.js',true);
        $module = array(
        		'name'      => 'mod_gallery',
        		'fullpath'  => '/mod/gallery/module.js',
        		'requires'  => array('base', 'dom', 'event','io',)
        );
        $PAGE->requires->js_init_call('M.mod_gallery.init', array(array('context'=>$context->id,'currentImage'=>$iid)), false, $module);
        echo $renderer->render(new gallery_header($gallery->name(),$context));
        $images = gallery_load_images($gallery, $context);
        echo $renderer->render(new gallery_image_preview($images[$iid], $images, $cm, $context, $USER->editing));
        break;
    case 'addimages':
        echo $renderer->render(new gallery_header($gallery->name(),$context));
        echo $renderer->render(new gallery_form('imageupload',$mform));
        break;
    case 'addimagedesc':
    case 'editimages':
    case 'editimage':
        echo $renderer->render(new gallery_header($gallery->name(),$context));
        require_once($CFG->dirroot.'/mod/gallery/image_edit_form.php');
        echo $renderer->render(new gallery_form('imageedit', $mform));
        break;
}

echo $renderer->render_footer();