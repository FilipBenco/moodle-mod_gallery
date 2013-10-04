<?php

defined('MOODLE_INTERNAL') || die();

class gallery {
    
    protected $data;
    
    public function __construct($id) {
        global $DB;
        $this->data = $DB->get_record('gallery',array('id'=>$id),'*', MUST_EXIST);
    }
    
    public function id() {
        return $this->data->id;
    }
    
    public function course() {
        return $this->data->course;
    }
    
    public function name() {
        return $this->data->name;
    }
    
    public function intro() {
        return $this->data->intro;
    }
    
    public function introformat() {
        return $this->data->introformat;
    }
    
    public function showdescription() {
        return $this->data->showdescription;
    }
    
    public function showthumbnails() {
        return $this->data->showthumbnails;
    }
    
    public function showoriginalimage() {
        return $this->data->showoriginalimage;
    }
    
    public function imageattachments() {
        return $this->data->imageattachments;
    }
    
    public function isValid() {
        return !is_null($this->data);
    }
    
    public function data() {
        return $this->data;
    }
}
