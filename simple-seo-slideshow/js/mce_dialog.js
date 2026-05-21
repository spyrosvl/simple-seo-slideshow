var ssSlideDialog = {
	init : function(ed) {
		var dom = ed.dom, f = document.forms[0], n = ed.selection.getNode(), sctitle, scdelay, scheight, scdisplaybul, scdisplayarr, scrandomize, sclinkwhat, scdisplaycap, scbulpos, sccappos, scexclude;

		sctitle = dom.getAttrib(n, 'sctitle');
		scdelay = parseInt(dom.getAttrib(n, 'scdelay'));
		scheight = parseInt(dom.getAttrib(n, 'scheight'));
		scdisplaybul = dom.getAttrib(n, 'scdisplaybul');
		scdisplayarr = dom.getAttrib(n, 'scdisplayarr');
		scrandomize = dom.getAttrib(n, 'scrandomize');
		sclinkwhat = dom.getAttrib(n, 'sclinkwhat');
		scdisplaycap = dom.getAttrib(n, 'scdisplaycap');
		scbulpos = dom.getAttrib(n, 'scbulpos');
		sccappos = dom.getAttrib(n, 'sccappos');
		scexclude = dom.getAttrib(n, 'scexclude');
	},

	update : function() {
		var ed = tinyMCEPopup.editor, sc, f = document.forms[0];

		sc = '[simpleslideshow';

		if (f.sctitle.value) { sc += ' sctitle="' + f.sctitle.value + '"'; }
		if (f.scdelay.value) { sc += ' scdelay=' + f.scdelay.value;	} else { sc += ' scdelay=5'; }
    if (f.scheight.value) { sc += ' scheight=' + f.scheight.value; }
    if (f.scdisplaybul.value) { sc += ' scdisplaybul=' + f.scdisplaybul.value; }
    if (f.scdisplayarr.value) { sc += ' scdisplayarr=' + f.scdisplayarr.value; }
    if (f.scrandomize.value) { sc += ' scrandomize=' + f.scrandomize.value; }
    if (f.sclinkwhat.value) { sc += ' sclinkwhat=' + f.sclinkwhat.value; }
    if (f.scdisplaycap.value) { sc += ' scdisplaycap=' + f.scdisplaycap.value; }
    if (f.scbulpos.value) { sc += ' scbulpos=' + f.scbulpos.value; }
    if (f.sccappos.value) { sc += ' sccappos=' + f.sccappos.value; }
    if (f.scexclude.value) { sc += ' scexclude=' + f.scexclude.value; }

		sc += ']';

		ed.execCommand("mceInsertContent", false, sc);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.requireLangPack();
tinyMCEPopup.onInit.add(ssSlideDialog.init, ssSlideDialog);
