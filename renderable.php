<?php

defined('MOODLE_INTERNAL') || die();

class gallery_view_gallery implements renderable {
    
    public $gallery;
    public $edit;
    public $coursemodule;
    public $images;
    
    public function __construct(gallery $gallery, array $images, $coursemodule, $edit = 0) {
        $this->gallery = $gallery;
        $this->edit = $edit;
        $this->coursemodule = $coursemodule;
        $this->images = $images;
    }
}

class gallery_header implements renderable {
    
    public $heading;
    public $subpage;
    public $context;
    
    public function __construct($heading, $context, $subpage = 0) {
        $this->heading = $heading;
        $this->subpage = $subpage;
        $this->context = $context;
    }    
}

class gallery_image_preview implements renderable {
    
    public $image;
    public $thumbnails;
    public $context;
    public $edit;
    public $coursemodule;
    
    public function __construct($image, $thumbnails, $coursemodule, $context, $edit) {
        $this->image = $image;
        $this->thumbnails = $thumbnails;
        $this->context = $context;
        $this->edit = $edit;
        $this->coursemodule = $coursemodule;
    }
    
}

class gallery_form implements renderable {
    public $form = null;
    public $classname = '';
    public $jsinitfunction = '';


    public function __construct($classname, moodleform $form, $jsinitfunction = '') {
        $this->classname = $classname;
        $this->form = $form;
        $this->jsinitfunction = $jsinitfunction;
    }

}