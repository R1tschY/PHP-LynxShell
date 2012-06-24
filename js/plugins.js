
// usage: log('inside coolFunc', this, arguments);
// paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
window.log = function(){
  log.history = log.history || [];   // store logs to an array for reference
  log.history.push(arguments);
  if(this.console) {
    arguments.callee = arguments.callee.caller;
    var newarr = [].slice.call(arguments);
    (typeof console.log === 'object' ? log.apply.call(console.log, console, newarr) : console.log.apply(console, newarr));
  }
};

// make it safe to use console.log always
(function(b){function c(){}for(var d="assert,count,debug,dir,dirxml,error,exception,group,groupCollapsed,groupEnd,info,log,timeStamp,profile,profileEnd,time,timeEnd,trace,warn".split(","),a;a=d.pop();){b[a]=b[a]||c}})((function(){try
{console.log();return window.console;}catch(err){return window.console={};}})());


// place any jQuery/helper plugins in here, instead of separate, slower script files.

/**
 * jQuery.ScrollTo - Easy element scrolling using jQuery.
 * Copyright (c) 2007-2009 Ariel Flesler - aflesler(at)gmail(dot)com | http://flesler.blogspot.com
 * Dual licensed under MIT and GPL.
 * Date: 5/25/2009
 * @author Ariel Flesler
 * @version 1.4.2
 *
 * http://flesler.blogspot.com/2007/10/jqueryscrollto.html
 */
;(function(d){var k=d.scrollTo=function(a,i,e){d(window).scrollTo(a,i,e)};k.defaults={axis:'xy',duration:parseFloat(d.fn.jquery)>=1.3?0:1};k.window=function(a){return d(window)._scrollable()};d.fn._scrollable=function(){return this.map(function(){var a=this,i=!a.nodeName||d.inArray(a.nodeName.toLowerCase(),['iframe','#document','html','body'])!=-1;if(!i)return a;var e=(a.contentWindow||a).document||a.ownerDocument||a;return d.browser.safari||e.compatMode=='BackCompat'?e.body:e.documentElement})};d.fn.scrollTo=function(n,j,b){if(typeof j=='object'){b=j;j=0}if(typeof b=='function')b={onAfter:b};if(n=='max')n=9e9;b=d.extend({},k.defaults,b);j=j||b.speed||b.duration;b.queue=b.queue&&b.axis.length>1;if(b.queue)j/=2;b.offset=p(b.offset);b.over=p(b.over);return this._scrollable().each(function(){var q=this,r=d(q),f=n,s,g={},u=r.is('html,body');switch(typeof f){case'number':case'string':if(/^([+-]=)?\d+(\.\d+)?(px|%)?$/.test(f)){f=p(f);break}f=d(f,this);case'object':if(f.is||f.style)s=(f=d(f)).offset()}d.each(b.axis.split(''),function(a,i){var e=i=='x'?'Left':'Top',h=e.toLowerCase(),c='scroll'+e,l=q[c],m=k.max(q,i);if(s){g[c]=s[h]+(u?0:l-r.offset()[h]);if(b.margin){g[c]-=parseInt(f.css('margin'+e))||0;g[c]-=parseInt(f.css('border'+e+'Width'))||0}g[c]+=b.offset[h]||0;if(b.over[h])g[c]+=f[i=='x'?'width':'height']()*b.over[h]}else{var o=f[h];g[c]=o.slice&&o.slice(-1)=='%'?parseFloat(o)/100*m:o}if(/^\d+$/.test(g[c]))g[c]=g[c]<=0?0:Math.min(g[c],m);if(!a&&b.queue){if(l!=g[c])t(b.onAfterFirst);delete g[c]}});t(b.onAfter);function t(a){r.animate(g,j,b.easing,a&&function(){a.call(this,n,b)})}}).end()};k.max=function(a,i){var e=i=='x'?'Width':'Height',h='scroll'+e;if(!d(a).is('html,body'))return a[h]-d(a)[e.toLowerCase()]();var c='client'+e,l=a.ownerDocument.documentElement,m=a.ownerDocument.body;return Math.max(l[h],m[h])-Math.min(l[c],m[c])};function p(a){return typeof a=='object'?a:{top:a,left:a}}})(jQuery);


/* Used DnD File Upload from http://code.google.com/p/dnd-file-upload/

Links:
 http://docs.jquery.com/Plugins/Authoring#Events
 http://jquery-howto.blogspot.com/2009/11/create-callback-functions-for-your.html 
 FormData:
  http://hacks.mozilla.org/2010/07/firefox-4-formdata-and-the-new-file-url-object/
  https://developer.mozilla.org/en/using_xmlhttprequest
  http://hacks.mozilla.org/2010/05/formdata-interface-coming-to-firefox/
  https://developer.mozilla.org/En/XMLHttpRequest/Using_XMLHttpRequest
  https://developer.mozilla.org/en/Using_files_from_web_applications
  https://github.com/dz0ny/dnd-file-upload/commit/d58e7c0166fc2c685022dd8d28f29d4eef479cea
  
Alternativen:
 http://aquaron.com/jquery/dropup
 */

(function($) {
  var files = new Array();

	var opts = {};
	var defaults = {
		url : "",
		numConcurrentUploads : 3,
		printLogs : false,
		responseType: "",
		// update upload speed every second
		uploadRateRefreshTime : 1000,
    
    events : {
      FilesDroped: function () {},
      UploadStarted: function (fileIndex, file) {},
      UploadFinished: function (fileIndex, file, time) {},
      UploadProgress: function (fileIndex, file, newProgress, newSpeed) {},
      Response: function(fileIndex, file, response) {},
      ResponseError: function(fileIndex, file, xhr, textStatus) {},
    }
	};
  
  var methods = {
    init : function(options) {
    
      opts = $.extend( true, {}, defaults, options);
      
      return this.bind({
        "dragenter.dropzone": function(event) {
          event.stopPropagation();
          event.preventDefault();
          return false;
        },

        "dragover.dropzone": function(event) {
          event.stopPropagation();
          event.preventDefault();
          return false;
        },

        "drop.dropzone": function(event) {
          event.stopPropagation();
          event.preventDefault();
          
          var dt = event.originalEvent.dataTransfer;
          
         files = dt.files;
          
          opts.events.FilesDropped();

          return false;
        }      
      });
    },
    destroy : function( ) {

      return this.each(function(){
        $(this).unbind('.dropzone');
      })

    },
    upload : function( ) {
      for ( var i = 0; i < files.length; i++) {
        var file = files[i]; 
        
        var xhr = new XMLHttpRequest();
        var upload = xhr.upload;
        upload.fileIndex = i;
        upload.fileObj = file;
        upload.downloadStartTime = new Date().getTime();
        upload.lastTime = upload.downloadStartTime;
        upload.lastData = 0;
        
        upload.addEventListener("progress", progress, false);
        upload.addEventListener("load", load, false);
        
        xhr.addEventListener("load", response, false);
        xhr.addEventListener("error", response_error, false);
        
        xhr.open("POST", opts.url, true);
        
        xhr.responseType = opts.responseType
        
        if (window.FormData) {
          var fd = new FormData();
          fd.append("file", file); 
          
          xhr.send(fd);             
        } else {
          // simulate a file POST request.
          var boundary = "DropZoneBoundary" + randomchars(10)
          
          xhr.setRequestHeader("Content-Type", "multipart/form-data; boundary="+boundary);
          
          var body = "--" + boundary + "\r\n";
          body += "Content-Disposition: form-data; name=\"file\"; filename=\"" + file.name + "\"\r\n";
          body += "Content-Type: application/octet-stream\r\n\r\n";
          body += file.getAsBinary() + "\r\n";
          body += "--" + boundary + "--";  
          
          xhr.sendAsBinary(body);             
        }
        
        opts.events.UploadStarted(i, file);
      }
    }
  };
  
  
  $.fn.dropzone = function(method) {
    
    if ( methods[method] ) {
      return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
    } else if ( typeof method === 'object' || ! method ) {
      return methods.init.apply( this, arguments );
    } else {
      $.error( 'Method ' +  method + ' does not exist on jQuery.dropzone' );
    }    
  
  };
  
  function randomchars(n) {
    var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
    var result = ""
    for (var i = 0; i < n; i++) {
      result += chars[parseInt(Math.random() * (chars.length-1))]
    }
    return result
  }

	function log(logMsg) {
		if (opts.printLogs) {
			console.log("[dropzone] " + logMsg);
		}
	}

	function load(event) {
		var timeDiff = parseInt(event.timeStamp/1000) - this.downloadStartTime;
		opts.events.UploadFinished(this.fileIndex, this.fileObj, timeDiff);
		log("finished loading of file " + this.fileIndex);
	}

	function progress(event) {
		if (event.lengthComputable) {
			var percentage = event.loaded / event.total;
			var speed = (event.loaded - this.lastData) / (parseInt(event.timeStamp/1000) - this.lastTime); // in KB/sec

   		log(this.fileIndex + " --> " + Math.round(percentage * 100) + "% " + Math.round(speed) + "KB/s");

			opts.events.UploadProgress(this.fileIndex, this.fileObj, percentage, speed);
			
			this.lastTime = elapsed;
			this.lastData = event.loaded;
		}
	}
	
	function response(event) {
	  opts.events.Response(this.upload.fileIndex, this.upload.fileObj, this.response)
	}
	
	function response_error(event) {
	  opts.events.ResponseError(this.upload.fileIndex, this.upload.fileObj, this, this.statusText)
	}
})(jQuery);


