<?php
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filelib.php");

define('GALLERY_IMAGES_FILEAREA','gallery_images');
define('GALLERY_IMAGE_THUMBS_FILEAREA','gallery_thumbs');
define('GALLERY_IMAGE_PREVIEWS_FILEAREA','gallery_previews');
define('GALLERY_IMAGE_DRAFTS_FILEAREA','gallery_drafts');

function gallery_process_editing($edit, $context) {
    global $USER;

    if (has_capability('mod/gallery:edit', $context)) {
        if ($edit != -1 and confirm_sesskey()) 
            $USER->editing = $edit;
    } else
        $USER->editing = 0;
    if(!isset($USER->editing))
        $USER->editing = 0;
}

function gallery_process_drafts($context, $gallery) {
    global $CFG, $USER, $PAGE;
    require_once($CFG->dirroot.'/mod/gallery/image.class.php');
    
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_gallery', GALLERY_IMAGE_DRAFTS_FILEAREA, $gallery->id());
    $fs->delete_area_files($context->id, 'mod_gallery', 'unpacktemp');
    
    $draftid = file_get_submitted_draft_itemid('images');
    if (!$files = $fs->get_area_files(
        get_context_instance(CONTEXT_USER, $USER->id)->id, 'user', 'draft', $draftid, 'filename ASC', false)) {
        redirect($PAGE->url);
    }

    $preloaded_images = array();
    
    $i = 1;
    foreach($files as $file) {
        if(!$file->is_valid_image()) {
            $packer = get_file_packer($file->get_mimetype());
            $file->extract_to_storage($packer, $context->id, 'mod_gallery', 'unpacktemp', $i, '/');
            $unpackedFiles = $fs->get_area_files($context->id, 'mod_gallery', 'unpacktemp', $i);
            $preloaded_images = array_merge($preloaded_images, array_reverse($unpackedFiles));
            $file->delete();
            $i++;
        } 
            $preloaded_images[] = $file;
    }
    
    $images = array();
    foreach ($preloaded_images as $file) {
        $data = gallery_image::get_initial_data();
        $data->name = $file->get_filename();
        if ($file->is_valid_image()) {
            $fileinfo = array(
                'contextid' => $context->id,
                'component' => 'mod_gallery',
                'filearea' =>  GALLERY_IMAGE_DRAFTS_FILEAREA,
                'itemid' => $gallery->id(),
                'filepath' => '/',
                'filename' =>  $file->get_filename()
            );
            if (!$fs->get_file($context->id, 'mod_gallery', GALLERY_IMAGE_DRAFTS_FILEAREA, $gallery->id(), '/', $file->get_filename())) {
                $file = $fs->create_file_from_storedfile($fileinfo, $file);
                $images[] = new gallery_image($data,$file,null,false);
            }
        }
    }
    $fs->delete_area_files($context->id, 'mod_gallery', 'unpacktemp');
    
    return $images;
}

function gallery_get_draft_images($context, $gallery) {
    global $PAGE, $CFG;
    $fs = get_file_storage();
    if (!$files = $fs->get_area_files(
        $context->id, 'mod_gallery', GALLERY_IMAGE_DRAFTS_FILEAREA, $gallery->id(), 'filename DESC', false)) {
        redirect($PAGE->url);
    }
    
    require_once($CFG->dirroot.'/mod/gallery/image.class.php');
    $images = array();
    foreach($files as $file) {
        $data = gallery_image::get_initial_data();
        $data->name = $file->get_filename();
        $images[] = new gallery_image($data,$file,null,false);
    }
    return $images;
}

function gallery_process_image_drats_save($data, $context, $gallery, $files) {
    global $CFG;
    require_once($CFG->dirroot.'/mod/gallery/image.class.php');
    require_once($CFG->dirroot.'/mod/gallery/imagemanager.class.php');

    $fs = get_file_storage();
    var_dump($data); die;
    foreach ($files as $file) {
        $uId = clean_param($file->stored_file()->get_filename(), PARAM_ALPHA);
        $imgData = gallery_image::from_form_data($uId, $data);
        $imgData->gallery = $gallery->id();
        $imgData->type = pathinfo($file->stored_file()->get_filename(), PATHINFO_EXTENSION);
        $image_data = gallery_imagemanager::create_image($imgData);
        
        $filepath = '/'.$gallery->id().'/';
        $filename = $image_data->id.'.'.pathinfo($file->stored_file()->get_filename(), PATHINFO_EXTENSION);
        $fileinfo = array(
            'contextid' => $context->id,
            'component' => 'mod_gallery',
            'filearea' =>  GALLERY_IMAGES_FILEAREA,
            'itemid' => $image_data->id,
            'filepath' => $filepath,
            'filename' =>  $filename
        );
        if (!$fs->get_file($context->id, 'mod_gallery', GALLERY_IMAGES_FILEAREA, $image_data->id, $filepath, $filename)) {
            $file = $fs->create_file_from_storedfile($fileinfo, $file->stored_file());
            new gallery_image($image_data, $file, $context);
        }
    }
    $fs->delete_area_files($context->id, 'mod_gallery', GALLERY_IMAGE_DRAFTS_FILEAREA, $gallery->id());
}

function gallery_process_images_save($data, $images) {
    global $CFG;
    require_once($CFG->dirroot.'/mod/gallery/imagemanager.class.php');
    require_once($CFG->dirroot.'/mod/gallery/image.class.php');
    
    foreach($images as $image) {
        $imgData = $image->from_form($data);
        if($image->data()->description != $imgData->description ||
                $image->data()->descriptionformat != $imgData->descriptionformat ||
                $image->data()->sourcetype != $imgData->sourceType ||
                ($image->data()->sourcetype == GALLERY_IMAGE_SOURCE_TEXT && $image->data()->source != $imgData->source)) {
            gallery_imagemanager::update_image($imgData);
        }
    }
}

function gallery_load_images($gallery, $context) {
    global $CFG;
    require_once($CFG->dirroot.'/mod/gallery/image.class.php');
    require_once($CFG->dirroot.'/mod/gallery/imagemanager.class.php');
    
    $images_db = gallery_imagemanager::get_images($gallery);
    
    $images = array();
    $fs = get_file_storage();
    $filepath = '/'.$gallery->id().'/';
    foreach($images_db as $idb) {
        $images[$idb->id] = new gallery_image($idb, $fs->get_file($context->id, 'mod_gallery', GALLERY_IMAGES_FILEAREA, $idb->id, $filepath,
                                       $idb->id.'.'.$idb->type),$context);
    }
    return $images;
}

function gallery_load_image($context,$iid) {
    global $CFG;
    require_once($CFG->dirroot.'/mod/gallery/image.class.php');
    require_once($CFG->dirroot.'/mod/gallery/imagemanager.class.php');
    $image_db = gallery_imagemanager::get_image($iid);
    
    $fs = get_file_storage();
    $filepath = '/'.$image_db->gallery.'/';
    
    return new gallery_image($image_db, $fs->get_file($context->id, 'mod_gallery', GALLERY_IMAGES_FILEAREA, $image_db->id, $filepath,
                                       $image_db->id.'.'.$image_db->type),$context);
}

function gallery_process_move_image($direction,$id) {
    global $DB;
    $i1 = $DB->get_record('gallery_images',array('id'=>$id));
    $i2 = null;
    if($direction == 'right') {
        $i2 = $DB->get_record('gallery_images',array('ordering'=>$i1->ordering+1,'gallery'=>$i1->gallery));
        $i1->ordering ++;
        $i2->ordering --;
    }
    if($direction == 'left') {
        $i2 = $DB->get_record('gallery_images',array('ordering'=>$i1->ordering-1,'gallery'=>$i1->gallery));
        $i1->ordering --;
        $i2->ordering ++;
    }
    $DB->update_record('gallery_images',$i1);
    $DB->update_record('gallery_images',$i2);
}

function gallery_process_rotate_image($direction,$iid,$context) {
    $angle = 90;
    if($direction == 'right')
        $angle = 270;
    $image = gallery_load_image($context, $iid);
    $image->rotate($angle);
}

function gallery_process_delete_image($iid, $context, $gallery) {
    global $CFG,$DB;
    require_once($CFG->dirroot.'/mod/gallery/image.class.php');
    require_once($CFG->dirroot.'/mod/gallery/imagemanager.class.php');
    require_once($CFG->dirroot.'/comment/lib.php');
    $img = gallery_imagemanager::get_image($iid);
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_gallery', GALLERY_IMAGES_FILEAREA, $img->id, '/'.$gallery->id().'/', $img->id.'.'.$img->type);
    $image = new gallery_image($img,$file,$context);
    $image->delete();
    gallery_imagemanager::delete_image($iid);
    comment::delete_comments(array('contextid'=>$context->id,'commentarea'=>'gallery_image_comments','itemid'=>$iid));
}

class gallery_content_file_info extends file_info_stored {
    public function get_parent() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->browser->get_file_info($this->context);
        }
        return parent::get_parent();
    }
    public function get_visible_name() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->topvisiblename;
        }
        return parent::get_visible_name();
    }
}