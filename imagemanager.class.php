<?php
require_once($CFG->dirroot.'/mod/gallery/image.class.php');


class gallery_imagemanager {
    
    public function create_image($gallery_id, $description, $format = FORMAT_MOODLE, $type = '') {
        global $DB;
        $record = new stdClass;
        $record->gallery = $gallery_id;
        $record->description = $description;
        $record->descriptionformat = $format;
        $record->timecreated = time();
        $record->timemodified = $record->timecreated;
        $record->type = $type;
        $record->ordering = $DB->count_records('gallery_images',array('gallery'=>$gallery_id)) + 1;
        $record->id = $DB->insert_record('gallery_images',$record);
        return $record;
    }
    
    public function get_images($gallery, $from = 0, $limit = 0) {
        global $DB;
        return $DB->get_records('gallery_images',array('gallery'=>$gallery->id()),'ordering ASC','*',$from,$limit);
    }
    
    public function delete_image($id) {
        global $DB;
        $img = $DB->get_record('gallery_images',array('id'=>$id));
        $DB->execute('UPDATE {gallery_images} SET ordering=ordering-1 WHERE ordering > ?', array($img->ordering));
        $DB->delete_records('gallery_images',array('id' => $id));
    }
    
}
