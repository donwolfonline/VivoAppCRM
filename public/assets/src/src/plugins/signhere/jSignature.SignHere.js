/** @license
jSignature v2 jSignature's Sign Here "sticker" plugin
Copyright (c) 2011 Willow Systems Corp http://willow-systems.com
MIT License <http://www.opensource.org/licenses/mit-license.php>

*/
;(function(){

	var apinamespace = 'jSignature'

	function attachHandlers($obj, apinamespace, extensionName) {

		;(function(jSignatureInstance, $obj, apinamespace) {
			$obj.bind('click', function(){
				// when user is annoyed enough to click on us, hide us
				$obj.hide()
			})

			jSignatureInstance.events.subscribe(
				apinamespace + '.change'
				, function(){
					if (jSignatureInstance.dataEngine.data.length) {
						$obj.hide()
					} else {
						$obj.show()
					}
				}
			)
		})( this, $obj, apinamespace )
	}

	function ExtensionInitializer(extensionName){
		// we are called very early in instance's life.
		// right after the settings are resolved and 
		// jSignatureInstance.events is created 
		// and right before first ("jSignature.initializing") event is called.
		// You don't really need to manupilate 
		// jSignatureInstance directly, just attach
		// a bunch of events to jSignatureInstance.events
		// (look at the source of jSignatureClass to see when these fire)
		// and your special pieces of code will attach by themselves.

		// this function runs every time a new instance is set up.
		// this means every var you create will live only for one instance
		// unless you attach it to something outside, like "window."
		// and pick it up later from there.

		// when globalEvents' events fire, 'this' is globalEvents object
		// when jSignatureInstance's events fire, 'this' is jSignatureInstance

		// Here,
		// this = is new jSignatureClass's instance.

		// The way you COULD approch setting this up is:
		// if you have multistep set up, attach event to "jSignature.initializing"
		// that attaches other events to be fired further lower the init stream.
		// Or, if you know for sure you rely on only one jSignatureInstance's event,
		// just attach to it directly

		var apinamespace = 'jSignature'

		this.events.subscribe(
			// name of the event
			apinamespace + '.attachingEventHandlers'
			// event handlers, can pass args too, but in majority of cases,
			// 'this' which is jSignatureClass object instance pointer is enough to get by.
			, function(){

				var renderer = function(){
					var data = ""
					, $img = $('<img style="position:absolute !important; top: auto; min-width:90px !important; max-width:180px !important;width:10% !important;border:none !important;padding: 0 !important;margin:0 !important;box-shadow:0 0 0 !important;" />')
					try {
						$img[0].src = data
						return $img
					} catch (ex) {
						return $() // empty jQuery obj
					}
				}
				if (this.settings[extensionName] && typeof this.settings[extensionName].renderer === 'function') {
					renderer = this.settings[extensionName].renderer
				}

				var $obj = renderer()

				if ($obj.length) {
					$obj.appendTo(this.$controlbarUpper)

					attachHandlers.call( 
						this
						, $obj
						, apinamespace
						, extensionName
					)
				}
			}
		)
	}

	var ExtensionAttacher = function(){
		$.fn[apinamespace](
			'addPlugin'
			, 'instance' // type of plugin
			, 'SignHere' // extension name
			, ExtensionInitializer
		)
	}
	

//  //Because plugins are minified together with jSignature, multiple defines per (minified) file blow up and dont make sense
//	//Need to revisit this later.
	
//	if ( typeof define === "function" && define.amd != null) {
//		// AMD-loader compatible resource declaration
//		// you need to call this one with jQuery as argument.
//		define(function(){return Initializer} )
//	} else {
		ExtensionAttacher()
//	}

})();