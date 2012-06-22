jQuery(document).ready(function($) {

        $('#imageaddbox').hide();
        $('#textaddbox').hide();

        $('#addimage').click(function(e){
            e.preventDefault();
            $('#imageaddbox').show();
            $('#textaddbox').hide();
            // $('#header-controls').hide();
            // $('#header-text').hide();
            // $('#header-image').show();

        });

        $('#activate-image').click(function(e){
            e.preventDefault();
            var $img = $('#upload_image').val();
            if($img.length > 0){
                $('#active_header').attr('value','image');
                $('#post').submit();
            }else{
                $('#imageaddbox .error').show();
            }
        });

        $('#close-image').click(function(e){
            e.preventDefault();
            $('#imageaddbox').hide();
        });

        $('#activate-text').click(function(e){
            e.preventDefault();
            $('#active_header').attr('value','text');
            $('#post').submit();
        });

        $('#addtext').click(function(e){
            e.preventDefault();
            $('#textaddbox').show();
            $('#imageaddbox').hide();
            //$('#header-controls').hide();
            // $('#header-text').show();
            // $('#header-image').show();
        });

        $('#next-style').click(function(e){
            e.preventDefault();
            $('#save-type').val('save-style');
            $('#post').submit();

        });

        $('#save-edit-email').click(function(e){
            e.preventDefault();
            $('#save-action').val('save-edit');

            $('#post').submit();
        });

        $('#confirm-send').click(function(e){
           e.preventDefault();
           $('#post').submit();
        });

        $('#save-send-email').click(function(e){
            e.preventDefault();
            $('#save-action').val('save-send');

            $('#post').submit();
        });

        $('#save-style-email').click(function(e){
            e.preventDefault();
            $('#save-action').val('save-style');

            $('#post').submit();
        });


        $('#save-update,#save-text').click(function(e){
            e.preventDefault();
          
            $('#post').submit();

        });

        $('.view-btn').click(function(e) { 
            e.preventDefault();
        $v = $(this).attr('href')+'?TB_iframe=1';
        tb_show($(this).attr('title'), $v );
        return false;
       });

        $('#test').click(function(){
           console.debug(tinyMCE.activeEditor.getContent());
            $('#wp-content-wrap').toggle();
          tinyMCE.
           alert($c);

        });    

                //Build the Reset Button Actions
        $(".reset-line").click(function(e){
            var $reset = $(this);
            var id = $reset.attr("data-id");
        
            switch($reset.attr('data-type')){
                case "cp":
                    e.preventDefault();
                    var cp = $.farbtastic('#'+ id +'_colorpicker');
                    cp.setColor($('#default_'+ id ).val());
                break;
                case "border":
                    e.preventDefault();
                    var cp = $.farbtastic('#'+ id +'_colorpicker');
                    cp.setColor($('#default_'+ id + '_color').val());
                    $('#' + id +'_style').val($('#default_'+ id + '_style').val());
                    $('#' + id +'_width').val($('#default_'+ id + '_width').val());
                    //alert('reset border');
                break;
                
                case "image":
                    $('#'+ id +'_id').val("");
                    $('#'+ id +'_preview').toggle();
                    
                break;
                               
            }
            
        });


        //Build ColorPickers
        $('.cpcontroller').each(function(i){
            var $element = $(this);
            var id = $element.attr('data-id');
            var $holder = $('#pickholder_' + id);
            var $fb = $('#'+ id +'_colorpicker').farbtastic($element);
           // $.farbtastic('#'+ id +'_colorpicker').linkTo( cb  );
           $( $element.attr('link-id') ).css($element.attr('css-id') , $element.val() );
            $element.focus(function(){
                var p = $element.position();
                $holder.css('top',p.right+"px").css('left',p.left+"px").toggle('slow');
            })
            .change(function(){
                $item = $(this);
                $( $item.attr('link-id') ).css($item.attr('css-id') , $item.val() );
            })
            .blur(function(){
                var p = $element.position();
                $holder.css('top',p.right+"px").css('left',p.left+"px").toggle('slow');
            })
            .keyup(function(){
                var _hex = $element.val(), hex = _hex;
                if ( hex[0] != '#' ){
                    hex = '#' + hex;
                }
                hex = hex.replace(/[^#a-fA-F0-9]+/, '');
                if ( hex != _hex ){
                    $element.val(hex);
                }
                if ( hex.length == 4 || hex.length == 7 ){
                        var cp = $.farbtastic('#'+ id +'_colorpicker');
                        cp.setColor(hex);
                }
            });
            $holder.hide();
        });

        //list edit js
        $(".edit-list").click(function(e){
            e.preventDefault();

            $(".edit-list-form").each(function(){
                $(this).closest("tr").remove();
            });

            var $btn = $(this),
                $row = $btn.closest("tr"),
                listID = $btn.attr("listid"),
                name = $btn.attr('name'),
                pub = $btn.attr('public'),
                url = $btn.attr("href");

            jQuery.get(url+'?listid='+listID+'&name='+name+"&public="+pub, function(data) {
                $row.after(data);

                var $form = $(".edit-list-form");
                $form.animate({height:30},750);
            });
        });

        $("#cancel-edit-list").live('click',function(e){
            e.preventDefault();

            $(this).closest("tr").remove();
        });

        $('.edit-list-checkbox').live('click',function(){
            if( $(this).is(':checked') ){
                $(this).val(1);
            }else{
                $(this).val(0);
            }
        });

        $("#save-edit-list").live('click',function(e){
            e.preventDefault();

            var list = {},
                $form = $(this).parent('.edit-form');

            list['id'] = $form.find('#list-id').val();
            list['name'] = $form.find('#list-name').val();
            list['public'] = $form.find('#list-public').val();
            list['action'] = 'sendpress_save_list';

            //console.debug(list);

            jQuery.post(sendpress.ajaxurl, list, function(response){
                
                try {
                    response = JSON.parse(response);
                } catch (err) {
                    // Invalid JSON.
                    if(!jQuery.trim(response).length) {
                        response = { error: 'Server returned empty response during charge attempt'};
                    } else {
                        response = {error: 'Server returned invalid response:<br /><br />' + response};
                    }
                }

                if(response['success']){
                    location.reload();
                }else{
                    //possibly display an error here
                }
            });

        });

        
    $('#myModal').on('show',function(){
        $.get(sendpress.ajaxurl+'?action=sendpress-stopcron', function(data) {
             $.get(sendpress.ajaxurl+'?action=sendpress-sendcount', function(data) {
                var $qt = $("#queue-total");
                sendpress.queue = data;
                $qt.html(data);
                 sendpress.sendemail();
            });
        });
    }).on('hidden', function () {
        sendpress.sending = false;
        $('#sendbar-inner').css('width', '100%');
        location.reload();
        // do something…
    }).on('shown', function(){ 
        sendpress.count = 0;
         $('#sendbar-inner').css('width', '0%');
        sendpress.sending = true;
       
       
    });




     
});
sendpress.sending= false;
sendpress.count = 0;
sendpress.sendemail = function(){
    jQuery.get(sendpress.ajaxurl+'?action=sendpress-sendnow', function(data) {
        if(sendpress.sending == false){
                location.reload();
               }
               if(data != '0' && data != 'empty'){
                    sendpress.count = parseInt(sendpress.count) + parseInt(data);
                    var $qt =jQuery("#queue-sent");
                    $qt.html(sendpress.count);
                    $p = parseInt( sendpress.count/sendpress.queue * 100 );
                    jQuery('#sendbar-inner').css('width', $p+'%');
               }
               if(sendpress.sending == true && data != 'empty'){
                sendpress.sendemail();
               }
               if(data == 'empty'){
                 jQuery('#myModal').modal('hide');
               }


    });
       
}


var cb =   function (fb, color){
    alert(this.callback);
    // Set background/foreground color
      jQuery(fb.callback).css({
        backgroundColor: fb.color,
        color: fb.hsl[2] > 0.5 ? '#000' : '#fff'
      });

      /*Change linked value
      jQuery(fb.callback).each(function() {
        if (this.value && this.value != fb.color) {
          this.value = fb.color;
        }
      });
*/
}
