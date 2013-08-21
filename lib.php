<?php
defined('MOODLE_INTERNAL') || die();
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Supported features
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function book_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_MOD_INTRO:               
            return true;
        case FEATURE_SHOW_DESCRIPTION:        
            return true;
        default: 
            return null;
    }
}

/**
 * Add galerry instance.
 *
 * @param stdClass $data
 * @param stdClass $mform
 * @return int new book instance id
 */
function gallery_add_instance(stdClass $data, mod_assign_mod_form $form = null) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    $data->wattermark = 0;

    return $DB->insert_record('gallery', $data);
}

/**
 * Update gallery instance.
 *
 * @param stdClass $data
 * @param stdClass $mform
 * @return bool true
 */
function gallery_update_instance(stdClass $data, mod_assign_mod_form $form = null) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    $DB->update_record('gallery', $data);

    return true;
}

/**
 * Delete gallery instance by activity id
 *
 * @param int $id
 * @return bool success
 */
function gallery_delete_instance($id) {
    global $DB;

    if (!$galerry = $DB->get_record('gallery', array('id'=>$id))) 
        return false;
    
    $DB->delete_records('gallery', array('id'=>$galerry->id));

    return true;
}

