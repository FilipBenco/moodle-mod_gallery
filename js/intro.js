M.mod_gallery = M.mod_gallery || {};

M.mod_gallery.init = function(Y, cfg) {
    this.Y = Y;
    this.currentPosition = new Array();
    this.maxPosition = Y.one("#mod-gallery-intro-thumb-cont-helper > ul").get('winWidth');    
};

M.mod_gallery.moveIntro = function(direction,gallery) {
    if(!(this.currentPosition[gallery]))
        this.currentPosition[gallery] = 0;
    if(direction === 'left') {
        if(M.mod_gallery.currentPosition[gallery] < 154)
            M.mod_gallery.currentPosition[gallery] = 0;
        else
            M.mod_gallery.currentPosition[gallery] -= 154;
    } else if(direction ==='right') {
        if((M.mod_gallery.maxPosition - M.mod_gallery.currentPosition[gallery]) < 154)
            M.mod_gallery.currentPosition[gallery] = M.mod_gallery.maxPosition;
        else
            M.mod_gallery.currentPosition[gallery] += 154;
    }
    
    var move = new M.mod_gallery.Y.Anim({
            node: '#mod-gallery-intro-thumb-cont-helper-'+gallery,
            to: {
                scroll: [M.mod_gallery.currentPosition[gallery],0]
            }
        });
    move.run();
};

function modGalleryMoveThumb(gallery,direction) {
    M.mod_gallery.moveIntro(direction,gallery);
}
