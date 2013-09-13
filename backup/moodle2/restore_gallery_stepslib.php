<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_lightboxgallery_activity_task
 */

/**
 * Structure step to restore one lightboxgallery activity
 */
require_once($CFG->dirroot.'/mod/gallery/locallib.php');

class restore_gallery_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();

        $gallery = new restore_path_element('gallery', '/activity/gallery');
        $paths[] = $gallery;

        $image = new restore_path_element('gallery_image', '/activity/gallery/images/image');
        $paths[] = $image;


        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    protected function process_gallery($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        // Insert the lightboxgallery record.
        $newitemid = $DB->insert_record('gallery', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    protected function process_gallery_image($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->gallery = $this->get_new_parentid('gallery');
        $data->user = $this->get_mappingid('user', $data->user);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        if($data->sourcetype == GALLERY_IMAGE_SOURCE_OWN)
            $data->sourceuser = $this->get_mappingid ('user', $data->sourceuser);
        $newitemid = $DB->insert_record('gallery_images', $data);
        $this->set_mapping('image_itemid', $oldid, $newitemid, true);
    }

    protected function after_execute() {
        $this->add_related_files('mod_gallery', 'intro', null);
        $this->add_related_files('mod_gallery', GALLERY_IMAGES_FILEAREA, 'image_itemid');
    }
}
