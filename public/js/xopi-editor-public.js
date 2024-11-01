/*
    Copyright (C) 2015 WildFireWeb, Inc

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

	Please note that this license contains additional terms according to 
	section 7 of GPL Version 3. Before making any modifications or redistributing 
	this code please review the license additional terms.

    You should have received a copy of the GNU General Public License and the Additional Terms
    along with this program.  If not, see http://wildfireweb.com//xopi-gpl3-license.html

	This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

*/

var lineEditors = {};

var ajaxTarget = '';

// xopi_data variables is generated dynamically via wp_localize_script
function ajaxRequest(item_id, item_type, item_value, target) {
	ajaxTarget = target;
	jQuery.post(xopi_data.ajax_url, { 
		_ajax_nonce: xopi_data.nonce,
		action: "xopi",
		item_id: item_id,
		item_type: item_type,
		item_value: item_value
	}, function(data) {
		if (ajaxTarget) {
			ajaxTarget.innerHTML = data;
		}
	});
}

function removeElement(el) {
	var parent = el.parentNode;
	while (el.firstChild) {
		el.removeChild(el.firstChild);
	}
	parent.removeChild(el);
}

function makeSpan(id,classname) {
	// create a span node that we put all the editor elements into
	var obj = document.createElement('SPAN');
	obj.setAttribute('ID', id);
	obj.id = id;
	obj.setAttribute('CLASS', classname);
	obj.className = classname;

	return obj;
}

// adjust  iframe windows to fit text
function adjustIFrameSize(aID, extraheight) {
	var obj, height, height1, height2;

	if (extraheight == -1) {
		extraheight = 0;
	}
	else if (!extraheight) {
		extraheight=50;
	} 

	if (document.getElementById) {
		obj = document.getElementById(aID);

		 if (obj) {
		  if (obj.contentDocument){
			height1=obj.contentDocument.body.scrollHeight;
			height2=obj.contentDocument.documentElement.scrollHeight;

			height = (height1 > height2) ? height2 : height1;
			height += extraheight;

			obj.style.height = height + 'px';
			obj.height = height;
		  } else {
		  // IE
			height=document.frames[aID].document.body.scrollHeight+extraheight
			obj.style.height = height + 'px';
			obj.height = height;
		  }
		}
	}
}

function saveEdit(itemID)
{	
	var re;
	var i;
	var reload = true;
	var c;

	var editspan = document.getElementById('EDITORSPAN_'+itemID);

	if (!editspan)
		return;

	var mama = editspan.parentNode;

	mama.style.lineHeight = lineEditors[itemID].lineHeight;

	// retrieve our new text from the edit box
	var editHTML = document.getElementById('EDITOR_'+itemID).value;

	c = lineEditors[itemID].clickspan;

	// clear our wait message and put in spinning image
	c.innerHTML = editHTML;

	// clear out all children of editor
	while (editspan.firstChild) {
		editspan.removeChild(editspan.firstChild);
	}

	// put back new content
	mama.insertBefore(c,editspan);

	// get rid of editor and all its children
	mama.removeChild(editspan);

	var itemType = lineEditors[itemID].itemType;

	// send the data
	ajaxRequest(itemID, itemType, editHTML, null);

	attachXopi();

}

function cancelEdit(itemID)
{

	var editspan = document.getElementById('EDITORSPAN_'+itemID);
	if (!editspan)
		return;

	var mama = editspan.parentNode;

	mama.style.lineHeight = lineEditors[itemID].lineHeight;

	// clear out all children of editor
	while (editspan.firstChild) {
		editspan.removeChild(editspan.firstChild);
	}

	var c = lineEditors[itemID].clickspan;

	// put back original content
	c.innerHTML = lineEditors[itemID].origHTML;

	// put back editable span
	mama.insertBefore(c,editspan);
	
	mama.removeChild(editspan);

	attachXopi();

}

function lineEditor(itemID, itemType, origHTML, clicked, lineHeight) {
	this.itemID = itemID;
	this.itemType = itemType;
	this.origHTML = origHTML;
	this.clickspan = clicked;
	this.lineHeight = lineHeight;
}

function textEditor(obj, itemID, itemType) {
	var ed;

	var mama = obj.parentNode;

	if (!mama)
		return;

	var origHTML = obj.innerHTML;

	// save the empty edit span for putting back later
	var clickspan = obj.cloneNode(false);
	clickspan.innerHTML = "";

	lineEditors[itemID] = new lineEditor(itemID, itemType, origHTML, clickspan, mama.style.lineHeight);

	mama.style.lineHeight = '1.0';

	ed = document.createElement("INPUT");
	ed.setAttribute('TYPE','TEXT',0);
	ed.setAttribute('SIZE',64,0);
	ed.setAttribute('MAX_LENGTH',256,0);
	ed.setAttribute('ID','EDITOR_'+itemID,0);
	ed.value = origHTML;

	var targ = makeSpan('EDITORSPAN_'+itemID,'editorspan');
	targ.appendChild(ed);

	mama.insertBefore(targ,obj);
	removeElement(obj);

	// add save and cancel buttons
	jQuery('#EDITORSPAN_'+itemID).prepend('<span id="mceu_cancel" class="mce-widget mce-btn" title="Cancel"><button type="button" onclick="cancelEdit('+itemID+')"><i class="mce-ico mce-i-cancel"></i></button></span><span id="mceu_save" class="mce-widget mce-btn" title="Save"><button type="button" onclick="saveEdit('+itemID+')"><i class="mce-ico mce-i-save"></i></button></span>');

	ed.focus();
}

function catchIt(e)
{
	var sMatch, itemID;

	if (!document.getElementById || !document.createElement || e.button==2) return;

	var clicked;

	if (!e) clicked = window.event.srcElement;
	else clicked = e.target;

	if (!clicked.className.match(/xopi-editable/i)) {
		return;
	}

	// create the editor
	if (sMatch = clicked.id.match(/title_(\d*)/i)) {
		itemID = sMatch[1];
		textEditor(clicked, itemID, 'title');
	}
}

// autosize the editor frame
function autoSize(id) {
	adjustIFrameSize(id,100);
	setTimeout(function() {
		autoSize(id);
	},500);
}

function attachXopi() {
	jQuery(".xopi-editable").click(
		function(e) {
			catchIt(e);
		}
	);
}

var xopiActive = false;

function toggleXopi() {

	// cancel any open line editors
	for (var le in lineEditors) {
		if (lineEditors[le]) {
			cancelEdit(le);
		}
	}

	xopiActive = (xopiActive == true) ? false : true;
	if (xopiActive) {
		window.localStorage.setItem('xopistate', 'editmode');
		jQuery('#wp-admin-bar-xopi_editor a').text('Preview Post');
		jQuery(".xopi").addClass("xopi-editable");
		attachXopi();
	}
	else {
		window.localStorage.setItem('xopistate', 'previewmode');
		jQuery('#wp-admin-bar-xopi_editor a').text('Edit Post');
		jQuery(".xopi").removeClass("xopi-editable");
	}

	if (xopiActive) {
		jQuery(".xopi_content_preview").hide();
		jQuery(".xopi_content_edit").each(
			function() {
				jQuery(this).show();
				var iframe = jQuery('iframe',this).get(0);
				if (iframe)
					autoSize(iframe.id);
				jQuery(this).find('.mce-toolbar').last().addClass('xopi-row');
			}
		);
		// remove post title links
		jQuery(".xopi").each(
			function() {
				var parent = jQuery(this).parent().get(0);
				if (parent.tagName.toUpperCase() == 'A') {
					parent.setAttribute('hrsave', parent.href);
					parent.removeAttribute('href');
					// if parent of link is h1, h2, etc, then set lineHeight
					parent.parentNode.style.lineHeight = '1';
				}
			}
		);
	}
	else {
		jQuery(".xopi_content_edit").hide();
		jQuery(".xopi_content_preview").show();

		// restore title links
		jQuery(".xopi").each(
			function() {
				var parent = jQuery(this).parent().get(0);
				if (parent.tagName.toUpperCase() == 'A') {
					if (parent.hasAttribute('hrsave')) {
						parent.setAttribute('href', parent.getAttribute('hrsave'));
						parent.removeAttribute('hrsave');
					}
				}
			}
		);
	}

}

(function( $ ) {
	$( window ).load(function() {

		// in case wp_footer and wp_head don't exist in the them we need our own admin widget
		jQuery("body").append('<div id="xopi_toggle" style="display:none"><a href="#" onclick="toggleXopi(); return false;" class="xopi-toolbar"><span class="xopi-icon"></span><span class="xopi-name">XOpi<sup>&trade;</sup></span></a></div>');

		// see if admin bar is present and if not display our button at the top
		setTimeout(function() {
			if (!jQuery('#wpadminbar').length || !jQuery('#wpadminbar').is(":visible")) {
				jQuery('#xopi_toggle').show();

				$(window).scroll( function() {
					if ($(window).scrollTop() > $('body').offset().top)
						$('#xopi_toggle').addClass('floating');
					else
						$('#xopi_toggle').removeClass('floating');
				} );
			}
		}, 1000);

		if (window.localStorage.getItem('xopistate') == 'editmode') {
			toggleXopi();
		}

	});
})( jQuery );

