<?php
defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');

class mod_gallery_image_edit_form extends moodleform {
    
    
    protected function definition() {
        $mform = $this->_form;
        
        $action = $this->_customdata['action'];
        
        $data = array();
        foreach($this->_customdata['images'] as $image) {
            
            $uniqueId = '';
            $imagePreview = '';
            if($action == 'addimagedesc') {
                $uniqueId = clean_param($image->stored_file()->get_filename(), PARAM_ALPHA);
                $imagePreview =  moodle_url::make_pluginfile_url($image->stored_file()->get_contextid(), $image->stored_file()->get_component(), 
                        $image->stored_file()->get_filearea(), $image->stored_file()->get_itemid(), 
                        $image->stored_file()->get_filepath(), $image->stored_file()->get_filename());
                
            } else {
                $uniqueId = 'desc-'.$image->id();
                $mform->addElement('editor', 'desc-'.$uniqueId, '<img src="'.$image->thumbnail().'" />',
                    array('rows' => 3), array('collapsed' => true));
            }
            $mform->addElement('header','header-'.$uniqueId,'');
            $mform->setExpanded('header-'.$uniqueId);
            
            $mform->addElement('text','name-'.$uniqueId,  get_string('imagename','gallery'));
            $mform->setType('name-'.$uniqueId, PARAM_TEXT);
            
            $mform->addElement('editor', 'desc-'.$uniqueId,'<img src="'.$imagePreview.'" style="max-width:150px; max-height:150px;" />',
                    array('rows' => 3), array('collapsed' => true));
            $mform->setType('desc-'.$uniqueId, PARAM_RAW);
            
            $mform->addElement('checkbox', 'sourcetype-'.$uniqueId, get_string('sourceown','gallery'));
            $mform->setType('sourcetype-'.$uniqueId, PARAM_BOOL);
            
            $mform->addElement('text','source-'.$uniqueId,  get_string('source','gallery'));
            $mform->setType('source-'.$uniqueId,PARAM_TEXT);
            $mform->disabledIf('source-'.$uniqueId, 'sourcetype-'.$uniqueId, 'checked');
            
            $data['name-'.$uniqueId] = $image->data()->name;
            $data['desc-'.$uniqueId]['text'] = $image->data()->description;
            $data['desc-'.$uniqueId]['format'] = $image->data()->descriptionformat;
            if($image->data()->sourcetype == GALLERY_IMAGE_SOURCE_TEXT) {
                $data['source-'.$uniqueId] = $image->data()->source;
                $data['sourcetype-'.$uniqueId] = false;
            } elseif($image->data()->sourcetype == GALLERY_IMAGE_SOURCE_OWN) {
                $data['source-'.$uniqueId] = '';
                $data['sourcetype-'.$uniqueId] = true;
            }
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
    
    /**
     * Perform minimal validation on the grade form
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        $action = $this->_customdata['action'];
        foreach($this->_customdata['images'] as $image) {
            $uniqueId = '';
            if($action == 'addimagedesc') 
                $uniqueId = clean_param($image->stored_file()->get_filename(), PARAM_ALPHA);  
            else 
                $uniqueId = 'desc-'.$image->id();
            
            $sourceTypeName = 'sourcetype-'.$uniqueId;
            $sourceName = 'source-'.$uniqueId;
            $source = $data->$sourceName;
            if(!isset($data->$sourceTypeName) && empty(trim($source)))
                $errors[$sourceName] = get_string('missingsourceerror','gallery');

        }
        return $errors;
    }
}