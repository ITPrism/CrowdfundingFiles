jQuery(document).ready(function() {

    /** Files Manager **/

    var filesManager = {

        projectId: 0,

        $formToken: {},
        $uploaderLoader: {},
        $rowElement: {},
        $elementsList: {},
        $updaterLoader: {},
        $modal: {},
        token: {},
        fields: {},
        $elementsFormEditTitle: {},
        $elementsFormEditDescription: {},
        $elementsFormEditSection: {},
        $elementsFormEditFileId: {},
        $elementsFormEdit: {},

        init: function() {

            // Prepare the token as an object.
            this.$formToken = jQuery("#js-form-token");
            this.token[this.$formToken.attr('name')] = 1;

            // Get the loader.
            this.$uploaderLoader  = jQuery("#js-cffiles-ajax-loader");
            this.$updaterLoader   = jQuery("#js-cffiles-modal-loader");

            // Set project ID.
            this.projectId        = parseInt(jQuery("#js-form-item-id").val());
            if (!this.projectId) {
                this.projectId = 0;
            }

            // Prepare default form fields.
            this.fields   = jQuery.fn.extend({}, {project_id: this.projectId, format: 'raw'}, this.token);

            this.$rowElement   = jQuery("#js-cffiles-element");
            this.$elementsList = jQuery("#js-cffiles-list");

            this.$elementsFormEdit = jQuery("#js-cffiles-form-edit");
            this.$elementsFormEditTitle = jQuery("#js-cffiles-edit-form-title");
            this.$elementsFormEditDescription = jQuery("#js-cffiles-edit-form-description");
            this.$elementsFormEditSection = jQuery("#js-cffiles-edit-form-section");
            this.$elementsFormEditFileId = jQuery("#js-cffiles-form-edit-file-id");

            // Initialize the modal plugin.
            this.$modal   = jQuery("#js-cffiles-modal").remodal({
                hashTracking: false,
                closeOnConfirm: false,
                closeOnCancel: false,
                closeOnEscape: false,
                closeOnOutsideClick: false
            });

            this.initFileUploader();
            this.initButtonEdit();
            this.initButtonRemove();
            this.initButtonSubmit();
            this.initButtonCancel();
        },

        initFileUploader: function() {

            var $this = this;

            // Prepare fields.
            var fields = jQuery.fn.extend({}, {task: 'files.upload'}, $this.fields);

            // Add image
            jQuery('#js-cffiles-fileupload').fileupload({
                dataType: 'text json',
                formData: fields,
                singleFileUploads: true,
                send: function () {
                    $this.$uploaderLoader.show();
                },
                fail: function () {
                    $this.$uploaderLoader.hide();
                },
                done: function (event, response) {

                    if (response.result.success) {
                        var $element = $this.$rowElement.clone(false);

                        $element.attr("id", "js-cffiles-file" + response.result.data.id);
                        $element.children("td").eq(0).text(response.result.data.filename);
                        $element.children("td").eq(1).text(response.result.data.filename);
                        $element.children("td").eq(2).text(response.result.data.section);

                        $element.find("a.js-cffile-btn-download").attr("href", response.result.data.file).attr('type', response.result.data.mime);
                        jQuery($element).find("button.js-cffile-btn-remove").data("file-id", response.result.data.id);
                        jQuery($element).find("button.js-cffile-btn-edit").data("file-id", response.result.data.id);

                        $this.$elementsList.append($element);
                    } else {
                        PrismUIHelper.displayMessageFailure(response.result.title, response.result.text);
                    }

                    // Hide ajax loader.
                    $this.$uploaderLoader.hide();
                }
            });
        },

        initButtonCancel: function() {

            var $this = this;

            jQuery("#js-cffiles-edit-btn-cancel").on("click", function(event) {
                event.preventDefault();
                $this.$modal.close();
            });
        },

        initButtonEdit: function() {

            var $this = this;

            $this.$elementsList.on("click", ".js-cffile-btn-edit", function (event) {
                event.preventDefault();

                var fileId = parseInt(jQuery(this).data("file-id"));

                if (fileId > 0) {
                    var fields = jQuery.fn.extend({}, {task: 'files.edit', file_id: fileId}, $this.fields);

                    jQuery.ajax({
                        url: "index.php?option=com_crowdfundingfiles",
                        type: "GET",
                        data: fields,
                        dataType: "text json"
                    }).done(function (response) {
                        if (response.success) {

                            $this.$elementsFormEditTitle.val(response.data.title);
                            $this.$elementsFormEditDescription.val(response.data.description);
                            $this.$elementsFormEditSection.val(response.data.section);

                            $this.$elementsFormEditFileId.val(fileId);

                            $this.$modal.open();
                        } else {
                            PrismUIHelper.displayMessageFailure(response.title, response.text);
                        }
                    });
                }
            });
        },

        initButtonSubmit: function() {

            var $this = this;

            jQuery("#js-cffiles-edit-btn-submit").on("click", function (event) {
                event.preventDefault();

                var formFields = $this.$elementsFormEdit.serializeJSON();

                var fields = jQuery.fn.extend({}, {task: 'files.update'}, $this.fields, formFields);

                jQuery.ajax({
                    url: "index.php?option=com_crowdfundingfiles",
                    type: "POST",
                    data: fields,
                    dataType: "text json"
                }).done(function (response) {
                    if (response.success) {
                        if (response.success) {
                            var fileId = $this.$elementsFormEditFileId.val();
                            var $elementRow = jQuery("#js-cffiles-file" + fileId);

                            $elementRow.children("td").eq(0).text(formFields.title);
                            if (formFields.description) {
                                var description = jQuery('<p/>').text(formFields.description);
                                $elementRow.children("td").eq(0).append(description);
                            }

                            $elementRow.children("td").eq(2).text(formFields.section);
                        }

                        $this.$modal.close();
                    } else {
                        PrismUIHelper.displayMessageFailure(response.title, response.text);
                    }
                });
            });
        },

        initButtonRemove: function() {

            var $this = this;

            $this.$elementsList.on("click", ".js-cffile-btn-remove", function (event) {
                event.preventDefault();

                if (confirm(Joomla.JText._('PLG_CROWDFUNDING_FILES_DELETE_QUESTION'))) {

                    var fileId = parseInt(jQuery(this).data("file-id"));

                    if (fileId > 0) {
                        var fields = jQuery.fn.extend({}, {task: 'files.remove', file_id: fileId}, $this.fields);

                        jQuery.ajax({
                            url: "index.php?option=com_crowdfundingfiles",
                            type: "POST",
                            data: fields,
                            dataType: "text json"
                        }).done(function (response) {
                            if (response.success) {
                                jQuery("#js-cffiles-file" + response.data.file_id).remove();
                                PrismUIHelper.displayMessageSuccess(response.title, response.text);
                            } else {
                                PrismUIHelper.displayMessageFailure(response.title, response.text);
                            }
                        });
                    }
                }
            });
        }
    };

    // Initialize image tools object and its properties.
    filesManager.init();
});