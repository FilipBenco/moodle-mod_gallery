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
        
        $o .= $this->output->box_start('generalbox boxaligncenter', 'intro');
        $o .= $this->output->box(format_text($widget->gallery->intro(), $widget->gallery->introformat()));
        $o .= $this->output->box_end();
        
        if($widget->edit) {
            $o .= $this->output->box_start('generalbox', 'mod-gallery-edit-buttons');
            $urlparams = array('id' => $widget->coursemodule->id, 'action' => 'addimages');
            $o .= $this->output->single_button(new moodle_url('/mod/gallery/view.php', $urlparams), get_string('addimages','gallery'));
            if(count($widget->images) > 0) {
                $urlparams['action'] = 'editimages';
                $o .= $this->output->single_button(new moodle_url('/mod/gallery/view.php', $urlparams), get_string('editimages','gallery'));
            }
            $o .= $this->output->box_end();
        }
        
       
        $o .= $this->output->box_start('generalbox','mod-gallery-thumb-container');
        if($widget->edit)
            $o .= $this->output->box('','','mod-gallery-drop-indicator');
        foreach($widget->images as $image) {
            $i = '<img src="'.$image->thumbnail().'" style="margin-top:'.floor((150-$image->t_height())/2).'px;"/>';         
            $urlparams = array('id' => $widget->coursemodule->id, 'action' => 'image', 'image' => $image->id());
            $a = $this->output->action_link(new moodle_url('/mod/gallery/view.php', $urlparams), $i, null, array('class'=>'mod-gallery-image-thumb-a'));
            
            if($widget->edit) {
                $o .= $this->output->box_start('mod-gallery-thumb-edit');
                $o .= $a;
                $o .= $this->output->box('','mod-gallery-clear');
                $o .= $this->output->box_start('mod-gallery-thumb-actions');
                $o .= $this->output->box('', 'mod-gallery-drag-thumb');
                $urlparams['action'] = 'imagedelete';
                $o .= $this->output->action_link(new moodle_url('/mod/gallery/view.php', $urlparams), get_string('delete','gallery'));
                $o .= $this->output->box_end();
                $o .= $this->output->box_end();
            } else
                $o.= $a;
        }
        $o .= $this->output->box_end();
        
        return $o;
    }
    
    public function render_gallery_image_preview(gallery_image_preview $img) {
        global $CFG;
        require_once($CFG->dirroot.'/comment/lib.php');
        $o = '';
        
        $o .= $this->output->heading($img->image->data()->name, '3','','mod-gallery-image-name');
        
        if($img->edit) {
            $o .= $this->output->box_start('generalbox', 'mod-gallery-edit-buttons');
            $urlparams = array('id' => $img->coursemodule->id, 'action' => 'editimage', 'image'=>$img->image->id());
            $o .= $this->output->single_button(new moodle_url('/mod/gallery/view.php', $urlparams), get_string('editimage','gallery'));
            $urlparams['action'] = 'rotatelefti';
            $o .= $this->output->single_button(new moodle_url('/mod/gallery/view.php', $urlparams), get_string('rotateleft','gallery'));
            $urlparams['action'] = 'rotaterighti';
            $o .= $this->output->single_button(new moodle_url('/mod/gallery/view.php', $urlparams), get_string('rotateright','gallery'));
            $urlparams['action'] = 'imagedelete';
            $o .= $this->output->single_button(new moodle_url('/mod/gallery/view.php', $urlparams), get_string('deleteimage','gallery'));
            $o .= $this->output->box_end();
        }
        
        $o .= $this->output->box_start('mod-gallery-images-div');
        
        $o .= $this->output->box_start('mod-gallery-image-preview');
        
        $o .= $this->output->box_start();
        $o .= $this->output->box_start('mod-gallery-image-preview-table');
        $o .= $this->output->box_start('mod-gallery-image-preview-table-cell');
        foreach($img->thumbnails as $thumb) {
            if($thumb->id() == $img->image->id()) 
                $o .= '<a href="'.$thumb->image().'" data-lightbox="gallery" title="'.$thumb->data()->name.'" id="mod-gallery-image-perview-a-'.$thumb->id().'" >';
            else 
                $o .= '<a href="'.$thumb->image().'" data-lightbox="gallery" title="'.$thumb->data()->name.'" style="display:none;" id="mod-gallery-image-perview-a-'.$thumb->id().'" >';
                
            $o .= '<img src="'.$thumb->preview().'" class="mod-gallery-image-preview-img"/>';  
            $o .= '</a>';
        }
        $o .= $this->output->box_end();
        $o .= $this->output->box_end();
        $o .= $this->output->box_end();
        
        $o .= $this->output->box_end();
        
        $o .= $this->output->box_start('mod-gallery-images-side');
                
        foreach($img->thumbnails as $thumb) {
            $o .= $this->output->action_link('#', '<img src="'.$thumb->thumbnail().'" />',null,array('onclick'=>'return showImage('.$thumb->id().')','data-preview'=>$thumb->preview(),'id'=>'mod-gallery-thumb-'.$thumb->id()));
            $o .= $this->output->box('', 'mod-gallery-hidden-description', 'mod-gallery-image-desc-'.$thumb->id());
            $o .= $this->output->box('', 'mod-gallery-hidden-name', 'mod-gallery-image-name-'.$thumb->id());
            $o .= $this->output->box('', 'mod-gallery-hidden-source', 'mod-gallery-image-source-'.$thumb->id());
        }
        $o .= $this->output->box_end();
        $o .= $this->output->box('','mod-gallery-clear');
        $o .= $this->output->box_end();
        
        $o .= $this->output->box_start('','mod-gallery-image-source');
        if($img->image->data()->sourcetype == GALLERY_IMAGE_SOURCE_OWN) {
            $urlparams = array('id'=>$img->user->id);
            $o .= '<strong>'.get_string('author','gallery') . ':</strong> ';
            $o .= $this->output->action_link(new moodle_url('/user/profile.php',$urlparams), fullname($img->user));
        }
        if($img->image->data()->sourcetype == GALLERY_IMAGE_SOURCE_TEXT) {
            $o .= '<strong>'.get_string('source','gallery') . ':</strong> ';
            $o .= $img->image->data()->source;
        }
        $o .= $this->output->box_end();
        
        $o .= $this->output->box(format_text($img->image->data()->description,$img->image->data()->descriptionformat),null,'mod-gallery-image-desc');

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
    
    protected function moodleform(moodleform $mform) {
        ob_start();
        $mform->display();
        $o = ob_get_contents();
        ob_end_clean();

        return $o;
    }
}