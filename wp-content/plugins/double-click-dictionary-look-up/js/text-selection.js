(function($){
$('body').prepend('<p id="dblclick_qTip">'+dblclick_params.text+'</p>');
$('.dblclick_enableLookUp').dblclick( dblclick_create_qTip );
$(document).click( function(){ $('#dblclick_qTip').css({'visibility':'hidden'}).unbind(); });

$(window).scroll(function(){ $('#dblclick_qTip').css({'visibility':'hidden'}).unbind(); });
function getSelected() {
  if(window.getSelection) { return window.getSelection(); }
  else if(document.getSelection) { return document.getSelection(); }
  else {
    var selection = document.selection && document.selection.createRange();
    if(selection.text) { return selection.text; }
    return false;
  }
  return false;
}

function dblclick_create_qTip(e) {
	sel = getSelected();
	if (sel){
		word = sel.toString();
		word = word.replace(/[^\w]|_/g, "");
		if ( word != "" ){
			//IE fix 
			if (typeof(sel) == 'string'){
				range = document.selection.createRange();
				moved=range.moveStart('character', word.length-1);
				$('#dblclick_qTip').css({'visibility':'visible','left':range.offsetLeft,'top':range.offsetTop - 15 });
				$('#dblclick_qTip').click( function(){ window.open('http://www.merriam-webster.com/dictionary/'+word, "_blank", "menubar=no,resizable=no,width=675,height=700");} );
			}
			else{
				range = sel.getRangeAt(0);
				endoffset = range.endOffset;
				startoffset = range.startOffset;
				startcontainer = range.startContainer;
				endcontainer = range.endContainer;
				parentnode = endcontainer.parentNode;
				for (i=0;i<parentnode.childNodes.length;i++){
					if(parentnode.childNodes[i] == endcontainer){
						pos = i;
					}
				}
				if ( endcontainer != startcontainer ){
					for (i=0;i<parentnode.childNodes.length;i++){
						if(parentnode.childNodes[i] == startcontainer){
							pos2 = i;
						}
					}
				}
				else{
					pos2 = pos;
				}
				
				//Create Marker
				replacementNode = endcontainer.splitText(endoffset);
				var span = document.createElement('span');
				span.setAttribute('id','dblclick_marker');
				parentnode.insertBefore(span, replacementNode);
				marked = parentnode.innerHTML;
				
				//Add the qtip
				offset = $('#dblclick_marker').offset();
				$('#dblclick_qTip').css({'visibility':'visible','left':offset.left - 10,'top':offset.top - window.pageYOffset - 15 });
				$('#dblclick_qTip').click( function(){ window.open('http://www.merriam-webster.com/dictionary/'+word, "_blank", "menubar=no,resizable=no,width=675,height=700");} );

				//Revert to orginial
				original = marked.replace('<span id="dblclick_marker"></span>','');
				parentnode.innerHTML=original;
				
				//Reselect the text
				textnodeEnd = parentnode.childNodes[pos];
				textnodeStart = parentnode.childNodes[pos2];
				range = document.createRange();
				range.setStart(textnodeStart,startoffset);
				range.setEnd(textnodeEnd,endoffset);
				sel.removeAllRanges();
				sel.addRange(range);
			}
		}
	}
}

})(jQuery);
/* OLD CODE:

//.contents().filter(function() { return this.nodeType == Node.TEXT_NODE; })[pos];
// html = el.html();
// marked = html.substring(0,endoffset)+"<span id='dblclick_marker'></span>"+html.substring(endoffset,html.length);
// el.html(marked);

*/