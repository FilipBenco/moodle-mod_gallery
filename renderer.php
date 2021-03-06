<?php

defined('MOODLE_INTERNAL') || die();

class mod_gallery_renderer extends plugin_renderer_base {
    
    public function render_gallery_header(gallery_header $header) {
        $o = '';

        if ($header->subpage) {
            $this->page->navbar->add($header->subpage);
        }

        $this->page->set_title(get_string('pluginname', 'gallery'));
        $this->page->set_heading($header->heading);

        $o .= $this->output->header();
        
        $heading = format_string($header->heading, false, array('context' => $header->context));
        $o .= $this->output->heading($heading);

        return $o;
    }
    
    public function render_gallery_view_gallery(gallery_view_gallery $widget) {
        $o = '';
        
        if(count($widget->images)) {
            $urlparams = array('id' => $widget->coursemodule->id, 'gaction' => 'image','image'=>reset($widget->images)->id());
            $o .= $this->output->action_link(new moodle_url('/mod/gallery/view.php', $urlparams), get_string('viewpreview','gallery'),null,array('class'=>'mod-gallery-extra-nav'));
        }
        
        $o .= $this->output->box_start('generalbox boxaligncenter', 'intro');
        $o .= $this->output->box(format_text($widget->gallery->intro(), $widget->gallery->introformat()));
        $o .= $this->output->box_end();
        
        
        if($widget->edit) {
            
            $o .= $this->output->box_start('generalbox', 'mod-gallery-navigation-buttons');

            $urlparams = array('id' => $widget->coursemodule->id);

            if($widget->canadd) {
               $urlparams['gaction']= 'addimages';
               $o .= $this->output->single_button(new moodle_url('/mod/gallery/view.php', $urlparams), get_string('addimages','gallery'));
            }
            $o .= $this->output->box_end();
            
            $o .= $this->output->box('','mod-gallery-clear');
            
            $fUrl = new moodle_url('/mod/gallery/view.php',array('id' => $widget->coursemodule->id));
            $o .= '<form action="'.$fUrl->out().'" method="post" class="mod-gallery-edit-thumb-form">';
            
            $options = array();
            if($widget->canedit) {
                $options['batchedit'] = get_string('edit','gallery');
                $options['batchrotateleft'] = get_string('rotateleft','gallery');
                $options['batchrotateright'] = get_string('rotateright','gallery');
            }
            if($widget->candelete)
                $options['batchdelete'] = get_string ('delete','gallery');
            if($widget->candownload)
                $options['batchdownload'] = get_string ('download','gallery');
            if(count($options) && count($widget->images)) {
                $o .= $this->output->box_start('mod-gallery-select-deselect-container');
                $o .= $this->output->action_link('#',get_string('selectdeselectall','gallery'),null,array('class'=>'mod-gallery-select-all'));
                $o .= ' ';
                $o .= get_string('selectedimageslabel','gallery');
                $o .= '<select name="gaction1" id="mod-gallery-batch-action-select-1">';
                foreach($options as $key => $value)
                    $o .= '<option value="'.$key.'">'.$value.'</option>';
                $o .= '</select>';
                $o .= '<input type="submit" name="batchsubmit1" value="'.get_string('batchrun','gallery').'" />';
                $o .= $this->output->box_end();      
            }
        }
        
        $o .= $this->output->box_start('generalbox','mod-gallery-thumb-container');
        if($widget->edit)
            $o .= '<div id="mod-gallery-drop-indicator" style="display:none;"></div>';
        foreach($widget->images as $image) {
            $i = '<img src="'.$image->thumbnail().'" style="margin-top:'.floor((150-$image->t_height())/2).'px;"/>'; 
            if($widget->gallery->showthumbstitles())
                $i .= '<div class="mod-gallery-thumb-title">'.$image->data()->name.'</div>';
            $urlparams = array('id' => $widget->coursemodule->id, 'gaction' => 'image', 'image' => $image->id());
            
            if($widget->edit) {
                $o .= '<div class="mod-gallery-thumb-edit" data-image-id="'.$image->id().'">';
                $o .= $a = $this->output->action_link(new moodle_url('/mod/gallery/view.php', $urlparams), $i, null, array('class'=>'mod-gallery-image-thumb-a-edit'));
                $o .= $this->output->box('','mod-gallery-clear');
                $o .= $this->output->box_start('mod-gallery-thumb-actions');
                if($widget->canedit || $widget->candelete)
                    $o .= '<input type="checkbox" value="1" name="mod-gallery-batch-'.$image->id().'" class="mod-gallery-batch-checkbox"/>';
                if($widget->canedit || ($widget->caneditown && $image->data()->user == $widget->currentuser)) {
                    $urlparams['gaction'] = 'editimageg';
                    $urlparams['image'] = $image->id();
                    $o .= $this->output->action_link(new moodle_url('/mod/gallery/view.php', $urlparams), $this->output->pix_icon('edit', get_string('editimage','gallery'),'mod_gallery'));
                    $urlparams['gaction'] = 'rotateleftg';
                    $o .= $this->output->action_link(new moodle_url('/mod/gallery/view.php', $urlparams), $this->output->pix_icon('rotateleft', get_string('rotateleft','gallery'),'mod_gallery'));
                    $urlparams['gaction'] = 'rotaterightg';
                    $o .= $this->output->action_link(new moodle_url('/mod/gallery/view.php', $urlparams), $this->output->pix_icon('rotateright', get_string('rotateright','gallery'),'mod_gallery'));
                }
                if($widget->canedit)
                    $o .= $this->output->pix_icon('dragdrop', get_string('moveimage','gallery'),'mod_gallery',array('class'=>'mod-gallery-drag-thumb'));
                if($widget->candelete || ($widget->candeleteown && $image->data()->user == $widget->currentuser)) {
                    $urlparams['gaction'] = 'imagedelete';
                    $o .= $this->output->action_link(new moodle_url('/mod/gallery/view.php', $urlparams), $this->output->pix_icon('delete', get_string('deleteimage','gallery'),'mod_gallery'), null, array('onclick'=>"return confirm('".get_string('confirmdelete','gallery')."')"));
                }
                $o .= $this->output->box_end();
                $o .= '</div>';
            } else
                $o .= $this->output->action_link(new moodle_url('/mod/gallery/view.php', $urlparams), $i, null, array('class'=>'mod-gallery-image-thumb-a'));
        }
        $o .= $this->output->box('','mod-gallery-clear');
        $o .= $this->output->box_end();
        
        if($widget->edit) {
            if(count($widget->images) && count($options)) {
                $o .= $this->output->box_start('mod-gallery-select-deselect-container');
                $o .= $this->output->action_link('#',get_string('selectdeselectall','gallery'),null,array('class'=>'mod-gallery-select-all'));
                $o .= ' ';
                $o .= get_string('selectedimageslabel','gallery');
                $o .= '<select name="gaction2" id="mod-gallery-batch-action-select-2">';
                foreach($options as $key => $value)
                    $o .= '<option value="'.$key.'">'.$value.'</option>';
                $o .= '</select>';
                $o .= '<input type="submit" name="batchsubmit2" value="'.get_string('batchrun','gallery').'" />';
                $o .= $this->output->box_end();                    
            }
            $o .= '</form">';
        }
        return $o;
    }
    
    public function render_gallery_image_preview(gallery_image_preview $img) {
        global $CFG;
        require_once($CFG->dirroot.'/comment/lib.php');
        $o = '';
        
        $urlparams = array('id' => $img->coursemodule->id);
        $o .= $this->output->action_link(new moodle_url('/mod/gallery/view.php', $urlparams), get_string('returntogallery','gallery'),null,array('class'=>'mod-gallery-extra-nav'));
        
        $o .= $this->output->heading($img->image->data()->name, '3','','mod-gallery-image-name');
        
        $o .= $this->output->box_start('','mod-gallery-image-source');
        $o .= $this->output->pix_icon('author', get_string('source','gallery'), 'mod_gallery',array('onclick'=>'return toogleSource()','style'=>'cursor:pointer;')).' ';
        $o .= '<span style="display:none;"> ';
        $o .= '<strong>'.get_string('source','gallery').': </strong>';
        
        if($img->image->data()->sourcetype == GALLERY_IMAGE_SOURCE_OWN) {
            $urlparams = array('id'=>$img->user->id);
            $o .= $this->output->action_link(new moodle_url('/user/profile.php',$urlparams), fullname($img->user));
        } elseif($img->image->data()->sourcetype == GALLERY_IMAGE_SOURCE_TEXT) 
            $o .= $img->image->data()->sourcetext;
        
        $o .= '</span>'; 
        $o .= $this->output->box_end();
        
        $o .= $this->output->box_start('generalbox', 'mod-gallery-navigation-buttons');
        if($img->edit) {
            if($img->canedit || ($img->caneditown && $img->image->data()->user == $img->currentuser)) {
                $urlparams = array('id' => $img->coursemodule->id, 'gaction' => 'editimage', 'image'=>$img->image->id());
                $o .= $this->output->action_link(new moodle_url('/mod/gallery/view.php', $urlparams), $this->output->pix_icon('edit', get_string('editimage','gallery'),'mod_gallery'),null,array('class'=>'mod-gallery-edit-actions'));
                $urlparams['gaction'] = 'rotatelefti';
                $o .= $this->output->action_link(new moodle_url('/mod/gallery/view.php', $urlparams), $this->output->pix_icon('rotateleft', get_string('rotateleft','gallery'),'mod_gallery'),null,array('class'=>'mod-gallery-edit-actions'));
                $urlparams['gaction'] = 'rotaterighti';
                $o .= $this->output->action_link(new moodle_url('/mod/gallery/view.php', $urlparams), $this->output->pix_icon('rotateright', get_string('rotateright','gallery'),'mod_gallery'),null,array('class'=>'mod-gallery-edit-actions'));
            }
            if($img->candelete || ($img->candeleteown && $img->image->data()->user == $img->currentuser)) {
                $urlparams['gaction'] = 'imagedelete';
                $o .= $this->output->action_link(new moodle_url('/mod/gallery/view.php', $urlparams), $this->output->pix_icon('delete', get_string('deleteimage','gallery'),'mod_gallery'), null, array('onclick'=>"return confirm('".get_string('confirmdelete','gallery')."')",'class'=>'mod-gallery-delete-actions'));
            }
        }
        $o .= $this->output->box_end();
        
        $o .= $this->output->box_start('mod-gallery-images-div');
        
        $o .= $this->output->box_start('mod-gallery-image-preview');
        
        if($img->image->data()->ordering != 1)
            $o .= $this->output->pix_icon('prev', get_string('previousimage','gallery'), 'mod_gallery',array('id'=>'mod-gallery-image-previous', 'style'=>'top:'.($img->previewheight/2-23).'px','onclick'=>'return showImagePrev()'));
        else
            $o .= $this->output->pix_icon('prev', get_string('previousimage','gallery'), 'mod_gallery',array('id'=>'mod-gallery-image-previous', 'style'=>'top:'.($img->previewheight/2-23).'px;display:none;','onclick'=>'return showImagePrev()'));
        
        if($img->image->data()->ordering == count($img->thumbnails))
            $o .= $this->output->pix_icon('next', get_string('nextimage','gallery'), 'mod_gallery',array('id'=>'mod-gallery-image-next', 'style'=>'top:'.($img->previewheight/2-23).'px;display:none;','onclick'=>'return showImageNext()'));
        else
            $o .= $this->output->pix_icon('next', get_string('nextimage','gallery'), 'mod_gallery',array('id'=>'mod-gallery-image-next', 'style'=>'top:'.($img->previewheight/2-23).'px','onclick'=>'return showImageNext()'));
        
        $o .= $this->output->box_start();
        $o .= '<div style="height:'.$img->previewheight.'px;position:relative;">';
        foreach($img->thumbnails as $thumb) {
            if($thumb->id() == $img->image->id()) 
                $o .= '<a href="'.$thumb->image().'" data-lightbox="gallery" title="'.$thumb->data()->name.'" class="mod-gallery-img-preview-a" id="mod-gallery-image-perview-a-'.$thumb->id().'" >';
            else 
                $o .= '<a href="'.$thumb->image().'" data-lightbox="gallery" title="'.$thumb->data()->name.'" class="mod-gallery-img-preview-a" style="display:none;" id="mod-gallery-image-perview-a-'.$thumb->id().'" >';
                
            $o .= '<img src="'.$thumb->preview().'" class="mod-gallery-image-preview-img"/>';  
            $o .= '</a>';
        }
        $o .= '</div>';
        $o .= $this->output->box_end();
              
        $o .= $this->output->box_end();
        
        $o .= '<div class="mod-gallery-images-side" style="height:'.$img->previewheight.'px">';
                
        foreach($img->thumbnails as $thumb) {
            $t = '<img src="'.$thumb->thumbnail().'" />';
            if($img->showthumbstitles)
                $t .= '<div class="mod-gallery-thumb-title">'.$thumb->data()->name.'</div>';
            $o .= $this->output->action_link('#', $t,null,array('onclick'=>'return showImage('.$thumb->id().')','data-id'=>$thumb->id(),'data-preview'=>$thumb->preview(),'id'=>'mod-gallery-thumb-'.$thumb->id()));
            $o .= $this->output->box('', 'mod-gallery-hidden-description', 'mod-gallery-image-desc-'.$thumb->id());
            $o .= $this->output->box('', 'mod-gallery-hidden-name', 'mod-gallery-image-name-'.$thumb->id());
            $o .= $this->output->box('', 'mod-gallery-hidden-source', 'mod-gallery-image-source-'.$thumb->id());
            $o .= $this->output->box('', 'mod-gallery-hidden-attachments', 'mod-gallery-image-attachments-'.$thumb->id());
        }
        $o .= '</div>';
        $o .= $this->output->box('','mod-gallery-clear');
        $o .= $this->output->box_end();
        
        if($img->showoriginalimage)
            $o .= $this->output->box($this->output->action_link($img->image->image(), get_string('downloadoriginalimage','gallery'),null,array('target'=>'_blank')),'','mod-gallery-image-preview-download');
        
        $o .= $this->output->box(format_text($img->image->data()->description,$img->image->data()->descriptionformat),null,'mod-gallery-image-desc');

        $o .= $this->output->box_start('','mod-gallery-image-attachments');
        foreach($img->image->attachments() as $att) {
            if($att->is_directory())
                continue;
            $ico = $this->output->pix_icon(file_file_icon($att),$att->get_filename(),'moodle',array('class'=>'icon'));
            $o .= $this->output->box_start();
            $attUrl = moodle_url::make_pluginfile_url($att->get_contextid(), $att->get_component(), 
                    $att->get_filearea(), $att->get_itemid(), 
                    $att->get_filepath(), $att->get_filename());
            $o .= $this->output->action_link($attUrl, $ico.$att->get_filename());
            $o .= $this->output->box_end();
        }
        $o .= $this->output->box_end();
        
        comment::init();
        $options = new stdClass();
        $options->area    = 'gallery_image_comments';
        $options->context = $img->context;
        $options->component = 'mod_gallery';
        $options->showcount = true;
        $options->displaycancel = true;
        foreach($img->thumbnails as $thumb) {
            $options->itemid  = $thumb->id();
            $comment = new comment($options);
            $comment->set_view_permission(true);
            if($thumb->id() == $img->image->id()) {
                $o .= '<div id="mod-gallery-image-comments-'.$thumb->id().'" class="box generalbox mod-gallery-image-comments">';
                $o .= $comment->output(true);
                $o .= "</div>";
            } else {
                $o .= '<div id="mod-gallery-image-comments-'.$thumb->id().'" class="box generalbox mod-gallery-image-comments" style="display:none;">';
                $o .= $comment->output(true);
                $o .= "</div>";
            }
        }
        

        return $o;
    }
    
    public function render_gallery_form(gallery_form $form) {
        $o = '';
        if ($form->jsinitfunction) {
            $this->page->requires->js_init_call($form->jsinitfunction, array());
        }
        $o .= $this->output->box_start('boxaligncenter ' . $form->classname);
        $o .= $this->moodleform($form->form);
        $o .= $this->output->box_end();
        return $o;
    }
    
    public function render_footer() {
        return $this->output->footer();
    }
    
    public function render_gallery_no_permission(gallery_no_permission $widget) {
        $o = '';
        $o .= get_string('nopermission','gallery');
        $o .= $this->output->single_button(new moodle_url('/mod/gallery/view.php', array('id' => $widget->cm->id)), get_string('returntogallery','gallery'));
        return $o;
    }
    
    protected function moodleform(moodleform $mform) {
        ob_start();
        $mform->display();
        $o = ob_get_contents();
        ob_end_clean();

        return $o;
    }
}