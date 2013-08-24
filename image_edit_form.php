<?php
defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');

class mod_gallery_image_edit_form extends moodleform {
    
    
    protected function definition() {
        $mform = $this->_form;
        
        $action = $this->_customdata['action'];
        
        $data = array();
        foreach($this->_customdata['images'] as $image) {
            if($action == 'addimagedesc') {
                $elementname = 'desc-'.  clean_param($image->stored_file()->get_filename(), PARAM_ALPHA);
                $mform->addElement('editor', $elementname,'<img src="'.
                moodle_url::make_pluginfile_url($image->stored_file()->get_contextid(), $image->stored_file()->get_component(), 
                        $image->stored_file()->get_filearea(), $image->stored_file()->get_itemid(), 
                        $image->stored_file()->get_filepath(), $image->stored_file()->get_filename()).'" style="max-width:150px; max-height:150px;" />',
                    array('rows' => 3), array('collapsed' => true));
            } else {
                $elementname = 'desc-'.$image->id();
                $mform->addElement('editor', $elementname,'<img src="'.$image->thumb().'" />',
                    array('rows' => 3), array('collapsed' => true));
            }
            
           
            $mform->setType($elementname, PARAM_RAW);
            $data[$elementname]['text'] = $image->description();
        }
        
        $mform->addElement('hidden','action',$action);
        if($action == 'editimage') {
            $mform->addElement('hidden','image',$this->_customdata['image']);
            $mform->setType('image',PARAM_INT);
        }
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden','id',$this->_customdata['id']);
        $mform->setType('id',PARAM_INT);
        
        $this->add_action_buttons(true, get_string('saveimages','gallery'));
        
        $this->set_data($data);
    }    
}