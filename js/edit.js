YUI().use('dd-drop', 'dd-proxy','dd-constrain','node', function(Y) {
    //Listen for all drop:over events
    var X = 0;
    Y.DD.DDM.on('drop:over', function(e) {
        var drop = e.drop.get('node');
        
        var isLeft = false;
        if(X < drop.getX()+80)
            isLeft = true;
            
        if(!isLeft)
            drop = drop.next();
        dropNode = Y.Node.one('#mod-gallery-drop-indicator');

        if(drop)
            drop.insert(dropNode,'before');
        else
            Y.Node.one('#mod-gallery-thumb-container').append(dropNode);
            
        dropNode.show();
    });
    
    //Listen for all drag:drag events
    Y.DD.DDM.on('drag:drag', function(e) {
        X = e.target.mouseXY[0];
    });
    
    //Listen for all drag:start events
    Y.DD.DDM.on('drag:start', function(e) {
        //Get our drag object
        var drag = e.target;
        //Set some styles here
        drag.get('node').setStyle('opacity', '.25');
        drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
    });
    //Listen for a drag:end events
    Y.DD.DDM.on('drag:end', function(e) {
        var drag = e.target;
        //Put our styles back
        drag.get('node').setStyles({
            visibility: '',
            opacity: '1'
        });
    });
    //Listen for all drag:drophit events
    Y.DD.DDM.on('drag:drophit', function(e) {
        var drag = e.drag.get('node');
        var dropIndicator = Y.Node.one('#mod-gallery-drop-indicator');
        dropIndicator.hide();
        dropIndicator.insert(drag,'before');
    });
    
    
    //Get the list of li's in the lists and make them draggable
    var lis = Y.Node.all('.mod-gallery-drag-thumb');
    lis.each(function(v, k) {
        var dd = new Y.DD.Drag({
            node: v
        }).plug(Y.Plugin.DDProxy, {
            moveOnEnd: false
        });
    });

    //Create simple targets for the 2 lists.
    var uls = Y.Node.all('.mod-gallery-thumb-edit');
    uls.each(function(v, k) {
        var tar = new Y.DD.Drop({
            node: v
        });
    });
});