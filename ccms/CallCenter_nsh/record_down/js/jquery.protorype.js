$.extend(String.prototype, {
	isPositiveInteger:function(){
		return (new RegExp(/^[1-9]\d*$/).test(this));
	},
	isInteger:function(){
		return (new RegExp(/^\d+$/).test(this));
	},
	isNumber: function(value, element) {
		return (new RegExp(/^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/).test(this));
	},
	trim:function(){
		return this.replace(/(^\s*)|(\s*$)|\r|\n/g, "");
	},
	trans:function() {
		return this.replace(/&lt;/g, '<').replace(/&gt;/g,'>').replace(/&quot;/g, '"');
	},
	replaceAll:function(os, ns) {
		return this.replace(new RegExp(os,"gm"),ns);
	},         skipChar:function(ch) {
		if (!this || this.length===0) {return '';}
		if (this.charAt(0)===ch) {return this.substring(1).skipChar(ch);}
		return this;
	},
	isValidPwd:function() {
		return (new RegExp(/^([_]|[a-zA-Z0-9]){6,32}$/).test(this)); 
	},
	isValidMail:function(){
		return(new RegExp(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/).test(this.trim()));
	},
	isSpaces:function() {
		for(var i=0; i<this.length; i+=1) {
			var ch = this.charAt(i);
			if (ch!=' '&& ch!="\n" && ch!="\t" && ch!="\r") {return false;}
		}
		return true;
	},
	isPhone:function() {
		return (new RegExp(/(^([0-9]{3,4}[-])?\d{3,8}(-\d{1,6})?$)|(^\([0-9]{3,4}\)\d{3,8}(\(\d{1,6}\))?$)|(^\d{3,8}$)/).test(this));
	},
	isUrl:function(){
		return (new RegExp(/^[a-zA-z]+:\/\/([a-zA-Z0-9\-\.]+)([-\w .\/?%&=:]*)$/).test(this));
	},
	isExternalUrl:function(){
		return this.isUrl() && this.indexOf("://"+document.domain) == -1;
	}
});