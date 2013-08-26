<?php
defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


require_once($CFG->libdir.'/formslib.php');

class mod_gallery_image_upload_form extends moodleform {
    
    
    protected function definition() {
        $mform = $this->_form;
        
        $mform->addElement('filemanager','images',  get_string('images','gallery'), null, 
                array('subdirs'=>0,'accepted_types'=>array('web_image','archive')));
        
        $mform->addElement('hidden','action','addimages');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden','id',$this->_customdata['id']);
        $mform->setType('id',PARAM_INT);
        
        $this->add_action_buttons(true, get_string('uploadimages','gallery'));
    }    
}