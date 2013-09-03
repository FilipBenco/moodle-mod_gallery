M.mod_gallery = M.mod_gallery || {};

M.mod_gallery.init = function(Y, cfg) {
    this.Y = Y;  
};

M.mod_gallery.moveIntro = function(direction,gallery) {
    var scroll = M.mod_gallery.Y.one('#mod-gallery-intro-thumb-cont-helper-'+gallery).get('winScrollX');
    var scrollS = M.mod_gallery.Y.one('#mod-gallery-intro-thumb-cont-helper-'+gallery).get('winScrollY');
    if(direction === 'left') 
       scroll -= 154;
    else if(direction ==='right') 
        scroll += 154;
    
    var move = new M.mod_gallery.Y.Anim({
            node: '#mod-gallery-intro-thumb-cont-helper-'+gallery,
            duration: 0.5,
            to: {
                scroll: [scroll,0]
            }
        });
    move.run();
};

function modGalleryMoveThumb(gallery,direction) {
    M.mod_gallery.moveIntro(direction,gallery);
    return false;
}
