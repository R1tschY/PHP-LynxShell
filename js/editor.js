/* Author: 

*/

function insertTextAtSelection(obj, text) {
  if(document.selection) {
    obj.focus();
    document.selection.createRange().text=strText;
    document.selection.createRange().select();
  } else if (obj.selectionStart || obj.selectionStart == '0') {
    intStart = obj.selectionStart;
    intEnd = obj.selectionEnd;
    obj.value = (obj.value).substring(0, intStart) + text + (obj.value).substring(intEnd, obj.value.length);
    obj.selectionStart=obj.selectionEnd=intStart+text.length;
    obj.focus();
  } else {
    obj.value += text;
  }
}

var editor = document.getElementById('edit').contentWindow.document
var $editor = $(editor)
$editor.ready(function(){
  editor.designMode = "on"
  
  $(editor.head).html('<link rel="stylesheet" href="css/editor.css">')

  $editor.keydown(function(event) {
    switch (event.keyCode) {
      case 9: // TAB
        event.preventDefault()         
        editor.execCommand('insertHTML', false, '  ')        
        break;
        
      /*case 13: // ENTER
        event.preventDefault()         
        editor.execCommand('insertHTML', false, '\r\n')        
        break;*/
        
      case 113: // F2 
        event.preventDefault()         
        alert(editor.body.innerHTML);        
        break;
    }
  })
})

$(document).ready(function(){
  var $log = $('#log')
  var filename = $('#edit').attr("file")
  
  $.ajax({
    type: "POST",
    url: "shell.php",
    data: {'cmd': 'cat ' + filename},
    success: function(data){
      if (data.status && data.status == 'NOT_AUTHORIZED') {
        // TODO
        return;
      }
      
      $(editor.body).html(data.stdout);
    }
  });

  $("#save").click(function(){
    $.ajax({
      type: "POST",
      url: "shell.php",
      data: {
        'cmd':   'save',
        'file':  filename,
        'input': editor.body.innerHTML.replace('/<br>/g', '\n')
      },
      success: function(data){
        if (data.status && data.status == 'NOT_AUTHORIZED') {
          $log.append(data.stdout + '\n')
          return;
        }
        
        $log.append(data.stdout + '\n')
      }
    });
  })
})

