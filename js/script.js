/* Author: 

*/

$(document).ready(function(){
  var $input = $('.cmdln_input')
  var $shell = $('#shell')
  var $shellname = $('#shellname')
  var $output = $('#output')
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
    /*var charwidth = $shellname.width() / $shellname.text().length
    var width = ($input.val().length + 5) * charwidth;
    
    if (width < 20) width = 20;
    $input.width(width);*/
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
  
  var clearAndExec = function(cmd, xdata) {
    $form = $('<form>',{
      method: 'POST',
      action: '.'
    }).css('display', 'none')
    $('body').append($form)
    data = $.extend({
      'cmd': cmd,
      'cw':  consolewidth
    }, xdata)
    $.each(data, function (key, value) {
      $form.append($('<input>', {
        type: 'hidden',
        name: key,
        value: value
      }))
    })
    $form.submit();
  }
  
  var jscmd = function(output) {
    $output.append('<pre class="shell_cmd">' + $shellname.html() + $input.val() + '</pre>')
    printOutput(output)
  }
  
  var execCmd = function(cmd) {
    var args = cmd.split(/\s/g)
  
    $output.append('<pre class="shell_cmd">' + $shellname.html() + cmd + '</pre>')
  
    if (args[0] == 'edit') {     
      openTab('editor.php', {'file': args[1]})   
      resetShell()
      return ;
    }
    if (args[0] == 'clear') {    
      location.href = '.'
      return;
    }
    if (args[0] == '' || args.length == 0) {    
      //jscmd('')
      resetShell()
      return;
    }
  
    // Anfrage stellen
    $.ajax({
      type: "POST",
      url: "shell.php",
      data: {
        cmd: cmd,
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
  }
  
  var show_php_errors = function(output) {
    var errors = {}
    var noerrors = output
    var j = 0, k = 0
    var out
    
    for (var i = 0; i in output; i++) {
      out = output[i]
      if (out.c != 'o') {
        errors[j++] = out
      } else {
        delete noerrors[i]
        noerrors[k++] = out      
      }
    }
    
    if (j > 0) {
      $shell.before('<pre class="shell">' + $shellname.html() + $input.val() + '</pre>')
      printOutput(errors)
    }
    
    return noerrors;
  }
  
  var setInput = function(value) {
    //$input.val(value)
    //$input[0].size = Math.max(12,$input.val().length+1)
  }
  
  var printOutput = function(output) {
    var out;
    var e;
    $o = $('<div class="cmd_output"></div>')
    for (var i = 0; output[i] != undefined; i++) {
      out = output[i]
      $o.append($('<pre class="output_'+out.c+'" />').text(out.m))      
    }
    $o.appendTo($output)
  }
  
  var getConsoleWidth = function() {
    var charwidth = $shellname.width() / $shellname.text().length
    return Math.floor($shell.width() / charwidth)
  }  
  var consolewidth = getConsoleWidth()
  
  $input.focus()
    
  // Login notwendig?
  if ($('.login_bgd').css('display') != 'none') {    
    $('#user').focus();
    
    $('#pwd').keydown(function(event) {
      if (event.keyCode == '13') {
        event.preventDefault()    
        
        var a = $('.login').serializeArray()
        var o = {}
        for (var i = 0; i < a.length; i++) {
          o[a[i].name] = a[i].value
        }
        
        clearAndExec('login', o)
      }
    });
  }
  
  $('#main').click(function() {
    //$input.focus()
  })
 
  $input.keydown(function(event) {
    if (event.ctrlKey) {
      switch (event.keyCode) {
      case 68: /* Shift + d */
        event.preventDefault()
        execCmd('logout')
        return;
      }
    }
  
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
      
      execCmd(value)
      
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
          data = show_php_errors(data)
          
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
              return;
            }
          }        
          var value = $input.val()
          setInput(value.substring(0, value.lastIndexOf(' ')+1) + data.result)
          updateInputLength()
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
    
    updateInputLength()
    complete_candidates = null
  })
})

