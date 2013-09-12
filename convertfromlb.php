<?php

require_once('../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->dirroot.'/course/modlib.php');
require_once($CFG->dirroot.'/mod/gallery/lib.php');
require_once($CFG->dirroot.'/mod/gallery/mod_form.php');
require_once($CFG->dirroot.'/mod/gallery/image.class.php');
require_once($CFG->dirroot.'/mod/gallery/imagemanager.class.php');

$lbgModule = $DB->get_record('modules',array('name'=>'lightboxgallery'),'*',MUST_EXIST);

$lbgalleries = $DB->get_records('lightboxgallery',array('course'=>877));
echo 'Found Lightbox galleries: '.count($lbgalleries),'<br /><br />';
foreach($lbgalleries as $lbgallery) {
    $course = $DB->get_record('course', array('id'=>$lbgallery->course), '*', MUST_EXIST);
    $lbgcm = $DB->get_record('course_modules',array('module'=>$lbgModule->id,'instance'=>$lbgallery->id,'course'=>$course->id),'*',MUST_EXIST);
    $section = $DB->get_record('course_sections',array('id'=>$lbgcm->section),'*',MUST_EXIST);
    list($module, $context, $cw) = can_add_moduleinfo($course, 'gallery', $section->section);

    $cm = null;

    $data = new stdClass();
    $data->section            = $section->section;  // The section number itself - relative!!! (section column in course_sections)
    $data->visible            = $lbgcm->visible;
    $data->course             = $course->id;
    $data->module             = $module->id;
    $data->modulename         = $module->name;
    $data->groupmode          = groups_get_activity_groupmode($lbgcm); // locked later if forced
    $data->groupingid         = $lbgcm->groupingid;
    $data->groupmembersonly   = $lbgcm->groupmembersonly;
    $data->id                 = '';
    $data->instance           = '';
    $data->coursemodule       = '';
    $data->add                = 'gallery';
    $data->return             = 0; //must be false if this is an add, go back to course view on cancel
    $data->sr                 = null;
    $data->completion         = $lbgcm->completion;
    $data->completionview     = $lbgcm->completionview;
    $data->completionexpected = $lbgcm->completionexpected;
    $data->completionusegrade = is_null($lbgcm->completiongradeitemnumber) ? 0 : 1;
    $data->showdescription    = $lbgcm->showdescription;
    $data->cmidnumber         = $lbgcm->idnumber;          // The cm IDnumber

    $data->intro = $lbgallery->intro;
    $data->introformat = $lbgallery->introformat;
    $data->name = $lbgallery->name;
    $data->showdescription = 1;
    $data->showthumbnails = 0;
    $data->imageattachments = 0;
    
    if (!empty($CFG->enableavailability)) {
        $data->availablefrom      = $lbgcm->availablefrom;
        $data->availableuntil     = $lbgcm->availableuntil;
        $data->showavailability   = $lbgcm->showavailability;
    }

    $draftid_editor = file_get_submitted_draft_itemid('introeditor');
    $currentintro = file_prepare_draft_area($draftid_editor, $context->id, 'mod_lightboxgallery', 'intro', 0, array('subdirs'=>true), $data->intro);
    $data->introeditor = array('text'=>$currentintro, 'format'=>$data->introformat, 'itemid'=>$draftid_editor);
    
    $mform = new mod_gallery_mod_form($data, $cw->section, $cm, $course);
    $mform->set_data($data);

    $fromform = gallery_add_moduleinfo($mform->get_data(), $course, $mform);
    
    //convert images
    $fs = get_file_storage();
    $stored_files = $fs->get_area_files($context->id, 'mod_lightboxgallery', 'gallery_images');

    $galleryId = $fromform->instance;
    echo 'Created new gallery: '.$galleryId.' from '.$lbgallery->id.'<br />';
    echo 'Found images: '.count($stored_files).'<br /><br />';
    foreach ($stored_files as $stored_file) {
        if (!$stored_file->is_valid_image()) 
            continue;
        
        $imgData = gallery_image::get_initial_data();
        $imgData->gallery = $galleryId;
        if ($imgData = $DB->get_record('lightboxgallery_image_meta', array('gallery' => $lbgallery->id, 'image' => $stored_file->get_filename(), 'metatype' => 'caption'))) {
            $imgData->description = $image_meta->description;
        }
        $imgData->name = $stored_file->get_filename();
        $imgData->sourcetype = GALLERY_IMAGE_SOURCE_TEXT;
        $imgData->source = 'Converted from LighboxGallery';
        
        $image_data = gallery_imagemanager::create_image($imgData);
        
        $filepath = '/'.$galleryId.'/';
        $filename = $image_data->id.'.'.pathinfo($stored_file->get_filename(), PATHINFO_EXTENSION);
        $fileinfo = array(
            'contextid' => $context->id,
            'component' => 'mod_gallery',
            'filearea' =>  GALLERY_IMAGES_FILEAREA,
            'itemid' => $galleryId,
            'filepath' => $filepath,
            'filename' =>  $filename
        );
        if (!$fs->get_file($context->id, 'mod_gallery', GALLERY_IMAGES_FILEAREA, $galleryId, $filepath, $filename)) {
            $file = $fs->create_file_from_storedfile($fileinfo, $stored_file);
            $image = new gallery_image($image_data, $file, $context);
        }

    }
    
}

echo 'Done!';
function gallery_add_moduleinfo($moduleinfo, $course, $mform = null) {
    global $DB, $CFG;

    $moduleinfo->course = $course->id;
    $moduleinfo = set_moduleinfo_defaults($moduleinfo);

    if (!empty($course->groupmodeforce) or !isset($moduleinfo->groupmode)) {
        $moduleinfo->groupmode = 0; // Do not set groupmode.
    }

    if (!course_allowed_module($course, $moduleinfo->modulename)) {
        print_error('moduledisable', '', '', $moduleinfo->modulename);
    }

    // First add course_module record because we need the context.
    $newcm = new stdClass();
    $newcm->course           = $course->id;
    $newcm->module           = $moduleinfo->module;
    $newcm->instance         = 0; // Not known yet, will be updated later (this is similar to restore code).
    $newcm->visible          = $moduleinfo->visible;
    $newcm->visibleold       = $moduleinfo->visible;
    $newcm->groupmode        = $moduleinfo->groupmode;
    $newcm->groupingid       = $moduleinfo->groupingid;
    $newcm->groupmembersonly = $moduleinfo->groupmembersonly;
    $completion = new completion_info($course);
    if ($completion->is_enabled()) {
        $newcm->completion                = $moduleinfo->completion;
        $newcm->completiongradeitemnumber = $moduleinfo->completiongradeitemnumber;
        $newcm->completionview            = $moduleinfo->completionview;
        $newcm->completionexpected        = $moduleinfo->completionexpected;
    }
    if(!empty($CFG->enableavailability)) {
        $newcm->availablefrom             = $moduleinfo->availablefrom;
        $newcm->availableuntil            = $moduleinfo->availableuntil;
        $newcm->showavailability          = $moduleinfo->showavailability;
    }
    if (isset($moduleinfo->showdescription)) {
        $newcm->showdescription = $moduleinfo->showdescription;
    } else {
        $newcm->showdescription = 0;
    }

    if (!$moduleinfo->coursemodule = add_course_module($newcm)) {
        print_error('cannotaddcoursemodule');
    }

    if (plugin_supports('mod', $moduleinfo->modulename, FEATURE_MOD_INTRO, true)) {
        $introeditor = $moduleinfo->introeditor;
        unset($moduleinfo->introeditor);
        $moduleinfo->intro       = $introeditor['text'];
        $moduleinfo->introformat = $introeditor['format'];
    }

    $addinstancefunction    = $moduleinfo->modulename."_add_instance";
    $returnfromfunc = $addinstancefunction($moduleinfo, $mform);
    if (!$returnfromfunc or !is_number($returnfromfunc)) {
        // Undo everything we can.
        $modcontext = context_module::instance($moduleinfo->coursemodule);
        delete_context(CONTEXT_MODULE, $moduleinfo->coursemodule);
        $DB->delete_records('course_modules', array('id'=>$moduleinfo->coursemodule));

        if (!is_number($returnfromfunc)) {
            print_error('invalidfunction', '', course_get_url($course, $cw->section));
        } else {
            print_error('cannotaddnewmodule', '', course_get_url($course, $cw->section), $moduleinfo->modulename);
        }
    }

    $moduleinfo->instance = $returnfromfunc;

    $DB->set_field('course_modules', 'instance', $returnfromfunc, array('id'=>$moduleinfo->coursemodule));

    // Update embedded links and save files.
    $modcontext = context_module::instance($moduleinfo->coursemodule);
    if (!empty($introeditor)) {
        $moduleinfo->intro = gallery_file_save_draft_area_files($introeditor['itemid'], $modcontext->id,
                                                      'mod_'.$moduleinfo->modulename, 'intro', 0,
                                                      array('subdirs'=>true), $introeditor['text']);
        $DB->set_field($moduleinfo->modulename, 'intro', $moduleinfo->intro, array('id'=>$moduleinfo->instance));
    }

    // Course_modules and course_sections each contain a reference to each other.
    // So we have to update one of them twice.
    $sectionid = course_add_cm_to_section($course, $moduleinfo->coursemodule, $moduleinfo->section);

    // Make sure visibility is set correctly (in particular in calendar).
    // Note: allow them to set it even without moodle/course:activityvisibility.
    set_coursemodule_visible($moduleinfo->coursemodule, $moduleinfo->visible);

    if (isset($moduleinfo->cmidnumber)) { // Label.
        // Set cm idnumber - uniqueness is already verified by form validation.
        set_coursemodule_idnumber($moduleinfo->coursemodule, $moduleinfo->cmidnumber);
    }

    // Set up conditions.
    if ($CFG->enableavailability) {
        condition_info::update_cm_from_form((object)array('id'=>$moduleinfo->coursemodule), $moduleinfo, false);
    }

    $eventname = 'mod_created';

    add_to_log($course->id, "course", "add mod",
               "../mod/$moduleinfo->modulename/view.php?id=$moduleinfo->coursemodule",
               "$moduleinfo->modulename $moduleinfo->instance");
    add_to_log($course->id, $moduleinfo->modulename, "add",
               "view.php?id=$moduleinfo->coursemodule",
               "$moduleinfo->instance", $moduleinfo->coursemodule);

    $moduleinfo = edit_module_post_actions($moduleinfo, $course, 'mod_created');

    return $moduleinfo;
}

function gallery_file_save_draft_area_files($draftitemid, $contextid, $component, $filearea, $itemid, array $options=null, $text=null, $forcehttps=false) {
    global $USER;

    $usercontext = context_user::instance($USER->id);
    $fs = get_file_storage();

    $options = (array)$options;
    if (!isset($options['subdirs'])) {
        $options['subdirs'] = false;
    }
    if (!isset($options['maxfiles'])) {
        $options['maxfiles'] = -1; // unlimited
    }
    if (!isset($options['maxbytes']) || $options['maxbytes'] == USER_CAN_IGNORE_FILE_SIZE_LIMITS) {
        $options['maxbytes'] = 0; // unlimited
    }
    if (!isset($options['areamaxbytes'])) {
        $options['areamaxbytes'] = FILE_AREA_MAX_BYTES_UNLIMITED; // Unlimited.
    }
    $allowreferences = true;
    if (isset($options['return_types']) && !($options['return_types'] & FILE_REFERENCE)) {
        // we assume that if $options['return_types'] is NOT specified, we DO allow references.
        // this is not exactly right. BUT there are many places in code where filemanager options
        // are not passed to file_save_draft_area_files()
        $allowreferences = false;
    }

    // Check if the draft area has exceeded the authorised limit. This should never happen as validation
    // should have taken place before, unless the user is doing something nauthly. If so, let's just not save
    // anything at all in the next area.
    if (file_is_draft_area_limit_reached($draftitemid, $options['areamaxbytes'])) {
        return null;
    }

    $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id');
    $oldfiles   = $fs->get_area_files($contextid, 'mod_lightboxgallery', $filearea, $itemid, 'id');

    // One file in filearea means it is empty (it has only top-level directory '.').
    if (count($draftfiles) > 1 || count($oldfiles) > 1) {
        // we have to merge old and new files - we want to keep file ids for files that were not changed
        // we change time modified for all new and changed files, we keep time created as is

        $newhashes = array();
        $filecount = 0;
        foreach ($draftfiles as $file) {
            if (!$options['subdirs'] && ($file->get_filepath() !== '/' or $file->is_directory())) {
                continue;
            }
            if (!$allowreferences && $file->is_external_file()) {
                continue;
            }
            if (!$file->is_directory()) {
                if ($options['maxbytes'] and $options['maxbytes'] < $file->get_filesize()) {
                    // oversized file - should not get here at all
                    continue;
                }
                if ($options['maxfiles'] != -1 and $options['maxfiles'] <= $filecount) {
                    // more files - should not get here at all
                    continue;
                }
                $filecount++;
            }
            $newhash = $fs->get_pathname_hash($contextid, $component, $filearea, $itemid, $file->get_filepath(), $file->get_filename());
            $newhashes[$newhash] = $file;
        }

        // Loop through oldfiles and decide which we need to delete and which to update.
        // After this cycle the array $newhashes will only contain the files that need to be added.
        foreach ($oldfiles as $oldfile) {
            $oldhash = $oldfile->get_pathnamehash();
            if (!isset($newhashes[$oldhash])) {
                // delete files not needed any more - deleted by user
                $oldfile->delete();
                continue;
            }

            $newfile = $newhashes[$oldhash];
            // Now we know that we have $oldfile and $newfile for the same path.
            // Let's check if we can update this file or we need to delete and create.
            if ($newfile->is_directory()) {
                // Directories are always ok to just update.
            } else if (($source = @unserialize($newfile->get_source())) && isset($source->original)) {
                // File has the 'original' - we need to update the file (it may even have not been changed at all).
                $original = file_storage::unpack_reference($source->original);
                if ($original['filename'] !== $oldfile->get_filename() || $original['filepath'] !== $oldfile->get_filepath()) {
                    // Very odd, original points to another file. Delete and create file.
                    $oldfile->delete();
                    continue;
                }
            } else {
                // The same file name but absence of 'original' means that file was deteled and uploaded again.
                // By deleting and creating new file we properly manage all existing references.
                $oldfile->delete();
                continue;
            }

            // status changed, we delete old file, and create a new one
            if ($oldfile->get_status() != $newfile->get_status()) {
                // file was changed, use updated with new timemodified data
                $oldfile->delete();
                // This file will be added later
                continue;
            }

            // Updated author
            if ($oldfile->get_author() != $newfile->get_author()) {
                $oldfile->set_author($newfile->get_author());
            }
            // Updated license
            if ($oldfile->get_license() != $newfile->get_license()) {
                $oldfile->set_license($newfile->get_license());
            }

            // Updated file source
            // Field files.source for draftarea files contains serialised object with source and original information.
            // We only store the source part of it for non-draft file area.
            $newsource = $newfile->get_source();
            if ($source = @unserialize($newfile->get_source())) {
                $newsource = $source->source;
            }
            if ($oldfile->get_source() !== $newsource) {
                $oldfile->set_source($newsource);
            }

            // Updated sort order
            if ($oldfile->get_sortorder() != $newfile->get_sortorder()) {
                $oldfile->set_sortorder($newfile->get_sortorder());
            }

            // Update file timemodified
            if ($oldfile->get_timemodified() != $newfile->get_timemodified()) {
                $oldfile->set_timemodified($newfile->get_timemodified());
            }

            // Replaced file content
            if (!$oldfile->is_directory() &&
                    ($oldfile->get_contenthash() != $newfile->get_contenthash() ||
                    $oldfile->get_filesize() != $newfile->get_filesize() ||
                    $oldfile->get_referencefileid() != $newfile->get_referencefileid() ||
                    $oldfile->get_userid() != $newfile->get_userid())) {
                $oldfile->replace_file_with($newfile);
                // push changes to all local files that are referencing this file
                $fs->update_references_to_storedfile($oldfile);
            }

            // unchanged file or directory - we keep it as is
            unset($newhashes[$oldhash]);
        }

        // Add fresh file or the file which has changed status
        // the size and subdirectory tests are extra safety only, the UI should prevent it
        foreach ($newhashes as $file) {
            $file_record = array('contextid'=>$contextid, 'component'=>$component, 'filearea'=>$filearea, 'itemid'=>$itemid, 'timemodified'=>time());
            if ($source = @unserialize($file->get_source())) {
                // Field files.source for draftarea files contains serialised object with source and original information.
                // We only store the source part of it for non-draft file area.
                $file_record['source'] = $source->source;
            }

            if ($file->is_external_file()) {
                $repoid = $file->get_repository_id();
                if (!empty($repoid)) {
                    $file_record['repositoryid'] = $repoid;
                    $file_record['reference'] = $file->get_reference();
                }
            }

            $fs->create_file_from_storedfile($file_record, $file);
        }
    }

    // note: do not purge the draft area - we clean up areas later in cron,
    //       the reason is that user might press submit twice and they would loose the files,
    //       also sometimes we might want to use hacks that save files into two different areas

    if (is_null($text)) {
        return null;
    } else {
        return file_rewrite_urls_to_pluginfile($text, $draftitemid, $forcehttps);
    }
}