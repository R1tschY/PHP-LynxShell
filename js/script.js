/*  PHP LynxShell
 *   - PHP command line shell 
 *  Copyright (C) 2012 Richard Liebscher
 *
 *  License: GNU General Public License Version 3
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
    $body.scrollTo('max')
  }
  
  // console width
  var getConsoleWidth = function() {
    var charwidth = $shellname.width() / $shellname.text().length
    return Math.floor($output.width() / charwidth - 5)
  }  
  
  var updateInputLength = function() {
    var charwidth = $shellname.width() / $shellname.text().length
    var width = ($input.val().length + 5) * charwidth;
    
    $input.width(Math.max(width, 300));
  }
  
  var flash = function() {
    $shell.hide().fadeIn('slow')
  }
  
  var openTab = function(url, data) {
    if (!data) {
      document.window.open(url, '_blank');
    } else {  
      var $form = $('<form>',{
        method: 'POST',
        action: url,
        target: '_blank'
      }).css('display', 'none')
      $body.append($form)
      
      $.each(data, function (key, value) {
        $form.append($('<input>', {
          type: 'hidden',
          name: key,
          value: value
        }))
      })
      $form.submit()
      $form.remove()
    }
  }
  
  var clearAndExec = function(cmd, xdata) {
    var $form = $('<form>',{
      method: 'POST',
      action: '.'
    }).css('display', 'none')
    $body.append($form)
    data = $.extend({
      'cmd': cmd,
      'cw':  getConsoleWidth()
    }, xdata)
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
  
  var last_cmd_id = 0;
  var last_cmdput;
  function CommandOutput(cmd) {
    this.cmd = cmd
    this.cmd_id = ++last_cmd_id
    last_cmdput = this

    var $div = $('<div class="cmd_wrapper" />')
    var $cmd = $('<pre class="shell_cmd">' + $shellname.html() + cmd + '</pre>')
    this.cmd_output = $('<pre class="cmd_output" id="cmd_' + this.cmd_id + '" />')
    var $loading = $('<img src="img/loader.gif" />').hide()
    $div.append($cmd)
    $div.append($loading)
    $div.append(this.cmd_output)
    $output.append($div)
    
    this.setLoading = function(val) {
      if (val) {
        $loading.show();
      } else {
        $loading.hide();
      }
    }
    
    this.setCommand = function(val) {
      $cmd.text($shellname.html() + val)
    }
    
    this.printOutput = function(output) {
      for (var i = 0; output[i] != undefined; i++) {
        this.cmd_output.append($('<span class="output_'+output[i].c+'" />').text(output[i].m))      
      }      
      $body.scrollTo('max')
    }

    this.print = function(msg, cmd_id) {
      this.printOutput({
        0:{'c':'o', 'm':msg} 
      })
    }
    
    this.printWarning = function(msg, cmd_id) {
      this.printOutput({
        0:{'c':'w', 'm':msg} 
      })
    }
    
    this.printError = function(msg, cmd_id) {
      this.printOutput({
        0:{'c':'e', 'm':msg} 
      })
    }
  }
  
  var jscmd = function(output) {
    var cmdput = new CommandOutput($input.val())
    cmdput.printOutput(output)
    return cmdput
  }
  
  var jscmds = {
    edit: function(filename) {
      openTab('editor.php', {'file': filename})   
      resetShell()
    },    
    
    clear: function() {
      location.href = '.'
    },
    
    download2: function(filename) {
      var $o = $('<div class="cmd_output"></div>').appendTo($output)
      var $progress = $('<pre class="output_o" />').appendTo($o).text('Download ist starting ...')

      var xhr = new XMLHttpRequest();      
      xhr.open("GET", "download.php?file=" + filename)
      xhr.responseType = "blob"
      xhr.addEventListener("progress", function() {
        if(evt.lengthComputable) {
          $('<pre class="output_o" />').appendTo($o).text('Progress: ' + evt.loaded + '/' + evt.total)
        }
      }, false);
      xhr.onload = function() {
        $('<pre class="output_o" />').appendTo($o).text('Finished Successful!')
        
        window.URL = window.URL || window.webkitURL;
        var $form = $('<form>', {
          method : 'GET',
          target: '_blank',
          action :  window.URL.createObjectURL(xhr.response),
        }).css('display', 'none')
        $body.append($form)
        $form.submit()
        $form.remove()
      };
      xhr.send()
    },
    
    download: function(filename) {      
      var $form = $('<form>', {
        method : 'POST',
        action : 'download.php'
      }).css('display', 'none')
      $form.append($('<input>', {
        type : 'hidden',
        name : 'file',
        value : filename
      }))
      $body.append($form)
      $form.submit()
      $form.remove()
    },
    
    view: function(filename) {
      var cmdput = last_cmdput
      var tab_open = window.open
      
      sendCmd($input.val(), {
        intern: false,
        async: false,
        success: function(data) {
          cmdput.setLoading(false);          
          if (data.status == 'NOT_AUTHORIZED') {
            location.href = '.'            
            return
          }
          $shellname.html(data.shell)
          
          if (data.status == 'NO_ERROR') {
            tab_open(data.result, '_blank')
          }
          cmdput.printOutput(data)
        },
        error: function(error_msg) {
          cmdput.setLoading(false);
          cmdput.print(error_msg)
        }
      })
    }

  }
  
  var sendCmd = function(cmd, options) {
    var defaults = {
      intern: true,
      async: true,
      data: {},
      success: function(data){},
      error: function(msg){}
    }
    var opts = $.extend(true, {}, defaults, options);
    
    var default_data = {
      cmd: cmd,
      cw:  getConsoleWidth(), // ClientWidth/ConsoleWidth
      'i': opts.intern
    }
    var data = $.extend(true, {}, default_data, opts.data);
  
    // Anfrage stellen
    $.ajax({
      type: "POST",
      url: "shell.php",
      data: data,
      async: opts.async,
      success: function(data) {        
        if (!data.status) { // JSON Data?
          opts.error('php error: ' + data)
        } else {
          opts.success(data)
        }        
        $body.scrollTo('max')
      },
      error: function(jqXHR, textStatus, errorThrown) {
        if (textStatus != null) {
          var errormsg;
          if (textStatus == 'parsererror') {
            errormsg = 'php error: ' + jqXHR.responseText
          } else {
            errormsg = 'ajax error: ' + jqXHR.statusText
          }
          opts.error(errormsg)
          $body.scrollTo('max')
        }        
      }        
    })
  }
  
  var execCmd = function(cmd) {
    var args = cmd.split(/\s/g)  
    var cmdput = new CommandOutput(cmd)
  
    if (args[0] == '' || args.length == 0) {    
      return;
    }
    
    cmdput.setLoading(true);
    
    if ((method = jscmds[args[0]])) {
      method.apply(this, args.slice(1));
      cmdput.setLoading(false);
      return ;
    }
    
    sendCmd(cmd, {
      intern: false,
      success: function(data) {
        cmdput.setLoading(false);
        cmdput.printOutput(data)          
        
        if (data.status == 'NOT_AUTHORIZED') {
          location.href = '.'            
          return ;
        }
      
        $shellname.html(data.shell)
      },
      error: function(error_msg) {
        cmdput.setLoading(false);
        cmdput.print(error_msg)
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
      var cmdput = new CommandOutput($input.val())
      cmdput.printOutput(errors)
    }
    
    return noerrors;
  }

  //////////////////////////////////////////////////////////////////////////////
  
  updateInputLength()
  $input.focus()
    
  // Login
  if ($('.login_bgd').css('display') != 'none') {    
    $('#user').focus()
    
    $('#pwd').keydown(function(event) {
      if (event.keyCode == '13') {
        event.preventDefault()    
        
        var a = $('.login').serializeArray()
        var o = {}
        for (var i = 0; i < a.length; i++) {
          o[a[i].name] = a[i].value
        }
        
        sendCmd('login', {
          intern: true,
          data: o,
          success: function(data) {
            var $div = $('<div class="cmd_wrapper" />')
            var $cmd_output = $('<pre class="cmd_output" id="cmd_login" />')
            $div.append(this.cmd_output).appendTo($output)
          
            for (var i = 0; data[i] != undefined; i++) {
              $cmd_output.append($('<span class="output_'+data[i].c+'" />').text(data[i].m))      
            }      
            $body.scrollTo('max')          
            
            if (data.status == 'NOT_AUTHORIZED') {
              location.href = '.'            
              return
            }
          
            $shellname.html(data.shell)
            $('.login_bgd').hide()
            resetShell()
            $input.focus()
          },
          error: function(error_msg) {
            alert(error_msg) // TODO
          }
        })
      }
    });
  }
  
  // image preloading
  var images = [
    'img/loader.gif'
  ];
  $(images).each(function() {
    var image = $('<img />').attr('src', this);
  });
  
  // 
  $('#main').click(function() {
    //$input.focus()
  })
  
  // keyboard events
  $input.mousedown(function(event) {
    if (event.button == 1) {
      event.preventDefault()
      
      var selection = $body.selection()
      var text = $body.text().substring(selection.start, selection.end);      
      $input.val($input.val() + text)
      updateInputLength()
    }
  })
 
  // keyboard events
  $input.keydown(function(event) {
    if (event.ctrlKey) {
      switch (event.keyCode) {
      case 65: /* Shift + a */
        event.preventDefault()
        $input.selection(0, 0)
        return
        
      case 66: /* Shift + b */ {
        event.preventDefault()
        var selection = $input.selection()
        var start = selection.start
        var end   = selection.end
        if (start > 0) --start
        if (end > 0) --end
        $input.selection(start, end)
        return
      }
        
      case 68: /* Shift + d */
        event.preventDefault()
        execCmd('logout')
        return
      
      case 67: /* Shift + e */ {
        event.preventDefault()
        var len = $input.val().length-1
        $input.selection(len, len)
        return
      }
      
      case 70: /* Shift + f */ {
        event.preventDefault()
        var selection = $input.selection()
        var start = selection.start
        var end   = selection.end
        var len = $input.val().length-1
        if (start < len) ++start
        if (end < len) ++end
        $input.selection(start, end)
        return
      }
      
      case 75: /* Shift + k */ {
        event.preventDefault()
        var selection = $input.selection()
        $input.val($input.val().substring(0, selection.start))
        updateInputLength()
        return
      }
      
      case 76: /* Shift + l */
        event.preventDefault()
        execCmd('clear')
        return
      
      case 85: /* Shift + u */ {
        event.preventDefault()
        var selection = $input.selection()
        var len = $input.val().length
        $input.val($input.val().substring(selection.start, len))
        $input.selection(0, 0)
        updateInputLength()
        return
      }
      }
    }
  
    switch (event.keyCode) {
    case 13: /* ENTER */
      event.preventDefault()
      
      var value = $input.val()
      
      // Verlauf
      if (history[history.length-1] != value) {
        history.push(value)
      }
      history_index = -1
      currentcmd = ''     
      
      execCmd(value)
      resetShell()      
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
      
      if (complete_candidates != null) {
        var cmdput = new CommandOutput($input.val())
        cmdput.printOutput(complete_candidates)
        return
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
      
      sendCmd(cmd, {
        intern: true,
        success: function(data) {
          data = show_php_errors(data)
          
          if (data.status) {
            if (data.status == 'NOT_AUTHORIZED') {
              location.href = '.'
              return
            } else if (data.status == 'NOT_FOUND') {
              flash()
              return
            } else if (data.status == 'MORE_FOUND') {
              complete_candidates = data
              flash()
              if (!data.result) return;
            }
          }   
          var value = $input.val()     
          $input.val(value.substring(0, value.lastIndexOf(' ')+1) + data.result)
          $input[0].selectionStart = $input[0].selectionEnd = $input.val().length
          updateInputLength()
        },
        error: function(msg) {
          jscmd(msg)
        } 
      });      
      break;      
    }
    
    updateInputLength()
    complete_candidates = null
  })
  
  var base = 1024;
  var getReadableSpeedString = function(speedInKBytesPerSec) {
	  var speed = speedInKBytesPerSec
	  speed = Math.round(speed * 10) / 10
	  if (speed < base) {
		  return speed + "kiB/s"
	  }

	  speed /= base
	  speed = Math.round(speed * 10) / 10
	  if (speed < base) {
		  return speed + "MiB/s"
	  }

	  return speedInBytesPerSec + "B/s"				
  }

  var getReadableFileSizeString = function(fileSizeInBytes) {
	  var fileSize = fileSizeInBytes;
	  if (fileSize < base) {
		  return fileSize + "B"
	  }

	  fileSize /= base
	  fileSize = Math.round(fileSize)
	  if (fileSize < base) {
		  return fileSize + "kiB"
	  }
	
	  fileSize /= base;
	  fileSize = Math.round(fileSize * 10) / 10
	  if (fileSize < base) {
		  return fileSize + "MiB"
	  }

	  return fileSizeInBytes + "B"
  }

  var getReadableDurationString = function(duration) {
	  var elapsed = duration

	  var minutes, seconds

	  seconds = Math.floor(elapsed / 1000)
	  minutes = Math.floor((seconds / 60))
	  seconds = seconds - (minutes * 60)

	  var str = ""
	  if (minutes>0)
		  str += minutes + "m"

	  str += seconds + "s"
	  return str
  }  
  
  $shellname.dropzone({
    url : "upload.php",
    responseType: "text",
    printLogs : false,
    uploadRateRefreshTime : 500,
    numConcurrentUploads : 2,
    //responseType: "json",
    events : {      
      UploadStarted : function(fileIndex, file) {
        file.progress.text(file.name + ': upload started 0% / ' + getReadableFileSizeString(file.size) + "\n")
     
       /*var percDiv = $("<div></div>").css({
        'background-color': 'orange',
        'width': '0%',
        'height': '20px'
        }).attr("id", "perc" + fileIndex).wrap(processTd); 

       var processTd = $("<td></td>").attr("class", "process").append(percDiv); 
        */
      },
      
      UploadFinished : function(fileIndex, file, duration) {
        //$("#dropzone-info" + fileIndex).html("upload finished: " + file.fileName + " ("+getReadableFileSizeString(file.fileSize)+") in " + (getReadableDurationString(duration)));
        /*$("#perc" + fileIndex).css({
        'width': '100%',
        'background-color': 'green'
        });*/
        file.progress.text(file.name + ': upload finished' + "\n")
      },
      
      UploadProgress : function(fileIndex, file, newProgress, newSpeed) {
        file.progress.text(file.name + ': progress '+ Math.round(newProgress * 100) + '% / ' + getReadableFileSizeString(file.size) + "\n")
        
        //$("#perc" + fileIndex).css("width", Math.round(newProgress * 100) + "%");
        //$("#dropzone-speed" + fileIndex).html(getReadableSpeedString(newSpeed))
      },
      
      FilesDropped : function(files) {
        var cmdput = new CommandOutput('upload')
        var cmd = 'upload'
        for (var i = 0; i < files.length; i++) {
          files[i].command = cmdput
          
          files[i].progress = $('<span class="output_o" />').appendTo(cmdput.cmd_output)
          files[i].progress.text(files[i].name + ': upload queued\n')
          
          cmd += ' ' + files[i].name
        }
        cmdput.setCommand(cmd)
      
        cmdput.setLoading(true)
        $(this).dropzone('upload')
      },
      
      Response : function(fileIndex, file, response) {
        file.command.setLoading(false)
        file.progress.text(file.name + ': request finished 100% / ' + getReadableFileSizeString(file.size) + "\n")
      },
      
      ResponseError : function(fileIndex, file, xhr, text_status) {
        file.command.setLoading(false)
        file.progress.text(file.name + ': request failed' + "\n")
        // for Chrome: parse JSON later
        file.command.printOutput(JSON.parse(response))
      },
    }     
  })
})

