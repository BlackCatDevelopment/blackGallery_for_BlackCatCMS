{translate('You can add files by drag & drop or by clicking on the [Add files] button.')}<br /><br />
<form id="fileupload" class="dropzone" enctype="multipart/form-data" method="POST" action="{$CAT_URL}/modules/blackGallery/ajax/upload.php">
    <input type="hidden" name="section_id" value="{$section_id}" />
    <div class="fileupload-buttonbar">
        <div class="fileupload-buttons">
            <div class="fallback">
                <input type="file" name="files[]" multiple="multiple" />
            </div>
            <button disabled="disabled" type="submit" class="btn icon icon-upload rounded">{translate('Start upload')}</button>
            <button disabled="disabled" type="reset" class="btn icon icon-cancel rounded">{translate('Reset')}</button>
        </div>
        <div id="progressbar">
            <strong>{translate('Overall progress')}:</strong><br />
            <div id="progress">
                <div class="bar" style="width: 0%;"></div>
            </div>
        </div>
    </div><br /><br />
    <div style="display:none;" id="set_global">
    <label for="global_category">{translate('Global category; you may override this per image below')}</label>
        <select id="global_category">
            {foreach $categories cat}<option value="{$cat.cat_id}">{$cat.cat_name}</option>{/foreach}
        </select>
    <button id="override">
        {translate('Reset all')}
    </button>
    </div>
</form>

<script charset=windows-1250 type="text/javascript">
    Dropzone.options.fileupload = {
        paramName             : "files"
        ,autoProcessQueue     : false
        ,maxFilesize          : {$max_file_size}
        ,addRemoveLinks       : true
        ,createImageThumbnails: true
        ,thumbnailWidth       : 80
        ,thumbnailHeight      : 80
        ,dictDefaultMessage   : '{translate("Drop files here or click to add")}'
        ,dictRemoveFile       : '{translate("Remove (no upload)")}'
        ,acceptedFiles        : 'image/*'
        ,init                 : function() {
            var myDropzone = this;
            this.element.querySelector("button[type=submit]").addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                myDropzone.processQueue();
            });
            this.on("addedfile", function() {
                $('button.icon-upload').prop('disabled', false);
                $('button.icon-cancel').prop('disabled', false);
                $('div#set_global').show();
                $('a.dz-remove').addClass('rounded');
            }).on("success", function(e,response) {
                $('.bar').text('{translate("Files successfully uploaded")}');
            }).on("error", function(error) {
alert('error: '+error);
            }).on("processing", function() {
                this.options.autoProcessQueue = true;
            }).on("sending", function(file) {
                $('#progressbar').show();
                $('.bar').text(file.name);
            }).on("complete", function(file) {
                myDropzone.removeFile(file);
            });

            document.querySelector("button.icon-cancel").addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                myDropzone.removeAllFiles();
                myDropzone.options.autoProcessQueue = false;
                $('button.icon-upload').prop('disabled','disabled');
                $('button.icon-cancel').prop('disabled','disabled');
                $('#progressbar').hide();
                $('.bar').text('').css('width','0');
            });

        }
        ,totaluploadprogress  : function(progress,totalBytes,totalSent) {
            console.log( 100 - ( 100 - ~~progress ));
            $('.bar').css('width', ( 100 - ( 100 - ~~progress ) )+'%');
        }
        ,previewTemplate      : '<div class="dz-preview dz-file-preview">' +
            '  <img data-dz-thumbnail />' +
            '  <div class="dz-details fc_gradient1">' +
            '    <div class="dz-filename"><span data-dz-name></span></div>' +
            '    <div class="dz-size" data-dz-size></div>' +
            '  </div>' +
            '  <div>{translate("Category")}: ' +
            '    <select name="cat_id">{foreach $categories cat}<option value="{$cat.cat_id}">{$cat.cat_name}</option>{/foreach}</select>' +
            '  </div>' +
            '  <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>' +
            '  <div class="dz-error-message"><span data-dz-errormessage></span></div>' +
            '</div>'
    };
    $('#override').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('select[name="cat_id"]').each( function() {
            $(this).val($('select#global_category').val());
        });
    });
</script>