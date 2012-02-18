/* Author: 

*/

$(document).ready(function(){
  var $input = $('#input')
  var $shell = $('#shell')
  var $shellname = $('#shellname')
  var $login = $('#login');
  var $body  = $("body");
  var history = new Array()
  var history_index = -1
  var currentcmd = ''
  var complete_candidates = null
  
  var resetShell = function() {
    $input.val('')
    $shell.show()
    $input.focus()
    $('body').scrollTo('max')
  }
  
  var updateInputLength = function() {
    var charwidth = $shellname.width() / $shellname.text().length
    var width = ($input.val().length + 5) * charwidth;
    
    if (width < 20) width = 20;
    $input.width(width);
  }
  
  var flash = function() {
    $shell.hide().fadeIn('slow')
  }
  
  var openTab = function(url, data) {
    $form = $('<form>',{
      method: 'POST',
      action: url,
      target: '_blank'
    }).css('display', 'none')
    $('body').append($form)
    $.each(data, function (key, value) {
      $form.append($('<input>', {
        type: 'hidden',
        name: key,
        value: value
      }))
    })
    $form.submit();
    $form.remove();
  }
  
  var jscmd = function(output) {
    $shell.before('<pre class="shell">' + $shellname.html() + $input.val() + '</pre>')
    printOutput(output)
  }
  
  var show_php_errors = function(output) {
    var out
    var msgs = false
    for (var i = 0; output[i] != undefined; i++) {
      out = output[i]
      if (out.c != 'o') {
        $shell.before('<pre class="channel_'+out.c+'">'+out.m+'</pre>') 
        msgs = true       
      } 
    }
    
    if (msgs) {
      $shell.before('<pre class="shell">' + $shellname.html() + $input.val() + '</pre>')
      printOutput(output)
    }
  }
  
  var setInput = function(value) {
    $input.val(value)
    $input[0].size = Math.max(12,$input.val().length+1)
  }
  
  var printOutput = function(output) {
    var out;
    var e;
    for (var i = 0; output[i] != undefined; i++) {
      out = output[i]
      $shell.before($('<pre class="channel_'+out.c+'" />').text(out.m))      
    }
  }
  
  var getConsoleWidth = function() {
    var charwidth = $shellname.width() / $shellname.text().length
    return Math.floor($shell.width() / charwidth)
  }  
  var consolewidth = getConsoleWidth()
    
  // Login notwendig
  if ($login.length > 0) {    
    $('#user').focus().keydown(function(event) {
      if (event.keyCode == '13') {
        event.preventDefault()    
        $('#pwdline').show()
        $('#pwd').focus();
      }
    })
    
    $('#pwd').keydown(function(event) {
      if (event.keyCode == '13') {
        event.preventDefault()    

        $.ajax({
          type: "POST",
          url: "shell.php",
          data: $login.serialize(),
          success: function(data){
            if (data.status && data.status == 'NOT_AUTHORIZED') {
              location.href = '.'
              return;
            }
          
            $shellname.html(data.shell)  

            printOutput(data);            
            
            resetShell()
          }
          
          //TODO: error
        });
      }
    });
  }
  
  $input.focus()
  
  $('#main').click(function() {
    //$input.focus()
  })
 
  $input.change(function(event) {
    $input[0].size = Math.max(12,$input.val().length+1)
    complete_candidates = null
  })
  
  $input.keydown(function(event) {
    switch (event.keyCode) {
    case 13: /* ENTER */
      event.preventDefault()
      
      var value = $input.val()
      consolewidth = getConsoleWidth()
      
      // Verlauf
      if (history[history.length-1] != value) {
        history.push(value)
      }
      history_index = -1
      currentcmd = ''     
      
      // letzten Befehl statisch setzen
      $shell.before('<pre class="shell">' + $shellname.html() + value + '</pre>')
      $shell.hide()
      
      // JS commands
      var args = value.split(" ")
      if (args[0] == 'edit') {     
        openTab('editor.php', {'file': args[1]})   
        resetShell()
        break ;
      }
      if (args[0] == 'clear') {    
        location.href = '.'
        break;
      }
      if (args[0] == '' || args.length == 0) {    
        //jscmd('')
        resetShell()
        break;
      }
     
      // Anfrage stellen
      $.ajax({
        type: "POST",
        url: "shell.php",
        data: {
          cmd: $input.val(),
          cw:  consolewidth, // ClientWidth/ConsoleWidth
          'i': false
        },
        success: function(data) {
          if (!data.status) { // JSON Data?
            data = {0:{
              c: 'o',
              m: 'php error: ' + data
            }}
          }
          
          printOutput(data)          
          
          if (data.status == 'NOT_AUTHORIZED') {
            location.href = '.'            
            return ;
          }
        
          $shellname.html(data.shell)
          
          resetShell()
        },
        error: function(jqXHR, textStatus, errorThrown) {
          if (textStatus != null) {
            var errormsg;
            if (textStatus == 'parsererror') {
              errormsg = 'php error: ' + jqXHR.responseText
            } else {
              errormsg = 'ajax error: ' + textStatus
            }
            printOutput({0:{
              c: 'o',
              m: errormsg
            }})
          }
          
          resetShell()
        }        
      })
      break;
      
    case 38: /* UP */
      event.preventDefault()
      
      if (history_index == -1) {
        currentcmd = $input.val()
        history_index = history.length 
      }
      
      if (history_index > 0) {
        $input.val(history[--history_index])
      }
      break;

    case 40: /* DOWN */
      event.preventDefault()
      
      if (history_index == -1) break;      
      if (history_index < history.length-1) {
        $input.val(history[++history_index])
      }      
      if (history_index == history.length-1) {
        $input.val(currentcmd)
        history_index = -1
      }
      break;   

    case 9: /* TAB */
      event.preventDefault()
      
      consolewidth = getConsoleWidth()      
      if (complete_candidates != null) {
        jscmd(complete_candidates)
        return ;
      }
      
      var args = $input.val().split(/\s/g)
      var cmd;
      if (args.length < 2) {
        if (args.length==0) args.push('')
        cmd = 'complete cmd ' + args[0]
      } else if (args[0] == 'cd') {
        cmd = 'complete dir ' + args[args.length-1]
      } else {
        cmd = 'complete file ' + args[args.length-1]
      }
      
      $.ajax({
        type: "POST",
        url: "shell.php",
        data: {
          'cmd': cmd,
          cw:    consolewidth,
          'i':   true
        },
        success: function(data) {
          show_php_errors(data)
          
          if (!data.result) {
            flash()
            return ;
          }
        
          if (data.status) {
            if (data.status == 'NOT_AUTHORIZED') {
              location.href = '.'
              return;
            } else if (data.status == 'NOT_FOUND') {
              flash()
              return ;
            } else if (data.status == 'MORE_FOUND') {
              complete_candidates = data
              flash()
            }
          }        
          var value = $input.val()
          setInput(value.substring(0, value.lastIndexOf(' ')+1) + data.result)
        },
        error: function(jqXHR, textStatus, errorThrown) {
          if (textStatus != null) {
            var errormsg;
            if (textStatus == 'parsererror') {
              errormsg = 'php error: ' + jqXHR.responseText
            } else {
              errormsg = 'ajax error: ' + textStatus
            }
            jscmd(errormsg)
          }
        } 
      });      
      break;      
    }
    
    updateInputLength();
  })
})

