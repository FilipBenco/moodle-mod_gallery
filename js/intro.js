M.mod_gallery = M.mod_gallery || {};

M.mod_gallery.init = function(Y, cfg) {
    this.Y = Y;
    this.currentPosition = new Array();
    this.maxScroll = Y.one(".mod-gallery-intro-thumb-cont-helper > ul").get('winWidth')-Y.one('.mod-gallery-intro-thumbnails-container').get('winWidth');    
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
        if((M.mod_gallery.maxScroll - M.mod_gallery.currentPosition[gallery]) < 154)
            M.mod_gallery.currentPosition[gallery] = M.mod_gallery.maxScroll;
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
    return false;
}
