<?php
defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


define('THUMBNAIL_WIDTH',150);
define('THUMBNAIL_HEIGHT',150);

define('PREVIEW_WIDTH',1024);
define('PREVIEW_HEIGHT',400);


require_once($CFG->libdir.'/gdlib.php');

class gallery_image {
    
    protected $data;
    protected $thumbnail;
    protected $preview;
    protected $image;
    
    protected $image_url;
    protected $preview_url;
    protected $thumb_url;
    
    protected $width;
    protected $height;
    
    protected $t_width;
    protected $t_height;
    
    protected $p_width;
    protected $p_height;
    
    protected $context;
    
    public function __construct($data, $file, $context, $prepare = true) {
       
        $this->data = $data;
        $this->context = $context;
        $this->image = $file;
        
        if($prepare) {
            $image_info = $this->image->get_imageinfo();

            $this->height = $image_info['height'];
            $this->width = $image_info['width'];
            
            $this->thumbnail = $this->get_thumbnail();
            if($this->thumbnail == null)
                $this->thumbnail = $this->create_thumbnail();
            
            $this->preview = $this->get_preview();
            if($this->preview == null)
                $this->preview = $this->create_preview();

            $this->image_url = moodle_url::make_pluginfile_url($this->image->get_contextid(), $this->image->get_component(), 
                    $this->image->get_filearea(), $this->image->get_itemid(), 
                    $this->image->get_filepath(), $this->image->get_filename());
            $this->thumb_url = moodle_url::make_pluginfile_url($this->thumbnail->get_contextid(), $this->thumbnail->get_component(), 
                    $this->thumbnail->get_filearea(), $this->thumbnail->get_itemid(), 
                    $this->thumbnail->get_filepath(), $this->thumbnail->get_filename());
            $this->preview_url = moodle_url::make_pluginfile_url($this->preview->get_contextid(), $this->preview->get_component(), 
                    $this->preview->get_filearea(), $this->preview->get_itemid(), 
                    $this->preview->get_filepath(), $this->preview->get_filename());
        }
    }
    
    public function setId($id) {
        $this->data->id = $id;
    }
    
    public function setDescription($description) {
        $this->data->description = $description;
    }
    
    public function setOrdering($ordering) {
        $this->data->ordering = $ordering;
    }
    
    public function id() {
        return $this->data->id;
    }
    
    public function description() {
        return $this->data->description;
    }
    
    public function ordering() {
        return $this->data->ordering;
    }
    
    public function thumbnail() {
        return $this->thumb_url;
    }
    
    public function image() {
        return $this->image_url;
    }
    
    public function preview() {
        return $this->preview_url;
    }
    
    public function width() {
        return $this->width;
    }
    
    public function height() {
        return $this->height;
    }
    
    public function data() {
        return $this->data;
    }
    
    public function t_width() {
        return $this->t_width;
    }
    
    public function t_height() {
        return $this->t_height;
    }
    
    public function p_width() {
        return $this->p_width;
    }
    
    public function p_height() {
        return $this->p_height;
    }
    
    public function stored_file() {
        return $this->image;
    }
    
    public function delete() {
        $this->delete_preview();
        $this->delete_thumbnail();
        $this->delete_image();
    }
    
    protected function get_thumbnail() {
        $fs = get_file_storage();
        $thumbnail = $fs->get_file($this->context->id, 'mod_gallery', GALLERY_IMAGE_THUMBS_FILEAREA, $this->data->id, $this->image->get_filepath(),
                                       $this->data->id.'.png');
        if ($thumbnail) {
            $image_info = $thumbnail->get_imageinfo();

            $this->t_height = $image_info['height'];
            $this->t_width = $image_info['width'];
            return $thumbnail;
        }

        return null;
    }
    
    protected function get_preview() {
        $fs = get_file_storage();
        $preview = $fs->get_file($this->context->id, 'mod_gallery', GALLERY_IMAGE_PREVIEWS_FILEAREA, $this->data->id, $this->image->get_filepath(),
                                       $this->data->id.'.png');
        if ($preview) {
            $image_info = $preview->get_imageinfo();

            $this->p_height = $image_info['height'];
            $this->p_width = $image_info['width'];
            return $preview;
        }

        return null;
    }
    
    public function create_thumbnail() {
        $fileinfo = array(
            'contextid' => $this->context->id,
            'component' => 'mod_gallery',
            'filearea' => GALLERY_IMAGE_THUMBS_FILEAREA,
            'itemid' => $this->data->id,
            'filepath' => $this->image->get_filepath(),
            'filename' => $this->data->id.'.png');

        ob_start();
        imagepng($this->get_image_resized(THUMBNAIL_WIDTH, THUMBNAIL_HEIGHT));
        $thumbnail = ob_get_clean();

        $this->delete_thumbnail();
        $fs = get_file_storage();
        return $fs->create_file_from_string($fileinfo, $thumbnail);
    }
    
    public function create_preview() {
        $fileinfo = array(
            'contextid' => $this->context->id,
            'component' => 'mod_gallery',
            'filearea' => GALLERY_IMAGE_PREVIEWS_FILEAREA,
            'itemid' => $this->data->id,
            'filepath' => $this->image->get_filepath(),
            'filename' => $this->data->id.'.png');

        ob_start();
        imagepng($this->get_image_resized(PREVIEW_WIDTH, PREVIEW_HEIGHT));
        $preview = ob_get_clean();

        $this->delete_preview();
        $fs = get_file_storage();
        return $fs->create_file_from_string($fileinfo, $preview);
    }
    
    protected function delete_thumbnail() {
        if (isset($this->thumbnail) && is_object($this->thumbnail)) {
            $this->thumbnail->delete();
            unset($this->thumbnail);
        }
    }
    
    protected function delete_preview() {
        if (isset($this->preview) && is_object($this->preview)) {
            $this->preview->delete();
            unset($this->preview);
        }
    }
    
    protected function delete_image() {
        if (isset($this->image) && is_object($this->image)) {
            $this->image->delete();
            unset($this->image);
        }
    }
    
    protected function get_image_resized($width, $height) {
        $image = imagecreatefromstring($this->image->get_content());
        
        $ratiow = $width / $this->width;
        $ratioh = $height / $this->height;

        if($ratiow>=1 && $ratioh>=1)
            return $image;
        
        if ($ratiow < $ratioh)
            $height =floor($this->height*$ratiow);
        else
            $width =floor($this->width*$ratioh);
        
        $resized = imagecreatetruecolor($width, $height);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

        return $resized;
    }
}