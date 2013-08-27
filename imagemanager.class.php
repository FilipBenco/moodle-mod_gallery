<?php
class gallery_imagemanager {
    
    public static function create_image(stdClass $image) {
        global $DB;
        $image->timecreated = time();
        $image->timemodified = $image->timecreated;
        $image->ordering = $DB->count_records('gallery_images',array('gallery'=>$image->gallery)) + 1;
        $image->id = $DB->insert_record('gallery_images',$image);
        return $image;
    }
    
    public static function update_image(stdClass $image) {
        global $DB;
        $image->timemodified = time();
        $DB->update_record('gallery_images',$image);
    }
    
    public static function get_images($gallery, $from = 0, $limit = 0) {
        global $DB;
        return $DB->get_records('gallery_images',array('gallery'=>$gallery->id()),'ordering ASC','*',$from,$limit);
    }
    
    public static function get_image($id) {
        global $DB;
        return $DB->get_record('gallery_images',array('id'=>$id));
    }
    
    public static function delete_image($id) {
        global $DB;
        $img = $DB->get_record('gallery_images',array('id'=>$id));
        $DB->execute('UPDATE {gallery_images} SET ordering=ordering-1 WHERE ordering > ?', array($img->ordering));
        $DB->delete_records('gallery_images',array('id' => $id));
    }
    
}
