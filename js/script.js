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
    $form.submit();
    $form.remove();
  }
  
  var clearAndExec = function(cmd, xdata) {
    var $form = $('<form>',{
      method: 'POST',
      action: '.'
    }).css('display', 'none')
    $body.append($form)
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
    $form.remove();
  }
  
  var jscmd = function(output) {
    $output.append('<pre class="shell_cmd">' + $shellname.html() + $input.val() + '</pre>')
    printOutput(output)
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
      xhr.open("GET", "download.php?file=" + filename);
      xhr.responseType = "blob";
      xhr.addEventListener("progress", function() {
        if(evt.lengthComputable) {
          $('<pre class="output_o" />').appendTo($o).text('Progress: ' + evt.loaded + '/' + evt.total);
        }
      }, false);
      xhr.onload = function() {
        $('<pre class="output_o" />').appendTo($o).text('Finished Successful!');
        
        window.URL = window.URL || window.webkitURL;
        var $form = $('<form>', {
          method : 'GET',
          target: '_blank',
          action :  window.URL.createObjectURL(xhr.response),
        }).css('display', 'none')
        $body.append($form)
        $form.submit(); 
        $form.remove();
      };
      xhr.send();
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
      $form.submit(); 
      $form.remove();
    },

  }
  
  var sendCmd = function(cmd, options) {
    var defaults = {
      intern: true,
      data: {},
      success: function(data){},
      error: function(msg){}
    }
    var opts = $.extend(true, {}, defaults, options);
    
    
    var default_data = {
      cmd: cmd,
      cw:  consolewidth, // ClientWidth/ConsoleWidth
      'i': opts.intern
    }
    var data = $.extend(true, {}, default_data, opts.data);
  
    // Anfrage stellen
    $.ajax({
      type: "POST",
      url: "shell.php",
      data: data,
      success: function(data) {        
        if (!data.status) { // JSON Data?
          data = {0:{
            c: 'o',
            m: 'php error: ' + data
          }}
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
  
    $output.append('<pre class="shell_cmd">' + $shellname.html() + cmd + '</pre>')
    resetShell()
  
    if (args[0] == '' || args.length == 0) {    
      //jscmd('')
      resetShell()
      return;
    }
    
    if ((method = jscmds[args[0]])) {
      method.apply(this, args.slice(1));
      return ;
    }
  
    sendCmd(cmd, {
      intern: false,
      success: function(data) {
        printOutput(data)          
        
        if (data.status == 'NOT_AUTHORIZED') {
          location.href = '.'            
          return ;
        }
      
        $shellname.html(data.shell)
      },
      error: function(error_msg) {
        printOutput({0:{
          c: 'o',
          m: errormsg
        }})
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
    
  // Login needed?
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
        
        sendCmd('login', {
          intern: true,
          data: o,
          success: function(data) {
            printOutput(data)          
            
            if (data.status == 'NOT_AUTHORIZED') {
              location.href = '.'            
              return
            }
          
            $shellname.html(data.shell)
            $('.login_bgd').hide()
            $input.focus()
          },
          error: function(error_msg) {
            alert(errormsg) // TODO
          }
        })
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
        return
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
      
      sendCmd(cmd, {
        intern: true,
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
              if (!data.result) return;
            }
          }   
          var value = $input.val()     
          $input.val(value.substring(0, value.lastIndexOf(' ')+1) + data.result)
          $input[0].selectionStart = $input[0].selectionEnd = $input.val().length
          updateInputLength()
        },
        error: function(msg) {
          jscmd(errormsg)
        } 
      });      
      break;      
    }
    
    updateInputLength()
    complete_candidates = null
  })
  
  var base = 1024;
  var getReadableSpeedString = function(speedInKBytesPerSec) {
	  var speed = speedInKBytesPerSec;
	  speed = Math.round(speed * 10) / 10;
	  if (speed < base) {
		  return speed + "KB/s";
	  }

	  speed /= base;
	  speed = Math.round(speed * 10) / 10;
	  if (speed < base) {
		  return speed + "MB/s";
	  }

	  return speedInBytesPerSec + "B/s";				
  }

  var getReadableFileSizeString = function(fileSizeInBytes) {
	  var fileSize = fileSizeInBytes;
	  if (fileSize < base) {
		  return fileSize + "B";
	  }

	  fileSize /= base;
	  fileSize = Math.round(fileSize);
	  if (fileSize < base) {
		  return fileSize + "KB";
	  }
	
	  fileSize /= base;
	  fileSize = Math.round(fileSize * 10) / 10;
	  if (fileSize < base) {
		  return fileSize + "MB";
	  }

	  return fileSizeInBytes + "B";
  }

  var getReadableDurationString = function(duration) {
	  var elapsed = duration;

	  var minutes, seconds;

	  seconds = Math.floor(elapsed / 1000);
	  minutes = Math.floor((seconds / 60));
	  seconds = seconds - (minutes * 60);

	  var str = "";
	  if (minutes>0)
		  str += minutes + "m";

	  str += seconds + "s";
	  return str;
  }  
  
  $shellname.dropzone({
    url : "upload.php",
    printLogs : true,
    uploadRateRefreshTime : 500,
    numConcurrentUploads : 2,
    //responseType: "json",
    events : {      
      UploadStarted : function(fileIndex, file) {
        var $command = $('<pre class="shell_cmd">' + $shellname.html() + 'upload '+ file.name + '</pre>')
        file.command = $command
        $output.append($command)
        printOutput({
          0:{'c':'o', 'm':'File upload started; File size: ' + getReadableFileSizeString(file.size)} 
        })
      
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
        console.log(file.name + ': upload finished');
      },
      
      UploadProgress : function(fileIndex, file, newProgress, newSpeed) {
        console.log(file.name + ': progress '+ Math.round(newProgress * 100) + '%');
        //$("#perc" + fileIndex).css("width", Math.round(newProgress * 100) + "%");
        //$("#dropzone-speed" + fileIndex).html(getReadableSpeedString(newSpeed))
      },
      
      FilesDropped : function() {
        //$("#droppedfiles table").empty();
        $(this).dropzone('upload')
      },
      
      Response : function(fileIndex, file, response) {
        console.log(file.name + ': request finished');
      },
      
      ResponseError : function(fileIndex, file, xhr, text_status) {
        console.log(file.name + ': request failed');
      },
    }     
  })
})

