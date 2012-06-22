jQuery(document).ready(function($) {

        $('#addimageupload').click(function(e) {
                e.preventDefault();
                $btn = $(this);
         formfield = jQuery('#upload_image').attr('name');
         formID = $btn.attr('rel');
         tb_show('SendPress', 'media-upload.php?post_id='+formID+'&amp;is_sendpress=yes&amp;TB_iframe=true');
         return false;
        });
        window.send_to_editor = function(html) {
         imgurl = jQuery('img',html).attr('src');
         jQuery('#upload_image').val(imgurl);

         tb_remove();
        };

        $('#upload_image').change(function(){
              $('#html-header').html('<img src="'+ $this.val() +'" />');
        });


        
});
