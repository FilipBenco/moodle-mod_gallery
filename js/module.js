M.mod_gallery = M.mod_gallery || {};

M.mod_gallery.init = function(Y, cfg) {
    this.Y = Y;
    this.context = cfg.context;
    this.currentImage = cfg.currentImage;
};

M.mod_gallery.showImage = function(imageId) {
    M.mod_gallery.Y.one('#mod-gallery-image-perview-a-'+M.mod_gallery.currentImage).hide();
    M.mod_gallery.Y.one('#mod-gallery-image-perview-a-'+imageId).show();
    if(M.mod_gallery.Y.one('#mod-gallery-image-desc-' + imageId).get('innerHTML') !== '' ||
        M.mod_gallery.Y.one('#mod-gallery-image-name-' + imageId).get('innerHTML') !== '' ||
        M.mod_gallery.Y.one('#mod-gallery-image-source-' + imageId).get('innerHTML') !== '') {
        M.mod_gallery.Y.one('#mod-gallery-image-desc').setHTML(M.mod_gallery.Y.one('#mod-gallery-image-desc-' + imageId).get('innerHTML'));
        M.mod_gallery.Y.one('#mod-gallery-image-name').setHTML(M.mod_gallery.Y.one('#mod-gallery-image-name-' + imageId).get('innerHTML'));
        M.mod_gallery.Y.one('#mod-gallery-image-source').setHTML(M.mod_gallery.Y.one('#mod-gallery-image-source-' + imageId).get('innerHTML'));
    } else 
        M.mod_gallery.send_request(imageId, 'description');

    M.mod_gallery.Y.one('#mod-gallery-image-comments-'+M.mod_gallery.currentImage).hide();
    M.mod_gallery.Y.one('#mod-gallery-image-comments-'+imageId).show();
    
    M.mod_gallery.currentImage = imageId;
    
    if(M.mod_gallery.Y.one('#mod-gallery-thumb-'+M.mod_gallery.currentImage).next('a')) 
        M.mod_gallery.Y.one('#mod-gallery-image-next').show();
    else
        M.mod_gallery.Y.one('#mod-gallery-image-next').hide();
    
    if(M.mod_gallery.Y.one('#mod-gallery-thumb-'+M.mod_gallery.currentImage).previous('a')) 
        M.mod_gallery.Y.one('#mod-gallery-image-previous').show();
    else
        M.mod_gallery.Y.one('#mod-gallery-image-previous').hide();

    var links = M.mod_gallery.Y.all('#mod-gallery-navigation-buttons a');    
    links.each(function (linkNode) {
        var test = linkNode.getAttribute('href').replace('&image=(.*)$','&image=' + M.mod_gallery.currentImage);
        linkNode.setAttribute('href',test);
    });
};

function showImage(imageId) {
    M.mod_gallery.showImage(imageId);
    return false;
};

function showImageNext() {
    var next = M.mod_gallery.Y.one('#mod-gallery-thumb-'+M.mod_gallery.currentImage).next('a');
    var imageId = next.getData('id');
    M.mod_gallery.showImage(imageId);
    return false;
}

function showImagePrev() {
    var previous = M.mod_gallery.Y.one('#mod-gallery-thumb-'+M.mod_gallery.currentImage).previous('a');
    var imageId = previous.getData('id');
    M.mod_gallery.showImage(imageId);
    return false;
}

M.mod_gallery.send_request = function(imageId) {
    this.api = M.cfg.wwwroot+'/mod/gallery/ajax.php?sesskey='+M.cfg.sesskey;
    M.mod_gallery.Y.io(this.api,{
        method : 'POST',
        data :  build_querystring({
            image : imageId,
            ctx : M.mod_gallery.context,
            action : 'display'
        }),
        on : {
            success : function(tid, outcome) {
                YUI().use('json-parse', 'node', function (Y) {
                    var data = Y.JSON.parse(outcome.responseText);
                    M.mod_gallery.Y.one('#mod-gallery-image-desc-' + imageId).setHTML(data.description);
                    M.mod_gallery.Y.one('#mod-gallery-image-desc').setHTML(M.mod_gallery.Y.one('#mod-gallery-image-desc-' + imageId).get('innerHTML'));
                    M.mod_gallery.Y.one('#mod-gallery-image-source-' + imageId).setHTML(data.source);
                    M.mod_gallery.Y.one('#mod-gallery-image-source').setHTML(M.mod_gallery.Y.one('#mod-gallery-image-source-' + imageId).get('innerHTML'));
                    M.mod_gallery.Y.one('#mod-gallery-image-name-' + imageId).setHTML(data.name);
                    M.mod_gallery.Y.one('#mod-gallery-image-name').setHTML(M.mod_gallery.Y.one('#mod-gallery-image-name-' + imageId).get('innerHTML'));
                });
            }
        },
        context : this
    });
};