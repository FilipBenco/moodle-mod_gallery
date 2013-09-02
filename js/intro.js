M.mod_gallery = M.mod_gallery || {};

M.mod_gallery.init = function(Y, cfg) {
    this.Y = Y;
    this.currentPosition = 0;
    this.maxPosition = Y.one("#mod-gallery-intro-thumbnails-container > ul").get('winWidth');
    this.currentY = Y.one("#mod-gallery-intro-thumbnails-container > ul").getY();
    
    M.mod_gallery.Y.one('#mod-gallery-image-next-intro').on('click',function(e) {
        M.mod_gallery.moveIntro('right');
    });
    
    M.mod_gallery.Y.one('#mod-gallery-image-previous-intro').on('click',function(e) {
        M.mod_gallery.moveIntro('left');
    });
};

M.mod_gallery.moveIntro = function(direction) {
    if(direction === 'right') {
        if(M.mod_gallery.currentPosition < 154)
            M.mod_gallery.currentPosition = 0;
        else
            M.mod_gallery.currentPosition -= 154;
    } else if(direction ==='left') {
        if((M.mod_gallery.maxPosition - M.mod_gallery.currentPosition) < 154)
            M.mod_gallery.currentPosition = M.mod_gallery.maxPosition;
        else
            M.mod_gallery.currentPosition += 154;
    }
    
    var move = new M.mod_gallery.Y.Anim({
            node: '#mod-gallery-intro-thumbnails-container > ul',
            to: {
                xy: [M.mod_gallery.currentPosition,M.mod_gallery.currentY]
            }
        });
    move.run();
};
