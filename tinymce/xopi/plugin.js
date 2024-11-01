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

tinymce.PluginManager.add("xopi", function(a, url) {
    function b() {

		// get the post id
		var postId = tinyMCE.activeEditor.id.replace(/.*_(.*)/, "$1");

		var content = tinymce.trim(a.getContent({format: "raw"}));

		// save the content
		ajaxRequest(postId, 'content', content, null);

		// copy the content to the preview
		jQuery("#xopi_content_"+postId).html(content);

		// let the user know that it's done
		jQuery("#xopi_message_"+postId).text("Content Saved").show().fadeOut(3000);

		return;
    }

    function c() {
        var b = tinymce.trim(a.startContent);
        return a.getParam("save_oncancelcallback") ? void a.execCallback("save_oncancelcallback", a) : (a.setContent(b), a.undoManager.clear(), void a.nodeChanged())
    }

    function d() {
        var b = this;
        a.on("nodeChange", function() {
            b.disabled(a.getParam("save_enablewhendirty", !0) && !a.isDirty())
        })
    }

    function e() {
		window.open('http://wildfireweb.com/xopi-for-wordpress.html');
    }


    a.addCommand("xopiSave", b), 
	a.addCommand("xopiCancel", c), 
	a.addCommand("goXopi", e), 
	a.addButton("save", {
        icon: "save",
        text: "",
		title: "Save",
        cmd: "xopiSave",
        disabled: !0,
        onPostRender: d
    }), 
	a.addButton("cancel", {
        icon: "cancel",
        text: "",
		title: "Cancel",
        cmd: "xopiCancel",
        disabled: !0,
        onPostRender: d
    }), 
	a.addButton("xopi", {
        icon: "xopi",
        text: "",
		title: "XOpi Help",
		image: xopi_data.xopi_dir+"/images/xopi_logo.png",
        cmd: "goXopi"
    }), 
	a.addShortcut("Meta+S", "", "xopiSave")
});