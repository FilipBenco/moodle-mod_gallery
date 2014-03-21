<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_gallery_mod_form extends moodleform_mod {

    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {
        global $CFG;
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('galleryname', 'gallery'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) 
            $mform->setType('name', PARAM_TEXT);
        else 
            $mform->setType('name', PARAM_CLEANHTML);
        
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->add_intro_editor(true, get_string('description', 'gallery'));

        $mform->addElement('select', 'previewheight', get_string('previewheight', 'gallery'), array(400=>'400px',800=>'800px',1200=>'1200px'));
        $mform->addHelpButton('previewheight', 'previewheight', 'gallery');
        
        $mform->addElement('advcheckbox','showthumbstitles', get_string('showthumbstitles','gallery'));
        $mform->addHelpButton('showthumbstitles', 'showthumbstitles', 'gallery');
        
        $mform->addElement('advcheckbox','showthumbnails', get_string('showthumbnails','gallery'));
        $mform->addHelpButton('showthumbnails', 'showthumbnails', 'gallery');
        
        $mform->addElement('advcheckbox','showoriginalimage', get_string('showoriginalimage','gallery'));
        $mform->addHelpButton('showoriginalimage', 'showoriginalimage', 'gallery');
        
        $mform->addElement('advcheckbox','imageattachments', get_string('imageattachments','gallery'));
        $mform->addHelpButton('imageattachments', 'imageattachments', 'gallery');
       
        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }
    
    function add_completion_rules() {
        $mform =& $this->_form;

        $add_images_group=array();
        $add_images_group[] =& $mform->createElement('checkbox', 'completionimagesenabled', ' ', get_string('completionaddimages','gallery'));
        $add_images_group[] =& $mform->createElement('text', 'completionaddimages', ' ', array('size'=>3));
        $mform->setType('completionaddimages',PARAM_INT);
        $mform->addGroup($add_images_group, 'completionaddimagesgroup', get_string('completionaddimagesgroup','gallery'), array(' '), false);
        $mform->setHelpButton('completionaddimagesgroup', 'completionaddimageshelp', 'gallery');
        $mform->disabledIf('completionimages','completionimagesenabled','notchecked');
        
        $add_comments_group=array();
        $add_comments_group[] =& $mform->createElement('checkbox', 'completioncommentsenabled', ' ', get_string('completionaddcomments','gallery'));
        $add_comments_group[] =& $mform->createElement('text', 'completionaddcomments', ' ', array('size'=>3));
        $mform->setType('completionaddcomments',PARAM_INT);
        $mform->addGroup($add_comments_group, 'completionaddcommentsgroup', get_string('completionaddcommentsgroup','gallery'), array(' '), false);
        $mform->setHelpButton('completionaddcommentsgroup', 'completionaddcommentshelp', 'gallery');
        $mform->disabledIf('completioncomments','completioncommentsenabled','notchecked');

        return array('completionaddimagesgroup', 'completionaddcommentsgroup');
    }
    
    function completion_rule_enabled($data) {
        return ((!empty($data['completionimagesenabled']) && $data['completionaddimages']!=0) &&
                (!empty($data['completioncommentsenabled']) && $data['completionaddcomments']!=0)
                );
    }
    
    public function getNotSubmittedData() {
        return (object) $this->_form->exportValues();
    }
    
    function get_data() {
        $data = parent::get_data();
        if (!$data) 
            return $data;
        
        if (!empty($data->completionunlocked)) {
            // Turn off completion settings if the checkboxes aren't ticked
            $autocompletion = !empty($data->completion) && $data->completion==COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionimagesenabled) || !$autocompletion) 
               $data->completionaddimages = 0;
            if (empty($data->completioncommentsenabled) || !$autocompletion) 
               $data->completionaddcomments = 0;
        }
        return $data;
    }
    
    function data_preprocessing(&$default_values){
        parent::data_preprocessing($default_values);
        // Set up the completion checkboxes which aren't part of standard data.
        // We also make the default value (if you turn on the checkbox) for those
        // numbers to be 1, this will not apply unless checkbox is ticked.
        $default_values['completionimagesenabled']=
            !empty($default_values['completionaddimages']) ? 1 : 0;
        if(empty($default_values['completionaddimages'])) {
            $default_values['completionaddimages']=1;
        }
        
        $default_values['completioncommentsnabled']=
            !empty($default_values['completionaddcomments']) ? 1 : 0;
        if(empty($default_values['completionaddcomments'])) {
            $default_values['completionaddcomments']=1;
        }
    }
}