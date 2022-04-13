/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */
 
 /*
 
 extraPlugins: 'uploadimage,image2',
			height: 300,

			
			// Upload images to a CKFinder connector (note that the response type is set to JSON).
			uploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files&responseType=json',

			// Configure your file manager integration. This example uses CKFinder 3 for PHP.
			filebrowserBrowseUrl: '/ckfinder/ckfinder.html',
			filebrowserImageBrowseUrl: '/ckfinder/ckfinder.html?type=Images',
			filebrowserUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
			filebrowserImageUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',

			// The following options are not necessary and are used here for presentation purposes only.
			// They configure the Styles drop-down list and widgets to use classes.

			stylesSet: [
				{ name: 'Narrow image', type: 'widget', widget: 'image', attributes: { 'class': 'image-narrow' } },
				{ name: 'Wide image', type: 'widget', widget: 'image', attributes: { 'class': 'image-wide' } }
			],

			// Load the default contents.css file plus customizations for this sample.
			contentsCss: [ CKEDITOR.basePath + 'contents.css', 'http://sdk.ckeditor.com/samples/assets/css/widgetstyles.css' ],

			// Configure the Enhanced Image plugin to use classes instead of styles and to disable the
			// resizer (because image size is controlled by widget styles or the image takes maximum
			// 100% of the editor width).
			image2_alignClasses: [ 'image-align-left', 'image-align-center', 'image-align-right' ],
			image2_disableResizer: true
 */
 
 
 
//CKEDITOR.config.extraPlugins = 'find,quicktable,tableresize';
CKEDITOR.editorConfig = function( config ) {
	config.extraPlugins ='uploadimage,image2,quicktable,tableresize';
	
	//config.height =  300;
/*
	config.qtRows: 20; // Count of rows
    config.qtColumns: 20; // Count of columns
    config.qtBorder: '1'; // Border of inserted table
    config.qtWidth: '90%'; // Width of inserted table
    config.qtStyle: { 'border-collapse' : 'collapse' };
    config.qtClass: 'test'; // Class of table
    config.qtCellPadding: '0'; // Cell padding table
    config.qtCellSpacing: '0'; // Cell spacing table
    config.qtPreviewBorder: '4px double black'; // preview table border 
    config.qtPreviewSize: '4px'; // Preview table cell size 
    config.qtPreviewBackground: '#c8def4'; // preview table background (hover)
*/
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		
		{ name: 'colors' },
		{ name: 'about' }
	];


	//config.removeButtons = 'Underline,Subscript,Superscript';  //Ver 
	


	config.format_tags = 'p;h1;h2;h3;pre';

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced';
	
	//config.uploadUrl =  '/intranet/aplic/pures/Sistema/js/ckeditor/kcfinder/upload.php?command=QuickUpload&type=file&dataType=json';
	//config.uploadUrl =  '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files&responseType=json';
	config.uploadUrl =  '/intranet/aplic/purso/index.php?pagina=paginas.upload';
	
	//config.skin = 'office2003';
	
	
	
	/*config.filebrowserBrowseUrl = '/intranet/aplic/pures/Sistema/js/ckeditor/kcfinder/browse.php';
    config.filebrowserImageBrowseUrl = '/intranet/aplic/pures/Sistema/js/ckeditor/kcfinder/browse.php?type=images';
    config.filebrowserFlashBrowseUrl = '/intranet/aplic/pures/Sistema/js/ckeditor/kcfinder/browse.php?type=flash';
    config.filebrowserUploadUrl = '/intranet/aplic/pures/Sistema/js/ckeditor/kcfinder/upload.php?type=files';
    config.filebrowserImageUploadUrl = '/intranet/aplic/pures/Sistema/js/ckeditor/kcfinder/upload.php?type=images';
    config.filebrowserFlashUploadUrl = '/intranet/aplic/pures/Sistema/js/ckeditor/kcfinder/upload.php?type=flash';*/
	config.toolbarCanCollapse = true;
	config.toolbarCanCollapse = true;
	config.toolbarStartupExpanded = true;
	config.toolbar = 'Basic';
	
	/*config.stylesSet = [
				{ name: 'Narrow image', type: 'widget', widget: 'image', attributes: { 'class': 'image-narrow' } },
				{ name: 'Wide image', type: 'widget', widget: 'image', attributes: { 'class': 'image-wide' } }
			];*/
	//config.contentsCss =  [ CKEDITOR.basePath + 'contents.css', 'http://sdk.ckeditor.com/samples/assets/css/widgetstyles.css' ];
	//config.image2_alignClasses =  [ 'image-align-left', 'image-align-center', 'image-align-right' ];
	
	config.image2_disableResizer = false;
	
	
	
};


