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

        $mform->addElement('introshow', get_string('introshow','gallery'));
        $mform->addElement('introthumbnails', get_string('introthumbnails','gallery'));
       
        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }
}